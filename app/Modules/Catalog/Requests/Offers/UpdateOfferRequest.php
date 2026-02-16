<?php

namespace App\Modules\Catalog\Requests\Offers;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Запрос на обновление предложения товара
 *
 * Валидация данных при обновлении предложения товара.
 */
class UpdateOfferRequest extends FormRequest
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
            'vcode' => 'nullable|string|max:255',
            'articul_supplier' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'prices' => 'nullable|array',
            'prices.*.type_price_id' => 'required|exists:catalog_type_price,id',
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
            'name.required' => 'Название предложения обязательно',
            'prices.*.type_price_id.required' => 'Тип цены обязателен',
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
            'vcode' => 'v-код',
            'articul_supplier' => 'артикул',
            'name' => 'название предложения',
            'meta_title' => 'мета-заголовок',
            'meta_description' => 'мета-описание',
            'meta_keywords' => 'ключевые слова',
            'prices.*.type_price_id' => 'тип цены',
            'prices.*.value' => 'значение цены',
        ];
    }
}
