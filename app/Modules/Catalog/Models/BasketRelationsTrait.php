<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Трейт связей корзины.
 *
 * @property Customer $customer
 * @property Collection<BasketItem> $items
 */
trait BasketRelationsTrait
{

    /**
     * Связанный покупатель
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Товары в корзине.
     *
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(BasketItem::class, 'basket_id');
    }
}
