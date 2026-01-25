<?php

namespace App\Modules\Menu\Requests\MenuType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

/**
 * Реквест для создания типа меню
 * Валидирует данные при создании нового типа меню
 */
class StoreMenuTypeRequest extends FormRequest
{
    /**
     * Определение, авторизован ли пользователь для выполнения запроса
     *
     * @return bool
     */
    public function authorize(): bool
    {
        Log::info('Проверка авторизации для создания типа меню', [
            'user_id' => auth()->id(),
            'ip' => $this->ip()
        ]);
        
        return auth()->check();
    }

    /**
     * Правила валидации для создания типа меню
     *
     * @return array
     */
    public function rules(): array
    {
        Log::info('Валидация данных для создания типа меню', [
            'data' => $this->all(),
            'user_id' => auth()->id()
        ]);
        
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:menu_types,name'
            ]
        ];
    }

    /**
     * Сообщения об ошибках валидации
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название типа меню обязательно для заполнения',
            'name.string' => 'Название типа меню должно быть строкой',
            'name.max' => 'Название типа меню не может превышать 100 символов',
            'name.unique' => 'Тип меню с таким названием уже существует'
        ];
    }

    /**
     * Названия атрибутов для сообщений об ошибках
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name' => 'Название типа меню'
        ];
    }
}