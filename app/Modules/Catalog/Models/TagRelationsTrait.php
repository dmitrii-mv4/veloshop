<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Трейт связей тега.
 *
 * @property Collection<Product> $products
 * @property Collection<CatalogProductOffer> $offers
 */
trait TagRelationsTrait
{
    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'taggable');
    }

    public function offers(): MorphToMany
    {
        return $this->morphedByMany(CatalogProductOffer::class, 'taggable');
    }
}
