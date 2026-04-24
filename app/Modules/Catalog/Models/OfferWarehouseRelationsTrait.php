<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Трейт связей наличия товара на складе.
 *
 * @property Offer $offer Предложение товара
 * @property Warehouse $warehouse Склад
 */
trait OfferWarehouseRelationsTrait
{
    /**
     * Отношение с предложением товара
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class, 'offer_id');
    }

    /**
     * Отношение со складом
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouses_id');
    }
}
