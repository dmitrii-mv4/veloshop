<?php

namespace App\Modules\Catalog\Requests\Tags;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

/**
 * Запрос на создание тега
 *
 * Валидация данных при создании нового тега.
 */
class CreateTagRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь для выполнения запроса
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Правила валидации
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:tags,slug',
        ];
    }

    /**
     * Сообщения об ошибках валидации
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'Название тега обязательно для заполнения',
            'name.max' => 'Название тега не должно превышать 100 символов',
            'slug.unique' => 'Тег с таким слагом уже существует',
            'slug.max' => 'Слаг не должен превышать 100 символов',
        ];
    }

    /**
     * Кастомные имена атрибутов для сообщений об ошибках
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'name' => 'название тега',
            'slug' => 'слаг',
        ];
    }
}
