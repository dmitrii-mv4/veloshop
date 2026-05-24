<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Трейт связей товара.
 *
 * @property Collection<Offer> $offers Вариации товара
 */
trait ProductRelationsTrait
{
    /**
     * Отношение с предложениями товара
     */
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'product_id');
    }

    /**
     * Отношение с категорией
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function catalogAttributes()
    {
        return $this->morphToMany(Attribute::class, 'attributable', 'catalog_attributables')->withPivot('value');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable', 'catalog_taggables');
    }
}
