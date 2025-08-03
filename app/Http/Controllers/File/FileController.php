<?php

namespace App\Http\Controllers\File;

use App\Http\Controllers\Controller;
use App\Http\Requests\File\StoreFileRequest;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Resources\File\FileResource;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

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

        if (!$parent) {
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

        if (!$parent) {
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

    private function getRoot(): File
    {
        return File::query()->whereIsRoot()->where('created_by', Auth::id())->firstOrFail();
    }

    private function saveFileTree(array $filesTree, File $parent, User $user)
    {
        foreach ($filesTree as $key => $file) {
            if (is_array($file)) {

                $folder = new File();
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
        $path = $file->store('files/' . $user->id);

        $fileModel = new File();

        $fileModel->storage_path = $path;
        $fileModel->name = $file->getClientOriginalName();
        $fileModel->is_folder = false;
        $fileModel->mime = $file->getClientMimeType();
        $fileModel->size = $file->getSize();

        $parent->appendNode($fileModel);
    }
}
