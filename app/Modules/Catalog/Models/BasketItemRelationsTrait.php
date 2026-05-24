<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Трейт связей корзины.
 *
 * @property Basket $basket
 * @property Offer $offer
 */
trait BasketItemRelationsTrait
{
    /**
     * Корзина, к которой относится элемент.
     */
    public function basket(): BelongsTo
    {
        return $this->belongsTo(Basket::class, 'basket_id');
    }

    /**
     * Оффер (предложение товара).
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class, 'offer_id');
    }
}
