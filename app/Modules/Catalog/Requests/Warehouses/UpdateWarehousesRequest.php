<?php

namespace App\Modules\Catalog\Requests\Warehouses;

use App\Modules\Catalog\Models\Warehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Запрос на обновление информации о складе
 * Включает валидацию данных для редактирования существующего склада
 */
class UpdateWarehousesRequest extends FormRequest
{
    /**
     * Определение прав доступа для запроса
     */
    public function authorize(): bool
    {
        Log::info('Проверка авторизации для обновления склада ID: '.$this->route('warehouse'));

        return true;
    }

    /**
     * Правила валидации для обновления склада
     * Исправлено: корректное использование Rule::unique с игнорированием текущей записи
     */
    public function rules(): array
    {
        $warehouseId = $this->route('warehouse')->id ?? null;

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Warehouse::getTableName(), 'title')->ignore($warehouseId),
            ],
            'description' => 'nullable|string|max:1000',
            'contacts' => 'nullable|string|max:500',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0|max:999',
        ];
    }

    /**
     * Сообщения об ошибках валидации
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
     */
    protected function prepareForValidation(): void
    {
        // Преобразуем строковое значение is_active в boolean
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        Log::info('Данные подготовлены для валидации: '.json_encode($this->all()));
    }
}
