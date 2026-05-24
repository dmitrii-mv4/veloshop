<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Трейт скоупов для предложения товара.
 *
 * @method static fullTextSearch(string $searchTerm)
 * @method static byProduct(string $productId)
 * @method static withQuantity()
 * @method static inStock()
 * @method static outOfStock()
 */
trait OfferScopesTrait
{
    /**
     * Поиск предложений с использованием полнотекстового поиска
     */
    public function scopeFullTextSearch(Builder $query, string $searchTerm): Builder
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            // Используем полнотекстовый поиск PostgreSQL
            return $query->whereRaw("
                to_tsvector('russian',
                    COALESCE(name, '') || ' ' ||
                    COALESCE(articul_supplier, '')
                ) @@ plainto_tsquery('russian', ?)
            ", [$searchTerm]);
        } else {
            // Для MySQL используем LIKE поиск
            return $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('articul_supplier', 'LIKE', "%{$searchTerm}%");
            });
        }
    }

    /**
     * Фильтр по товару
     */
    public function scopeByProduct(Builder $query, string $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Загрузка количества на складах
     */
    public function scopeWithQuantity(Builder $query): Builder
    {
        return $query->with(['warehouseOffers' => function ($q) {
            $q->select(['offer_id', 'warehouses_id', 'quantity']);
        }]);
    }

    /**
     * Только предложения в наличии
     */
    public function scopeInStock(Builder $query, int $minQuantity = 1): Builder
    {
        return $query->whereHas('warehouseOffers', function ($q) use ($minQuantity) {
            $q->havingRaw('SUM(quantity) >= ?', [$minQuantity]);
        });
    }

    /**
     * Только предложения отсутствующие в наличии
     */
    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->whereDoesntHave('warehouseOffers', function ($q) {
            $q->havingRaw('SUM(quantity) > 0');
        });
    }
}
