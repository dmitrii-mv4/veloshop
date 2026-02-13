<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Трейт связей атрибута каталога.
 *
 * @property Collection<CatalogAttributeValue> $values Значения атрибута
 */
trait CatalogAttributeRelationsTrait
{
    /**
     * Отношение с значениями атрибута
     */
    public function values(): MorphMany
    {
        return $this->hasMany(CatalogAttributeValue::class, 'attribute_id');
    }
}
