<?php

namespace App\Http\Requests;

use App\Models\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreFolderRequest extends ParentIdBaseRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique(File::class, 'name')
                    ->where('created_by', Auth::id())
                    ->where('parent_id', $this->getRootFile() ? $this->getRootFile()->id : $this->getParent()->id)
                    ->whereNull('deleted_at'),
            ],
        ]);
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Folder ":input" already exists',
        ];
    }
}
