<?php

namespace App\Modules\Catalog\Requests\Basket;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Запрос на добавление оффера в корзину
 *
 * @property int $offer_id
 * @property int $quantity
 */
class AddToBasketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'offer_id' => 'required|integer|exists:catalog_product_offers,offer_id',
            'quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'offer_id.required' => 'ID оффера обязателен.',
            'offer_id.exists' => 'Выбранный оффер не найден.',
            'quantity.required' => 'Количество обязательно.',
            'quantity.min' => 'Количество должно быть не менее 1.',
        ];
    }
}
