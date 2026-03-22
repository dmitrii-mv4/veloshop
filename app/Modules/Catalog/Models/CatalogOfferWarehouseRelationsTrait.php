<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Трейт связей наличия товара на складе.
 *
 * @property CatalogProductOffer $offer Предложение товара
 * @property CatalogWarehouse $warehouse Склад
 */

trait CatalogOfferWarehouseRelationsTrait {
    /**
     * Отношение с предложением товара
     *
     * @return BelongsTo
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(CatalogProductOffer::class, 'offer_id');
    }

    /**
     * Отношение со складом
     *
     * @return BelongsTo
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(CatalogWarehouse::class, 'warehouses_id');
    }
}
