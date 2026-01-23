<?php declare(strict_types=1);

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Трейт скоупов для наличия товара на складе.
 *
 * @method static byOffer(string $offerId)
 * @method static byWarehouse(int $warehouseId)
 * @method static inStock(int $minQuantity)
 * @method static outOfStock()
 * @method static byQuantityRange(int $min, int $max)
 */

trait CatalogWarehouseOfferScopesTrait
{
    /**
     * Фильтр по предложению
     *
     * @param Builder $query
     * @param string $offerId
     * @return Builder
     */
    public function scopeByOffer(Builder $query, string $offerId): Builder
    {
        return $query->where('offer_id', $offerId);
    }

    /**
     * Фильтр по складу
     *
     * @param Builder $query
     * @param int $warehouseId
     * @return Builder
     */
    public function scopeByWarehouse(Builder $query, int $warehouseId): Builder
    {
        return $query->where('warehouses_id', $warehouseId);
    }

    /**
     * Только позиции в наличии
     *
     * @param Builder $query
     * @param int $minQuantity
     * @return Builder
     */
    public function scopeInStock(Builder $query, int $minQuantity = 1): Builder
    {
        return $query->where('quantity', '>=', $minQuantity);
    }

    /**
     * Только позиции отсутствующие в наличии
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('quantity', '<=', 0);
    }

    /**
     * Фильтр по диапазону количества
     *
     * @param Builder $query
     * @param int $min
     * @param int $max
     * @return Builder
     */
    public function scopeByQuantityRange(Builder $query, int $min, int $max): Builder
    {
        return $query->whereBetween('quantity', [$min, $max]);
    }

    /**
     * Только позиции с нулевым количеством
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeZeroQuantity(Builder $query): Builder
    {
        return $query->where('quantity', 0);
    }

    /**
     * Только позиции с положительным количеством
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePositiveQuantity(Builder $query): Builder
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Сортировка по количеству (убывание)
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOrderByQuantityDesc(Builder $query): Builder
    {
        return $query->orderBy('quantity', 'desc');
    }

    /**
     * Сортировка по количеству (возрастание)
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOrderByQuantityAsc(Builder $query): Builder
    {
        return $query->orderBy('quantity', 'asc');
    }
}