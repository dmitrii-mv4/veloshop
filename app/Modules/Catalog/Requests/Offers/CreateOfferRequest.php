<?php

namespace App\Modules\Catalog\Requests\Offers;

use App\Modules\Catalog\Models\PriceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Запрос на создание предложения товара
 *
 * Валидация данных при создании нового предложения товара.
 */
class CreateOfferRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь для выполнения запроса
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Правила валидации
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'offer_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('catalog_product_offers', 'offer_id')
            ],
            'vcode' => 'nullable|string|max:255',
            'articul_supplier' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'prices' => 'nullable|array',
            'prices.*.price_type_id' => 'nullable|exists:' . PriceType::getTableName() . ',id',
            'prices.*.value' => 'nullable|numeric|min:0',
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
            'offer_id.required' => 'Уникальный ID предложения обязателен',
            'offer_id.unique' => 'Предложение с таким ID уже существует',
            'name.required' => 'Название предложения обязательно',
            'prices.*.price_type_id.required' => 'Тип цены обязателен',
            'prices.*.value.numeric' => 'Значение цены должно быть числом',
        ];
    }

    /**
     * Кастомные имена атрибутов для сообщений об ошибках
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'offer_id' => 'уникальный ID предложения',
            'vcode' => 'v-код',
            'articul_supplier' => 'артикул',
            'name' => 'название предложения',
            'meta_title' => 'мета-заголовок',
            'meta_description' => 'мета-описание',
            'meta_keywords' => 'ключевые слова',
            'prices.*.price_type_id' => 'тип цены',
            'prices.*.value' => 'значение цены',
        ];
    }
}
