<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Трейт связей цены предложения.
 *
 * @property CatalogProductOffer $offer Предложение товара
 */

trait CatalogOfferPriceRelationsTrait {

    public function priceType()
    {
        return $this->belongsTo(PriceType::class, 'price_type_id');
    }

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
}
