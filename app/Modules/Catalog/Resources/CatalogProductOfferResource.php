<?php

namespace App\Modules\Catalog\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Класс ресурса товара.
 *
 * @property int $id
 */
class CatalogProductOfferResource extends JsonResource
{
    /**
     * Преобразовывает ресурс в массив.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'product_id'            => $this->product_id,
            'offer_id'              => $this->offer_id,
            'vcode'                 => $this->vcode,
            'articul_supplier'      => $this->articul_supplier,
            'is_active'             => $this->is_active,
            'sort_order'            => $this->sort_order,
            'meta_title'            => $this->meta_title,
            'meta_description'      => $this->meta_description,
            'meta_keywords'         => $this->meta_keywords,
            'tags'                  => TagCollection::make($this->whenLoaded('tags', fn() => $this->tags)),
            'attributes'            => CatalogAttributeCollection::make($this->whenLoaded('catalogAttributes', fn() => $this->catalogAttributes)),
            'prices'                => CatalogOfferPriceCollection::make($this->whenLoaded('prices', fn() => $this->prices)),
            'stock'                 => CatalogOfferWarehouseCollection::make($this->whenLoaded('warehouseOffers', fn() => $this->warehouseOffers)),
        ];
    }
}
