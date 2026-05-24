<?php

namespace App\Modules\Catalog\Requests\Basket;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Запрос на создание корзины
 *
 * @property int|null $user_id
 * @property int|null $customer_id
 * @property array|null $offers
 */
class CreateBasketRequest extends FormRequest
{
    /**
     * Определить, авторизован ли пользователь для выполнения запроса.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Добавьте свою проверку прав, например:
        // return auth()->user()->hasPermission('catalog_baskets_create');
        return true;
    }

    /**
     * Правила валидации.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'user_id'     => 'nullable|integer|exists:users,id',
            'customer_id' => 'nullable|integer|exists:catalog_customers,id',
            'offers'      => 'nullable|array',
            'offers.*'    => 'integer|exists:catalog_offers,offer_id',
        ];
    }

    /**
     * Сообщения об ошибках.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'user_id.exists'     => 'Выбранный пользователь не существует.',
            'customer_id.exists' => 'Выбранный покупатель не существует.',
            'offers.*.exists'    => 'Один из выбранных товаров не найден.',
        ];
    }
}