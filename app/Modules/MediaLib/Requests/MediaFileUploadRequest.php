<?php

namespace App\Modules\MediaLib\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Валидация загрузки файла
 */
class MediaFileUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $maxSize = config('medialib.upload.max_file_size', 10240) * 1024;
        $allowedMimes = config('medialib.upload.allowed_mimes', []);

        return [
            'file' => 'required|file|mimes:' . implode(',', $allowedMimes) . '|max:' . $maxSize,
            'folder_id' => 'nullable|exists:media_folders,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'alt' => 'nullable|string|max:255',
            'translations' => 'nullable|array',
            'translations.*.title' => 'nullable|string|max:255',
            'translations.*.description' => 'nullable|string',
            'translations.*.alt' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Файл обязателен для загрузки',
            'file.file' => 'Загруженный объект должен быть файлом',
            'file.mimes' => 'Недопустимый формат файла. Разрешены: ' . 
                           implode(', ', config('medialib.upload.allowed_mimes', [])),
            'file.max' => 'Максимальный размер файла: ' . 
                         config('medialib.upload.max_file_size') . 'KB',
            'folder_id.exists' => 'Выбранная папка не существует',
            'title.max' => 'Заголовок не должен превышать 255 символов',
            'alt.max' => 'Alt текст не должен превышать 255 символов',
        ];
    }
}