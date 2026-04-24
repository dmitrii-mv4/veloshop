<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Трейт связей тега.
 *
 * @property Collection<Product> $products
 * @property Collection<Offer> $offers
 */
trait TagRelationsTrait
{
    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'taggable', 'catalog_taggables');
    }

    public function offers(): MorphToMany
    {
        return $this->morphedByMany(Offer::class, 'taggable', 'catalog_taggables');
    }
}
