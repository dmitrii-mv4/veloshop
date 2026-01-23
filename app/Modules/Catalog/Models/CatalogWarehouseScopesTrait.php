<?php declare(strict_types=1);

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Трейт скоупов для склада.
 *
 * @method static searchByAddress(string $searchTerm)
 * @method static withTotalQuantity()
 * @method static hasProducts()
 * @method static byPhone(string $phone)
 * @method static byEmail(string $email)
 */

trait CatalogWarehouseScopesTrait
{
    /**
     * Поиск складов по адресу
     *
     * @param Builder $query
     * @param string $searchTerm
     * @return Builder
     */
    public function scopeSearchByAddress(Builder $query, string $searchTerm): Builder
    {
        return $query->where('address', 'LIKE', "%{$searchTerm}%");
    }

    /**
     * Загрузка общего количества товаров на складе
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithTotalQuantity(Builder $query): Builder
    {
        return $query->withCount(['warehouseOffers as total_quantity' => function ($q) {
            $q->selectRaw('SUM(quantity)');
        }]);
    }

    /**
     * Только склады с товарами
     *
     * @param Builder $query
     * @param int $minQuantity
     * @return Builder
     */
    public function scopeHasProducts(Builder $query, int $minQuantity = 1): Builder
    {
        return $query->whereHas('warehouseOffers', function ($q) use ($minQuantity) {
            $q->havingRaw('SUM(quantity) >= ?', [$minQuantity]);
        });
    }

    /**
     * Только пустые склады
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeEmpty(Builder $query): Builder
    {
        return $query->whereDoesntHave('warehouseOffers', function ($q) {
            $q->havingRaw('SUM(quantity) > 0');
        });
    }

    /**
     * Фильтр по телефону
     *
     * @param Builder $query
     * @param string $phone
     * @return Builder
     */
    public function scopeByPhone(Builder $query, string $phone): Builder
    {
        return $query->where('phone', $phone);
    }

    /**
     * Фильтр по email
     *
     * @param Builder $query
     * @param string $email
     * @return Builder
     */
    public function scopeByEmail(Builder $query, string $email): Builder
    {
        return $query->where('email', $email);
    }

    /**
     * Фильтр по режиму работы
     *
     * @param Builder $query
     * @param string $operatingMode
     * @return Builder
     */
    public function scopeByOperatingMode(Builder $query, string $operatingMode): Builder
    {
        return $query->where('operating_mode', 'LIKE', "%{$operatingMode}%");
    }
}