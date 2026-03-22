<?php

namespace App\Modules\Catalog\Requests\Warehouses;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

/**
 * Запрос на обновление информации о складе
 * Включает валидацию данных для редактирования существующего склада
 */
class CreateWarehousesRequest extends FormRequest
{
    /**
     * Определение прав доступа для запроса
     *
     * @return bool
     */
    public function authorize(): bool
    {
        Log::info('Проверка авторизации для обновления склада ID: ' . $this->route('warehouse'));
        return true;
    }

    /**
     * Правила валидации для обновления склада
     * Исправлено: корректное использование Rule::unique с игнорированием текущей записи
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('catalog_warehouses', 'title')
            ],
            'description' => 'nullable|string|max:1000',
            'contacts' => 'nullable|string|max:500',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0|max:999',
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
            'title.required' => 'Название склада обязательно для заполнения.',
            'title.string' => 'Название склада должно быть строкой.',
            'title.max' => 'Название склада не должно превышать 255 символов.',
            'title.unique' => 'Склад с таким названием уже существует.',
            'description.string' => 'Описание должно быть строкой.',
            'description.max' => 'Описание не должно превышать 1000 символов.',
            'contacts.string' => 'Контактная информация должна быть строкой.',
            'contacts.max' => 'Контактная информация не должна превышать 500 символов.',
            'is_active.required' => 'Статус активности обязателен для указания.',
            'is_active.boolean' => 'Статус активности должен быть логическим значением.',
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
            'title' => 'Название склада',
            'description' => 'Описание',
            'contacts' => 'Контактная информация',
            'is_active' => 'Статус активности',
        ];
    }

    /**
     * Подготовка данных для валидации
     * Преобразование строковых значений в boolean
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Преобразуем строковое значение is_active в boolean
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN)
            ]);
        }
        
        Log::info('Данные подготовлены для валидации: ' . json_encode($this->all()));
    }
}