<?php

namespace App\Http\Controllers\File;

use App\Http\Controllers\Controller;
use App\Http\Requests\File\AddToFavoriteRequest;
use App\Http\Requests\File\FileActionRequest;
use App\Http\Requests\File\ShareFilesRequest;
use App\Http\Requests\File\StoreFileRequest;
use App\Http\Requests\File\TrashFilesRequest;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Resources\File\FileResource;
use App\Mail\ShareFilesMail;
use App\Models\File;
use App\Models\FileShare;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Kalnoy\Nestedset\Collection;
use ZipArchive;

class FileController extends Controller
{
    public function myFiles(Request $request, ?File $folder = null): AnonymousResourceCollection|Response
    {
        $user = Auth::user();
        if (is_null($folder)) {
            $folder = $this->getRoot();
        }

        if ($request->get('favorites') === '1') {
            $files = $user->starred()->where('parent_id', $folder->id)->paginate(10);
        } else {
            $files = File::query()
                ->where('parent_id', $folder->id)
                ->where('created_by', $user->id)
                ->orderBy('is_folder', 'desc')
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->paginate(10);
        }

        $files = FileResource::collection($files);

        if ($request->wantsJson()) {
            return $files;
        }

        $ancestors = FileResource::collection([...$folder->ancestors, $folder])->resolve();
        $parentFolder = FileResource::make($folder)->resolve();

        return Inertia::render('MyFiles', compact('files', 'parentFolder', 'ancestors'));
    }

    public function trash(Request $request)
    {
        $files = File::query()
            ->onlyTrashed()
            ->where('created_by', Auth::id())
            ->orderBy('is_folder', 'desc')
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);

        if ($request->wantsJson()) {
            return $files;
        }

        $files = FileResource::collection($files);

