<?php declare(strict_types=1);

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Трейт скоупов для атрибута предложения.
 *
 * @method static byType(string $attributeType)
 * @method static byValue(string $attributeValue)
 * @method static byOffer(string $offerId)
 * @method static byTypeAndValue(string $type, string $value)
 */

trait CatalogOfferAttributeScopesTrait
{
    /**
     * Фильтр по типу атрибута
     *
     * @param Builder $query
     * @param string $attributeType
     * @return Builder
     */
    public function scopeByType(Builder $query, string $attributeType): Builder
    {
        return $query->where('attributes_type', $attributeType);
    }

    /**
     * Фильтр по значению атрибута
     *
     * @param Builder $query
     * @param string $attributeValue
     * @return Builder
     */
    public function scopeByValue(Builder $query, string $attributeValue): Builder
    {
        return $query->where('attributes_value', $attributeValue);
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
     * Фильтр по типу и значению атрибута
     *
     * @param Builder $query
     * @param string $type
     * @param string $value
     * @return Builder
     */
    public function scopeByTypeAndValue(Builder $query, string $type, string $value): Builder
    {
        return $query->where('attributes_type', $type)
                    ->where('attributes_value', $value);
    }

    /**
     * Поиск атрибутов по значению (LIKE)
     *
     * @param Builder $query
     * @param string $searchTerm
     * @return Builder
     */
    public function scopeSearchByValue(Builder $query, string $searchTerm): Builder
    {
        return $query->where('attributes_value', 'LIKE', "%{$searchTerm}%");
    }

    /**
     * Фильтр по нескольким типам атрибутов
     *
     * @param Builder $query
     * @param array $types
     * @return Builder
     */
    public function scopeByTypes(Builder $query, array $types): Builder
    {
        return $query->whereIn('attributes_type', $types);
    }
}