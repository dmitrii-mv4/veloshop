<?php

namespace App\Modules\Catalog\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Трейт связей товара.
 *
 * @property Collection<CatalogProductOffer> $offers Вариации товара
 * @property Collection<CatalogAttribute> $catalogAttributes
 * @property CatalogCategory $category
 * @property User $creator Автор
 * @property User $editor Редактор
 */

trait ProductRelationsTrait {
    /**
     * Отношение с предложениями товара
     *
     * @return HasMany
     */
    public function offers(): HasMany
    {
        return $this->hasMany(CatalogProductOffer::class, 'product_id');
    }

    /**
     * Отношение с пользователем-создателем
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Отношение с пользователем-редактором
     *
     * @return BelongsTo
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Отношение с категорией
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CatalogCategory::class, 'category_id');
    }

    public function catalogAttributes()
    {
        return $this->morphToMany(CatalogAttribute::class,'attributable')->withPivot('value');
    }
}