        return Inertia::render('Trash', compact('files'));
    }

    public function sharedWithMe(Request $request)
    {
        $files = File::getFilesWithMe()
            ->paginate(10);

        if ($request->wantsJson()) {
            return $files;
        }

        $files = FileResource::collection($files);

        return Inertia::render('SharedWithMe', compact('files'));
    }

    public function sharedByMe(Request $request)
    {
        $files = File::getFilesByMe()
            ->paginate(10);

        if ($request->wantsJson()) {
            return $files;
        }

        $files = FileResource::collection($files);

        return Inertia::render('SharedByMe', compact('files'));
    }

    public function restore(TrashFilesRequest $request)
    {
        $data = $request->validated();

        if (! is_null(Arr::get($data, 'all'))) {
            $trashFiles = File::query()->onlyTrashed()->get();

            foreach ($trashFiles as $file) {
                $file->restore();
            }
        } else {
            $ids = Arr::get($data, 'ids') ?? [];
            $trashFiles = File::query()->onlyTrashed()->whereIn('id', $ids)->get();

            foreach ($trashFiles as $file) {
                $file->restore();
            }
        }

        return to_route('trash');
    }

    public function addFavorite(AddToFavoriteRequest $request)
    {
        $data = $request->validated();
        $user = Auth::user();
        $file = File::query()->findOrFail(Arr::get($data, 'id'));

        // Определяем текущее состояние
        $isNowStarred = ! $user->starred()->where('file_id', $file->id)->exists();

        // Получаем все id файлов (сама папка + все дети рекурсивно)
        $ids = $this->getAllChildrenIds($file->id);
        if ($isNowStarred) {
            $user->starred()->syncWithoutDetaching($ids);
        } else {
            $user->starred()->detach($ids);
        }

        return FileResource::make($file->fresh('children'))->resolve();
    }

    protected function getAllChildrenIds(int $rootId): array
    {
        $ids = DB::select('
        WITH RECURSIVE cte AS (
            SELECT id FROM files WHERE id = :rootId
            UNION ALL
            SELECT f.id FROM files f
            INNER JOIN cte ON f.parent_id = cte.id
        )
        SELECT id FROM cte
    ', ['rootId' => $rootId]);

        return collect($ids)->pluck('id')->all();
    }

    public function createFolder(StoreFolderRequest $request): void
    {
        $data = $request->validated();
        $parent = $request->getParent();

        if (! $parent) {
            $parent = $this->getRoot();
        }

        $file = new File;
        $file->is_folder = 1;
        $file->name = Arr::get($data, 'name');

        $parent->appendNode($file);
    }

    public function deleteForever(TrashFilesRequest $request)
    {
        $data = $request->validated();

        if (! is_null(Arr::get($data, 'all'))) {
            $trashFiles = File::query()->onlyTrashed()->get();

            foreach ($trashFiles as $file) {
                $file->deleteForever();
            }
        } else {
            $ids = Arr::get($data, 'ids') ?? [];
            $trashFiles = File::query()->onlyTrashed()->whereIn('id', $ids)->get();

            foreach ($trashFiles as $file) {
                $file->deleteForever();
            }
        }
    }

    public function store(StoreFileRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();
        $parent = $request->getParent();
        if (! $parent) {
            $parent = $this->getRoot();
        }

        if (is_null($request->filesTree)) {
            /** @var UploadedFile $file */
            foreach (Arr::get($data, 'files') as $file) {
                $this->saveFile($file, $parent, $user);
            }
        } else {
            $this->saveFileTree($request->filesTree, $parent, $user);
        }
    }

    public function share(ShareFilesRequest $request)
    {
        $data = $request->validated();
        $parent = $request->getParent();

        if (! $parent) {
            $parent = $this->getRoot();
        }

        $all = Arr::get($data, 'all') ?? false;
        $ids = Arr::get($data, 'ids') ?? [];

        if (! $all && empty($ids)) {
            return [
                'message' => 'Please select files to share.',
            ];
        }

        $user = User::query()->whereEmail(Arr::get($data, 'email'))->first();

        if (is_null($user))
        {
            return redirect()->back();
        }

        if ($all)
        {
            $files = $parent->children;
        } else {
            $files = File::query()->whereIn('id', $ids)->get();
        }

        $existingFiles =  FileShare::query()
            ->whereIn('file_id', $ids)
            ->where('user_id', $user->id)
            ->get()
            ->pluck('file_id')
            ->toArray();

        $data = [];

        foreach ($files as $file) {

            if (in_array($file->id, $existingFiles)) {
                continue;
            }

            $data[] = [
                'user_id' => $user->id,
                'file_id' => $file->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }

        FileShare::insert($data);

        Mail::to($user)->send(new ShareFilesMail(
            $user,
            Auth::user(),
            $files
        ));

        return redirect()->back();
    }

    public function destroy(FileActionRequest $request)
    {
        $data = $request->validated();
        $parent = $request->getParent();

        if (! $parent) {
            $parent = $this->getRoot();
        }

        if (! is_null(Arr::get($data, 'all'))) {
            $childrens = $parent->children;
            foreach ($childrens as $child) {
                $child->moveToTrash();
            }
        } else {
            foreach (Arr::get($data, 'ids') as $id) {
                $file = File::find($id);
                $file->moveToTrash();
            }
        }

        return redirect()->back()->with('success', true);
    }

    public function sharedWithMeDownload(FileActionRequest $request)
    {
        $data = $request->validated();

        $all = Arr::get($data, 'all') ?? false;
        $ids = Arr::get($data, 'ids') ?? [];

        if (! $all && empty($ids)) {
            return [
                'message' => 'Please select files to download.',
            ];
        }

        $zipName = 'shared_with_me';

        if ($all) {
            $files = File::getFilesWithMe()->get();
            $url = $this->createZip($files);
            $filename = $zipName.'.zip';
        } else {
            [$url, $filename] = $this->getUrlAndFilename($ids, $zipName);
        }

        return [
            'url' => $url,
            'filename' => $filename,
        ];
    }

    public function download(FileActionRequest $request)
    {
        $data = $request->validated();
        $parent = $request->getParent();
        if (! $parent) {
            $parent = $this->getRoot();
        }

        $all = Arr::get($data, 'all') ?? false;
        $ids = Arr::get($data, 'ids') ?? [];

        if (! $all && empty($ids)) {
            return [
                'message' => 'Please select files to download.',
            ];
        }

        if ($all) {
            $url = $this->createZip($parent->children);
            $filename = $parent->name.'.zip';
        } else {
            [$url, $filename] = $this->getUrlAndFilename($ids, $parent->name);
        }

        return [
            'url' => $url,
            'filename' => $filename,
        ];
    }

    private function getUrlAndFilename(array $ids, string $zipName): array
    {
        if (count($ids) === 1) {
            $file = File::findOrFail($ids[0]);

            if ($file->is_folder) {
                $file->load('childrenRecursive');
                if ($file->childrenRecursive->isEmpty()) {
                    return ['message' => 'The folder is empty.'];
                }

                $url = $this->createZip($file->childrenRecursive);
                $filename = $zipName.'.zip';
            } else {
                $url = $this->createSingleFileUrl($file);
                $filename = $zipName;
            }
        } else {
            $files = File::whereIn('id', $ids)->with('childrenRecursive')->get();
            $url = $this->createZip($files);
            $filename = $zipName.'.zip';
        }

        return [$url, $filename];
    }

    private function createSingleFileUrl(File $file): string
    {
        $dest = pathinfo($file->storage_path, PATHINFO_BASENAME);
        Storage::copy($file->storage_path, $dest);

        return asset(Storage::url($dest));
    }

    private function createZip(array|Collection $files): string
    {
        $zipPath = 'zip/'.Str::random().'.zip';

        if (! Storage::exists(dirname($zipPath))) {
            Storage::makeDirectory(dirname($zipPath));
        }

        $zipFile = Storage::path($zipPath);

        $zip = new ZipArchive;

        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            $this->addFilesToZip($zip, $files);
        }

        $zip->close();

        return Storage::url($zipPath);
    }

    private function addFilesToZip(ZipArchive $zip, Collection|array $files, string $ancestors = ''): void
    {
        foreach ($files as $file) {
            if ($file->is_folder) {
                // используем уже загруженные childrenRecursive
                $this->addFilesToZip($zip, $file->childrenRecursive, $ancestors.$file->name.'/');
            } else {
                $zip->addFile(Storage::path($file->storage_path), $ancestors.$file->name);
            }
        }
    }

    private function getRoot(): File
    {
        return File::query()->whereIsRoot()->where('created_by', Auth::id())->firstOrFail();
    }

    private function saveFileTree(array $filesTree, File $parent, User $user)
    {
        foreach ($filesTree as $key => $file) {
            if (is_array($file)) {

                $folder = new File;
                $folder->name = $key;
                $folder->is_folder = 1;
                $parent->appendNode($folder);
                $this->saveFileTree($file, $folder, $user);
            } else {
                $this->saveFile($file, $parent, $user);
            }
        }
    }

    private function saveFile(UploadedFile $file, File $parent, User $user): void
    {
        $path = $file->store('files/'.$user->id);
        $fileModel = new File;

        $fileModel->storage_path = $path;
        $fileModel->name = $file->getClientOriginalName();
        $fileModel->is_folder = false;
        $fileModel->mime = $file->getClientMimeType();
        $fileModel->size = $file->getSize();

        $parent->appendNode($fileModel);
    }
}
