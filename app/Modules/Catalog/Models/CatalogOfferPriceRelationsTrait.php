<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Трейт связей цены предложения.
 *
 * @property CatalogProductOffer $offer Предложение товара
 */

trait CatalogOfferPriceRelationsTrait {
    /**
     * Отношение с предложением товара
     *
     * @return BelongsTo
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(CatalogProductOffer::class, 'offer_id', 'offer_id');
    }

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
     * Отношение с ценами предложения через соединительную таблицу
     *
     * @return HasMany
     */
    public function prices(): HasMany
    {
        return $this->hasMany(CatalogOfferPrice::class, 'offer_id', 'offer_id');
    }

    /**
     * Отношение с типами цен через промежуточную таблицу
     *
     * @return BelongsToMany
     */
    public function priceTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            PriceType::class,
            'catalog_offers_prices',
            'offer_id',
            'price_type_id'
        )->withPivot('price')->withTimestamps();
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
        return $this->hasMany(CatalogWarehouseOffer::class, 'offer_id', 'offer_id');
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
