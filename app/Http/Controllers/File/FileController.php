<?php

namespace App\Http\Controllers\File;

use App\Http\Controllers\Controller;
use App\Http\Requests\File\FileActionRequest;
use App\Http\Requests\File\StoreFileRequest;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Resources\File\FileResource;
use App\Jobs\DeleteFilesJob;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
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
        if (is_null($folder)) {
            $folder = $this->getRoot();
        }

        $files = File::query()
            ->where('parent_id', $folder->id)
            ->where('created_by', Auth::id())
            ->orderBy('is_folder', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $files = FileResource::collection($files);

        if ($request->wantsJson()) {
            return $files;
        }
        $ancestors = FileResource::collection([...$folder->ancestors, $folder])->resolve();
        $parentFolder = FileResource::make($folder)->resolve();

        return Inertia::render('MyFiles', compact('files', 'parentFolder', 'ancestors'));
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

    public function destroy(FileActionRequest $request)
    {
        $data = $request->validated();
        $parent = $request->getParent();

        if (! $parent) {
            $parent = $this->getRoot();
        }

        DeleteFilesJob::dispatch($parent->id, $data);

        return redirect()->back()->with('success', true);
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
            if (count($ids) === 1) {
                $file = File::query()->find($ids[0]);

                if ($file->is_folder) {
                    if ($file->children->count() === 0) {
                        return [
                            'message' => 'The folder is empty.',
                        ];
                    }

                    $url = $this->createZip($file->children);
                    $filename = $file->name.'.zip';
                } else {
                    $dest = pathinfo($file->storage_path, PATHINFO_BASENAME);
                    Storage::copy($file->storage_path, $dest);

                    $url = asset(Storage::url($dest));
                    $filename = $file->name;
                }
            } else {
                $files = File::query()->whereIn('id', $ids)->get();
                $url = $this->createZip($files);
                $filename = $parent->name.'.zip';
            }
        }

        return [
            'url' => $url,
            'filename' => $filename,
        ];
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

    private function addFilesToZip(ZipArchive $zip, array|Collection $files, string $ancestors = '')
    {
        foreach ($files as $file) {
            if ($file->is_folder) {
                $this->addFilesToZip($zip, $file->children, $ancestors.$file->name.'/');
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
