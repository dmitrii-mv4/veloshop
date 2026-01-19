<?php

namespace App\Modules\Catalog\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request класс для валидации данных при создании заказа
 * 
 * Содержит правила валидации и сообщения об ошибках для формы создания заказа
 */
class OrdersCreateRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь для выполнения запроса
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Разрешаем всем авторизованным пользователям
    }

    /**
     * Возвращает правила валидации для запроса
     * 
     * @return array
     */
    public function rules(): array
    {
        return [
            'order_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('catalog_orders', 'order_number')
            ],
            'customer_id' => [
                'required',
                'exists:users,id'
            ],
            'is_paid' => [
                'boolean'
            ],
            'is_cancelled' => [
                'boolean'
            ],
            'has_problem' => [
                'boolean'
            ],
            'total_amount' => [
                'required',
                'numeric',
                'min:0'
            ],
            'responsible_id' => [
                'nullable',
                'exists:users,id'
            ],
            'comment' => [
                'nullable',
                'string',
                'max:2000'
            ],
            'cancellation_reason' => [
                Rule::requiredIf(function () {
                    return $this->boolean('is_cancelled');
                }),
                'nullable',
                'string',
                'max:2000'
            ],
            'problem_description' => [
                Rule::requiredIf(function () {
                    return $this->boolean('has_problem');
                }),
                'nullable',
                'string',
                'max:2000'
            ]
        ];
    }

    /**
     * Возвращает пользовательские сообщения об ошибках валидации
     * 
     * @return array
     */
    public function messages(): array
    {
        return [
            'order_number.required' => 'Номер заказа обязателен для заполнения',
            'order_number.unique' => 'Заказ с таким номером уже существует',
            'order_number.max' => 'Номер заказа не должен превышать 100 символов',
            
            'customer_id.required' => 'Выберите покупателя',
            'customer_id.exists' => 'Выбранный покупатель не существует в системе',
            
            'total_amount.required' => 'Сумма заказа обязательна для заполнения',
            'total_amount.numeric' => 'Сумма заказа должна быть числом',
            'total_amount.min' => 'Сумма заказа не может быть отрицательной',
            
            'responsible_id.exists' => 'Выбранный ответственный не существует в системе',
            
            'comment.max' => 'Комментарий не должен превышать 2000 символов',
            
            'cancellation_reason.required' => 'Причина отмены обязательна при отмене заказа',
            'cancellation_reason.max' => 'Причина отмены не должна превышать 2000 символов',
            
            'problem_description.required' => 'Описание проблемы обязательно при наличии проблем с заказом',
            'problem_description.max' => 'Описание проблемы не должно превышать 2000 символов',
        ];
    }

    /**
     * Возвращает пользовательские атрибуты для сообщений об ошибках
     * 
     * @return array
     */
    public function attributes(): array
    {
        return [
            'order_number' => 'Номер заказа',
            'customer_id' => 'Покупатель',
            'is_paid' => 'Оплачен',
            'is_cancelled' => 'Отменён',
            'has_problem' => 'Проблема',
            'total_amount' => 'Сумма заказа',
            'responsible_id' => 'Ответственный',
            'comment' => 'Комментарий',
            'cancellation_reason' => 'Причина отмены',
            'problem_description' => 'Описание проблемы',
        ];
    }

    /**
     * Подготовка данных для валидации
     * 
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Приводим булевые значения к правильному формату
        $this->merge([
            'is_paid' => $this->boolean('is_paid'),
            'is_cancelled' => $this->boolean('is_cancelled'),
            'has_problem' => $this->boolean('has_problem'),
        ]);

        // Если сумма не указана, устанавливаем 0
        if (!$this->has('total_amount') || $this->total_amount === null) {
            $this->merge(['total_amount' => 0]);
        }

        // Очищаем причину отмены и описание проблемы, если чекбоксы не отмечены
        if (!$this->boolean('is_cancelled')) {
            $this->merge(['cancellation_reason' => null]);
        }

        if (!$this->boolean('has_problem')) {
            $this->merge(['problem_description' => null]);
        }
    }
}