<?php

namespace App\Modules\MediaLib\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Валидация переименования
 */
class MediaRenameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Новое имя обязательно для заполнения',
            'name.max' => 'Имя не должно превышать 255 символов',
        ];
    }
}