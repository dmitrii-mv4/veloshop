<?php

namespace App\Modules\Catalog\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Класс запроса отелей.
 */
class ProductRequest extends FormRequest
{
    /**
     * Авторизация действия.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Возвращает правила валидации.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'offer' => ['integer', 'exists:' . Offer::getTableName() . ',id'],
            'page' => ['integer', 'gt:0'],
            'perPage' => ['integer', 'gt:0'],
            'orderDirection' => ['string', 'in:ASC,DESC'],
            'orderBy' => ['string', 'in:rating,minimal_price_rub'],
        ];
    }
}
