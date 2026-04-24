<?php

namespace App\Modules\Catalog\Models;

/**
 * Трейт с отношениями для модели CatalogWarehouse
 */
trait CatalogWarehouseRelationsTrait
{
    /**
     * Связь с остатками товаров на складе
     */
    public function warehouseOffers()
    {
        return $this->hasMany(CatalogOfferWarehouse::class, 'warehouse_id');
    }

    /**
     * Связь с предложениями через остатки
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
}
