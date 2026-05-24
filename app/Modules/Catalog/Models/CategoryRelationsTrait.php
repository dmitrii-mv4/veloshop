<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Трейт связей категории товаров.
 *
 * @property Collection<Category> $children
 * @property Collection<Product> $products
 */

trait CategoryRelationsTrait {

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->with('children');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
