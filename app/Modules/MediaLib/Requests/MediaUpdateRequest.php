<?php

namespace App\Modules\MediaLib\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Валидация обновления метаданных файла
 */
class MediaUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'alt' => 'nullable|string|max:255',
            'meta' => 'nullable|array',
            'translations' => 'nullable|array',
            'translations.*.title' => 'nullable|string|max:255',
            'translations.*.description' => 'nullable|string',
            'translations.*.alt' => 'nullable|string|max:255',
            'translations.*.meta' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'Заголовок не должен превышать 255 символов',
            'alt.max' => 'Alt текст не должен превышать 255 символов',
        ];
    }
}