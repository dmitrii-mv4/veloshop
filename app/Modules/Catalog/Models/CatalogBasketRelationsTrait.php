<?php

namespace App\Modules\Catalog\Models;


use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Трейт связей корзины.
 *
 * @property Customer $customer
 * @property Collection<CatalogBasketItem> $items
 * @property User $creator
 * @property User $updater
 */
trait CatalogBasketRelationsTrait
{
    /**
     * Связанный покупатель (если корзина привязана к клиенту каталога).
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
        return $this->hasMany(CatalogBasketItem::class, 'catalog_basket_id');
    }

    /**
     * Кто создал запись.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Кто последний обновил запись.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
