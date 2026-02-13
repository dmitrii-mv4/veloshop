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
trait CatalogAttributeValueRelationsTrait
{
    /**
     * Отношение с атрибутом
     *
     * @return BelongsTo
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(CatalogAttribute::class, 'attribute_id');
    }

    public function products()
    {
        return $this->morphedByMany(Product::class, 'attributable');
    }

    public function offers()
    {
        return $this->morphedByMany(CatalogProductOffer::class, 'attributable');
    }
}
