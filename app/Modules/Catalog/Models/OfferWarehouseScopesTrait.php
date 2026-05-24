<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Трейт с областями видимости для модели OfferWarehouse
 */
trait OfferWarehouseScopesTrait
{
    /**
     * Область видимости для активных складов
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Область видимости для неактивных складов
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    /**
     * Область видимости для сортировки по порядку
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    /**
     * Получение всех активных складов
     *
     * @return Collection
     */
    public static function getAllActive(): Collection
    {
        return static::active()->ordered()->get();
    }
}
