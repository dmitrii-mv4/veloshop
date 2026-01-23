<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Трейт связей склада.
 *
 * @property Collection<CatalogWarehouseOffer> $warehouseOffers Наличие товаров на складе
 */

trait CatalogWarehouseRelationsTrait {
    /**
     * Отношение с наличием товаров на складе
     *
     * @return HasMany
     */
    public function warehouseOffers(): HasMany
    {
        return $this->hasMany(CatalogWarehouseOffer::class, 'warehouses_id', 'id');
    }
}