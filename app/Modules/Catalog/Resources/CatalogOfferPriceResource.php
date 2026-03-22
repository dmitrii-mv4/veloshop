<?php

namespace App\Modules\Catalog\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Класс ресурса типа цены товара.
 *
 * @property int $id
 * @property string $offer_id
 * @property string $price_type
 * @property float $price
 */
class CatalogOfferPriceResource extends JsonResource
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
            'price_type'    => $this->price_type,
            'price'         => $this->price,
        ];
    }
}
