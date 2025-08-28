<?php

namespace App\Http\Requests\File;

use App\Http\Requests\ParentIdBaseRequest;
use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

class StoreFileRequest extends ParentIdBaseRequest
{
    protected function prepareForValidation()
    {
        $paths = array_filter($this->relativePaths ?? null, fn ($path) => $path !== null);
        $this->merge([
            'folderName' => $this->detectFolderName($paths),
        ]);
    }

    protected function passedValidation()
    {
        $data = $this->validated();
        $this->replace(['filesTree' => $this->buildFileTree($this->relativePaths, Arr::get($data, 'files'))]);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'files.*' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    if (! $this->folderName) {
                        /** @var $value UploadedFile */
                        $file = File::query()
                            ->where('name', $value->getClientOriginalName())
                            ->where('created_by', \Auth::id())
                            ->where('parent_id', $this->parent_id)
                            ->whereNull('deleted_at')
                            ->exists();
                        if ($file) {
                            $fail('The file "'.$value->getClientOriginalName().'" already exists!');
                        }
                    }
                },
            ],
            'folderName' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $file = File::query()
                            ->where('name', $value)
                            ->where('created_by', \Auth::id())
                            ->where('parent_id', $this->parent_id)
                            ->whereNull('deleted_at')
                            ->exists();
                        if ($file) {
                            $fail('The folder "'.$value.'" already exists!');
                        }
                    }

                },
            ],
        ]);
    }

    private function detectFolderName(array $paths): ?string
    {
        if (count($paths) === 0) {
            return null;
        }

        $folderName = explode('/', $paths[0]);

        return $folderName[0];
    }

    private function buildFileTree(array $relativePaths, array $files): ?array
    {
        if (is_null($relativePaths[0])) {
            return null;
        }

        $tree = [];

        foreach ($relativePaths as $ind => $relativePath) {
            $parts = explode('/', $relativePath);

            $currentNode = &$tree;

            foreach ($parts as $key => $part) {
                if (! isset($currentNode[$part])) {
                    $currentNode[$part] = [];
                }

                if ($key === count($parts) - 1) {
                    $currentNode[$part] = $files[$ind];
                } else {
                    $currentNode = &$currentNode[$part];
                }
            }
        }

        return $tree;
    }
}
