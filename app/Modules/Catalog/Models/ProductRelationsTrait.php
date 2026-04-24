<?php

namespace App\Modules\Catalog\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Трейт связей товара.
 *
 * @property Collection<CatalogProductOffer> $offers Вариации товара
 * @property Collection<CatalogAttribute> $catalogAttributes
 * @property Collection<Tag> $tags
 * @property CatalogCategory $category
 * @property User $creator Автор
 * @property User $editor Редактор
 */
trait ProductRelationsTrait
{
    /**
     * Отношение с предложениями товара
     */
    public function offers(): HasMany
    {
        return $this->hasMany(CatalogProductOffer::class, 'product_id');
    }

    /**
     * Отношение с пользователем-создателем
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Отношение с пользователем-редактором
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Отношение с категорией
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CatalogCategory::class, 'category_id');
    }

    public function catalogAttributes()
    {
        return $this->morphToMany(CatalogAttribute::class, 'attributable', 'catalog_attributables')->withPivot('value');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable', 'catalog_taggables');
    }
}
