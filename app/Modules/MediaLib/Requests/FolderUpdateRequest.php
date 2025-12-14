<?php

namespace App\Modules\MediaLib\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FolderUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'folder_name' => 'required|string|max:255|regex:/^[a-zA-Z0-9_\-\s]+$/'
        ];
    }

    public function messages(): array
    {
        return [
            //
        ];
    }
}