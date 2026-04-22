<?php

namespace App\Modules\Catalog\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Класс ресурса товара в корзине.
 *
 * @property int $id
 * @property int $offer_id
 * @property int $quantity
 * @property float $total_price
 */
class BasketItemResource extends JsonResource
{
    /**
     * Преобразовывает ресурс в массив.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'offer_id'      => $this->offer_id,
            'quantity'      => $this->quantity,
            'total_price'   => $this->offer->getPrice() * $this->quantity,
        ];
    }
}
