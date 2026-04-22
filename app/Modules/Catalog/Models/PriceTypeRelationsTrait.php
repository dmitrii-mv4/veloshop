<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Трейт связей типа цены.
 *
 * @property Collection<CatalogOfferPrice> $offerPrices Вариации товара
 */

trait PriceTypeRelationsTrait {

    /**
     * Отношение с ценами предложений
     *
     * @return HasMany
     */
    public function offerPrices(): HasMany
    {
        return $this->hasMany(CatalogOfferPrice::class, 'price_type_id');
    }
}
