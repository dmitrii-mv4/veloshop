<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Трейт связей типа цены.
 *
 * @property Collection<CatalogOfferPrice> $offerPrices Вариации товара
 */

trait CatalogTypePriceRelationsTrait {

    /**
     * Отношение с ценами предложений
     *
     * @return HasMany
     */
    public function offerPrices(): HasMany
    {
        return $this->hasMany(CatalogOfferPrice::class, 'type_price_id');
    }
}
