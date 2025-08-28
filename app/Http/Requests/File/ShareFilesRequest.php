<?php

namespace App\Http\Requests\File;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ShareFilesRequest extends FileActionRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return ! auth()->guest();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'all' => 'nullable|bool',
            'ids.*' => Rule::exists('files', 'id')->where(function ($query) {
                $query->where('created_by', Auth::id());
            }),
            'email' => 'required|email'
        ]);
    }
}
