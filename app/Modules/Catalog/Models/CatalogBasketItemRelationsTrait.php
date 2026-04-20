<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Трейт связей корзины.
 *
 * @property CatalogBasket $basket
 * @property CatalogProductOffer $offer
 */
trait CatalogBasketItemRelationsTrait
{

    /**
     * Корзина, к которой относится элемент.
     */
    public function basket(): BelongsTo
    {
        return $this->belongsTo(CatalogBasket::class, 'basket_id');
    }

    /**
     * Оффер (предложение товара).
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(CatalogProductOffer::class, 'offer_id');
    }
}
