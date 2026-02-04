<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Трейт связей предложения товара.
 *
 * @property Collection<Product> $product Товар
 * @property Collection<CatalogOfferPrice> $prices Цены предложения
 * @property Collection<CatalogOfferAttribute> $attributes Атрибуты предложения
 * @property Collection<CatalogWarehouseOffer> $warehouseOffers Наличие на складах
 * @property User $creator Автор
 * @property User $editor Редактор
 */

trait CatalogProductOfferRelationsTrait {
    /**
     * Отношение с товаром
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Отношение с ценами предложения
     *
     * @return HasMany
     */
    public function prices(): HasMany
    {
        return $this->hasMany(CatalogOfferPrice::class, 'offer_id');
    }

    /**
     * Отношение с атрибутами предложения
     *
     * @return HasMany
     */
    public function attributes(): HasMany
    {
        return $this->hasMany(CatalogOfferAttribute::class, 'offer_id', 'offer_id');
    }

    /**
     * Отношение с наличием на складах
     *
     * @return HasMany
     */
    public function warehouseOffers(): HasMany
    {
        // Используем правильную модель для связи
        return $this->hasMany(\App\Modules\Catalog\Models\CatalogOfferWarehouse::class, 'offer_id');
    }

    /**
     * Отношение с пользователем-создателем
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        if (class_exists(\App\Modules\User\Models\User::class)) {
            return $this->belongsTo(\App\Modules\User\Models\User::class, 'created_by');
        }

        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Отношение с пользователем-редактором
     *
     * @return BelongsTo
     */
    public function editor(): BelongsTo
    {
        if (class_exists(\App\Modules\User\Models\User::class)) {
            return $this->belongsTo(\App\Modules\User\Models\User::class, 'updated_by');
        }

        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
