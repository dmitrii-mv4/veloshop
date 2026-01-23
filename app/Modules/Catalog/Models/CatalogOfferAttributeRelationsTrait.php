<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Трейт связей атрибута предложения.
 *
 * @property CatalogProductOffer $offer Предложение товара
 */

trait CatalogOfferAttributeRelationsTrait {
    /**
     * Отношение с предложением товара
     *
     * @return BelongsTo
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(CatalogProductOffer::class, 'offer_id', 'offer_id');
    }
}