<?php

namespace App\Modules\Catalog\Models;

/**
 * Трейт связей значения атрибута каталога.
 *
 * @property Collection $attribute Значения атрибута
 * @property Collection<Product> $products
 * @property Collection<Offer> $offers
 */
trait AttributeRelationsTrait
{
    public function products()
    {
        return $this->morphedByMany(Product::class, 'attributable', 'catalog_attributables')->withPivot('value');
    }

    public function offers()
    {
        return $this->morphedByMany(Offer::class, 'attributable', 'catalog_attributables')->withPivot('value');
    }
}
