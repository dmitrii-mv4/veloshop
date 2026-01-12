<?php

namespace App\Modules\MediaLib\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Валидация создания папки
 */
class MediaFolderCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:media_folders,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Название папки обязательно для заполнения',
            'name.max' => 'Название папки не должно превышать 255 символов',
            'parent_id.exists' => 'Выбранная родительская папка не существует',
        ];
    }
}