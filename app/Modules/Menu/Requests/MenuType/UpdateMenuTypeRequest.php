<?php

namespace App\Modules\Menu\Requests\MenuType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

/**
 * Реквест для обновления типа меню
 * Валидирует данные при обновлении существующего типа меню
 */
class UpdateMenuTypeRequest extends FormRequest
{
    /**
     * Определение, авторизован ли пользователь для выполнения запроса
     *
     * @return bool
     */
    public function authorize(): bool
    {
        Log::info('Проверка авторизации для обновления типа меню', [
            'user_id' => auth()->id(),
            'ip' => $this->ip(),
            'type_id' => $this->route('menutype')?->id
        ]);
        
        return auth()->check();
    }

    /**
     * Правила валидации для обновления типа меню
     *
     * @return array
     */
    public function rules(): array
    {
        $menuTypeId = $this->route('menutype')?->id;
        
        Log::info('Валидация данных для обновления типа меню', [
            'type_id' => $menuTypeId,
            'data' => $this->all(),
            'user_id' => auth()->id()
        ]);
        
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:menu_types,name,' . $menuTypeId
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