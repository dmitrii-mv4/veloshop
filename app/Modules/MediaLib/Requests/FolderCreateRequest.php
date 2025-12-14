<?php

namespace App\Modules\MediaLib\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FolderCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'folder_name' => 'required|string|max:255|regex:/^[a-zA-Z0-9а-яА-Я_\-\s]+$/iu',
            'parent_id' => 'nullable|exists:media_folders,id'
        ];
    }

    public function messages(): array
    {
        return [
            //
        ];
    }
}