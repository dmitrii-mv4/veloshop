<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Трейт связей цены предложения.
 *
 * @property Offer $offer Предложение товара
 */
trait OfferPriceRelationsTrait
{
    public function priceType()
    {
        return $this->belongsTo(PriceType::class, 'price_type_id');
    }

    /**
     * Отношение с предложением товара
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class, 'offer_id', 'offer_id');
    }

    /**
     * Отношение с товаром
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Отношение с ценами предложения через соединительную таблицу
     */
    public function prices(): HasMany
    {
        return $this->hasMany(OfferPrice::class, 'offer_id', 'offer_id');
    }

    /**
     * Отношение с типами цен через промежуточную таблицу
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
     */
    public function attributes(): HasMany
    {
        return $this->hasMany(CatalogOfferAttribute::class, 'offer_id', 'offer_id');
    }

    /**
     * Отношение с наличием на складах
     */
    public function warehouseOffers(): HasMany
    {
        return $this->hasMany(CatalogWarehouseOffer::class, 'offer_id', 'offer_id');
    }
}
