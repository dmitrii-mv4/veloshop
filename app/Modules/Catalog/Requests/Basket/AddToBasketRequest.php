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
            'offer_id' => 'required|integer|exists:catalog_product_offers,id',
            'quantity' => 'required|integer|not_in:0',
        ];
    }

    public function messages(): array
    {
        return [
            'offer_id.required' => 'ID оффера обязателен.',
            'offer_id.exists'   => 'Выбранный оффер не найден.',
            'quantity.required' => 'Количество обязательно.',
            'quantity.not_in'   => 'Количество не может быть 0.',
        ];
    }
}
