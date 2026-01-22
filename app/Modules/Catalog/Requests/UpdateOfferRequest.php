<?php

namespace App\Modules\Catalog\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Запрос на обновление предложения товара
 * 
 * Валидация данных при обновлении существующего предложения товара.
 */
class UpdateOfferRequest extends FormRequest
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
        $offer = $this->route('offer');

        return [
            'articul_supplier' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'prices' => 'array',
            'prices.*.type' => 'required_with:prices.*.value|string|max:50',
            'prices.*.value' => 'required_with:prices.*.type|numeric|min:0',
            'attributes' => 'array',
            'attributes.*.type' => 'required_with:attributes.*.value|string|max:100',
            'attributes.*.value' => 'required_with:attributes.*.type|string|max:255',
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
            'name.required' => 'Название предложения обязательно',
            'prices.*.type.required_with' => 'Тип цены обязателен при указании значения',
            'prices.*.value.required_with' => 'Значение цены обязательно при указании типа',
            'prices.*.value.numeric' => 'Значение цены должно быть числом',
            'attributes.*.type.required_with' => 'Тип атрибута обязателен при указании значения',
            'attributes.*.value.required_with' => 'Значение атрибута обязательно при указании типа',
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
            'articul_supplier' => 'артикул',
            'name' => 'название предложения',
            'meta_title' => 'мета-заголовок',
            'meta_description' => 'мета-описание',
            'meta_keywords' => 'ключевые слова',
            'prices.*.type' => 'тип цены',
            'prices.*.value' => 'значение цены',
            'attributes.*.type' => 'тип атрибута',
            'attributes.*.value' => 'значение атрибута',
        ];
    }
}