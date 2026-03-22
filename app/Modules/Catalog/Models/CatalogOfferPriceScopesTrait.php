<?php declare(strict_types=1);

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Трейт скоупов для цены предложения.
 *
 * @method static byType(string $priceType)
 * @method static byPriceRange(float $min, float $max)
 * @method static byOffer(string $offerId)
 */

trait CatalogOfferPriceScopesTrait
{
    /**
     * Фильтр по типу цены
     *
     * @param Builder $query
     * @param string $priceType
     * @return Builder
     */
    public function scopeByType(Builder $query, string $priceType): Builder
    {
        return $query->where('price_type', $priceType);
    }

    /**
     * Фильтр по диапазону цен
     *
     * @param Builder $query
     * @param float $min
     * @param float $max
     * @return Builder
     */
    public function scopeByPriceRange(Builder $query, float $min, float $max): Builder
    {
        return $query->whereBetween('price', [$min, $max]);
    }

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
     * Только цены выше указанного значения
     *
     * @param Builder $query
     * @param float $price
     * @return Builder
     */
    public function scopePriceAbove(Builder $query, float $price): Builder
    {
        return $query->where('price', '>', $price);
    }

    /**
     * Только цены ниже указанного значения
     *
     * @param Builder $query
     * @param float $price
     * @return Builder
     */
    public function scopePriceBelow(Builder $query, float $price): Builder
    {
        return $query->where('price', '<', $price);
    }
}