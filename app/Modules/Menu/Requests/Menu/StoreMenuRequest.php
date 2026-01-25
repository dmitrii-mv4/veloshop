<?php

namespace App\Modules\Menu\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

/**
 * Реквест для создания меню
 * Валидирует данные при создании нового меню
 */
class StoreMenuRequest extends FormRequest
{
    /**
     * Определение, авторизован ли пользователь для выполнения запроса
     *
     * @return bool
     */
    public function authorize(): bool
    {
        Log::info('Проверка авторизации для создания меню', [
            'user_id' => auth()->id(),
            'ip' => $this->ip()
        ]);
        
        return auth()->check();
    }

    /**
     * Правила валидации для создания меню
     *
     * @return array
     */
    public function rules(): array
    {
        Log::info('Валидация данных для создания меню', [
            'data' => $this->all(),
            'user_id' => auth()->id()
        ]);
        
        return [
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'menu_type_id' => [
                'nullable',
                'integer',
                'exists:menu_types,id'
            ],
            'is_active' => [
                'boolean'
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
            'name.required' => 'Название меню обязательно для заполнения',
            'name.string' => 'Название меню должно быть строкой',
            'name.max' => 'Название меню не может превышать 255 символов',
            'description.string' => 'Описание должно быть строкой',
            'description.max' => 'Описание не может превышать 1000 символов',
            'menu_type_id.integer' => 'Тип меню должен быть числом',
            'menu_type_id.exists' => 'Выбранный тип меню не существует',
            'is_active.boolean' => 'Статус активности должен быть логическим значением'
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
            'name' => 'Название меню',
            'description' => 'Описание',
            'menu_type_id' => 'Тип меню',
            'is_active' => 'Активность'
        ];
    }
}