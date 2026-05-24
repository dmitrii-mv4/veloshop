<?php

namespace App\Modules\Catalog\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Класс ресурса корзины.
 *
 * @property int $id
 * @property float $total_price
 * @property int $total_quantity
 * @property array $items
 */
class BasketResource extends JsonResource
{
    /**
     * Преобразовывает ресурс в массив.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'total_price'       => $this->total_price,
            'total_quantity'    => $this->total_quantity,
            'items'             => BasketItemCollection::make($this->items),
        ];
    }
}
