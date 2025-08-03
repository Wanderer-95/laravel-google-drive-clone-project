<?php

namespace App\Http\Requests;

use App\Models\File;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ParentIdBaseRequest extends FormRequest
{
    private ?File $parent = null;

    private ?File $rootFile = null;

    public function authorize(): bool
    {
        $this->parent = File::query()->where('id', $this->input('parent_id'))->first();

        if (! isset($this->parent)) {
            $this->rootFile = File::query()->where('created_by', Auth::id())->whereNull('parent_id')->first();
        }

        if ($this->parent && ! $this->parent->isOwnedBy()) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => [
                Rule::exists(File::class, 'id')->where(function ($query) {
                    return $query->where('is_folder', 1)->where('created_by', Auth::id());
                }),
            ],
        ];
    }

    public function getParent(): ?File
    {
        return $this->parent;
    }

    public function getRootFile(): ?File
    {
        return $this->rootFile;
    }
}
