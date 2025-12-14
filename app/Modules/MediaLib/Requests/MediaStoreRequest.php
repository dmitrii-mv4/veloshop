<?php

namespace App\Modules\MediaLib\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MediaStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'files.*' => 'required|file|max:20480',
            'folder_id' => 'nullable|exists:media_folders,id'
        ];
    }

    public function messages(): array
    {
        return [
            'files.required' => 'Необходимо выбрать хотя бы один файл.',
            'files.array' => 'Файлы должны быть переданы в виде массива.',
            'files.min' => 'Необходимо выбрать хотя бы один файл.',
            'files.max' => 'Можно загрузить не более 10 файлов за раз.',
            'files.*.required' => 'Файл обязателен для загрузки.',
            'files.*.file' => 'Загруженный элемент должен быть файлом.',
            'files.*.max' => 'Размер файла не должен превышать 2MB.',
            'files.*.mimes' => 'Неподдерживаемый формат файла. Разрешены: JPEG, JPG, PNG, GIF, WebP, SVG, PDF, DOC, DOCX',
        ];
    }
}