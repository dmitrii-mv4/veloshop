<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Трейт связей типа цены.
 *
 * @property Collection<OfferPrice> $offerPrices Вариации товара
 */
trait PriceTypeRelationsTrait
{
    /**
     * Отношение с ценами предложений
     */
    public function offerPrices(): HasMany
    {
        return $this->hasMany(OfferPrice::class, 'price_type_id');
    }
}
