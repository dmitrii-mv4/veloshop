<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Трейт связей значения атрибута каталога.
 *
 * @property Collection<CatalogAttributeValue> $attribute Значения атрибута
 * @property Collection<Product> $products
 * @property Collection<CatalogProductOffer> $offers
 */
trait CatalogAttributeRelationsTrait
{
    public function products()
    {
        return $this->morphedByMany(Product::class, 'attributable')->withPivot('value');
    }

    public function offers()
    {
        return $this->morphedByMany(CatalogProductOffer::class, 'attributable')->withPivot('value');
    }
}
