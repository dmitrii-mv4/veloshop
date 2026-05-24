<?php

namespace App\Modules\Catalog\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Трейт связей предложения товара.
 *
 * @property Collection<Product> $product Товар
 * @property Collection<CatalogOfferPrice> $prices Цены предложения
 * @property Collection<CatalogAttribute> $catalogAttributes
 * @property Collection<Tag> $tags
 * @property Collection<CatalogOfferWarehouse> $warehouseOffers Наличие на складах
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
     * Связь со складами через промежуточную таблицу
     *
     * @return BelongsToMany
     */
    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(
            CatalogWarehouse::class,
            'catalog_offers_warehouses',
            'offer_id',
            'warehouse_id'
        )->withPivot('count')->withTimestamps();
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

    public function catalogAttributes()
    {
        return $this->morphToMany(CatalogAttribute::class, 'attributable')->withPivot('value');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
