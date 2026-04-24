<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Трейт связей предложения товара.
 *
 * @property Collection<Product> $product Товар
 * @property Collection<OfferPrice> $prices Цены предложения
 * @property Collection<Attribute> $catalogAttributes
 * @property Collection<Tag> $tags
 * @property Collection<OfferWarehouse> $warehouseOffers Наличие на складах
 */
trait OfferRelationsTrait
{
    /**
     * Отношение с товаром
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Отношение с ценами предложения
     */
    public function prices(): HasMany
    {
        return $this->hasMany(OfferPrice::class, 'offer_id');
    }

    /**
     * Отношение с наличием на складах
     */
    public function warehouseOffers(): HasMany
    {
        // Используем правильную модель для связи
        return $this->hasMany(OfferWarehouse::class, 'offer_id');
    }

    /**
     * Связь со складами через промежуточную таблицу
     */
    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(
            Warehouse::class,
            'catalog_offers_warehouses',
            'offer_id',
            'warehouse_id'
        )->withPivot('count')->withTimestamps();
    }

    public function catalogAttributes()
    {
        return $this->morphToMany(Attribute::class, 'attributable', 'catalog_attributables')->withPivot('value');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable', 'catalog_taggables');
    }
}
