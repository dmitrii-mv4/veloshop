<?php

namespace App\Modules\Catalog\Models;

/**
 * Трейт с отношениями для модели CatalogWarehouse
 */
trait CatalogWarehouseRelationsTrait
{
    /**
     * Связь с остатками товаров на складе
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function warehouseOffers()
    {
        return $this->hasMany(CatalogOfferWarehouse::class, 'warehouse_id');
    }

    /**
     * Связь с предложениями через остатки
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function offers()
    {
        return $this->belongsToMany(
            \App\Modules\Catalog\Models\Offer::class,
            'catalog_offers_warehouses',
            'warehouse_id',
            'offer_id'
        )->withPivot('count')->withTimestamps();
    }

    /**
     * Связь с пользователем, создавшим запись
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'created_by');
    }

    /**
     * Связь с пользователем, обновившим запись
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function editor()
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'updated_by');
    }
}
