<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Трейт связей категории товаров.
 *
 * @property Collection<CatalogCategory> $children
 * @property Collection<Product> $products
 */

trait CatalogCategoryRelationsTrait {

    public function children(): HasMany
    {
        return $this->hasMany(CatalogCategory::class, 'parent_id')->with('children');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
