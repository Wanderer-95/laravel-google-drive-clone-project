<?php

namespace App\Http\Requests\File;

use App\Http\Requests\ParentIdBaseRequest;
use App\Models\File;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FileActionRequest extends ParentIdBaseRequest
{
    protected function prepareForValidation()
    {
        if ($this->input('all') === 'true') {
            $this->merge(['all' => true]);
        }
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
            'ids.*' => [
                Rule::exists('files', 'id'),
                function (string $attribute, mixed $value, Closure $fail) {
                    $file = File::query()->leftJoin('file_shares', 'file_shares.file_id', '=', 'files.id')
                        ->where('files.id', $value)
                        ->where(function ($query) {
                            $query->where('files.created_by', Auth::id())
                                ->orWhere('file_shares.user_id', Auth::id());
                        })->first();
                    if (!$file) {
                        $fail('invalid ID "' . $value . '"');
                    }
                }
            ]
        ]);
    }
}
