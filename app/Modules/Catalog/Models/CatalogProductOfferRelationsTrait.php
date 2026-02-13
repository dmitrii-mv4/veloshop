<?php

namespace App\Modules\Catalog\Models;

use App\Modules\User\Models\User;
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
     * Отношение с наличием на складах
     *
     * @return HasMany
     */
    public function warehouseOffers(): HasMany
    {
        // Используем правильную модель для связи
        return $this->hasMany(CatalogOfferWarehouse::class, 'offer_id');
    }

    /**
     * Отношение с пользователем-создателем
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Отношение с пользователем-редактором
     *
     * @return BelongsTo
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function attributes()
    {
        return $this->morphToMany(CatalogAttributeValue::class, 'attributable');
    }
}
