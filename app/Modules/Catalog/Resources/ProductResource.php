<?php

namespace App\Modules\Catalog\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Класс ресурса товара.
 *
 * @property int $id
 * @property string $name
 * @property string $product_id
 * @property int $category_id
 * @property string|null $brand
 * @property string|null $model
 * @property string|null $seazon
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property array $offers
 */
class ProductResource extends JsonResource
{
    /**
     * Преобразовывает ресурс в массив.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' =>         $this->id,
            'name' =>       $this->name,
            'product_id'    => $this->product_id,
            'category_id'   => $this->category_id,
            'brand'         => $this->brand ,
            'model '        => $this->model ,
            'seazon'        => $this->seazon,
            'offers'        => CatalogProductOfferCollection::make($this->offers()->get()),
            'attributes'    => CatalogAttributeCollection::make($this->catalogAttributes()->get()),
        ];
    }
}
