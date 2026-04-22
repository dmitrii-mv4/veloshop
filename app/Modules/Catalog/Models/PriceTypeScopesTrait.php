<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Трейт скоупов типа цены.
 */

trait PriceTypeScopesTrait {

    /**
     * Получить активные типы цен
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Получить типы цен отсортированные по порядку
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    /**
     * Получить тип цены по техническому идентификатору
     *
     * @param Builder $query
     * @param string $type
     * @return Builder
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
