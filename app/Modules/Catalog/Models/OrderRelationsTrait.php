<?php

namespace App\Modules\Catalog\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Трейт связей заказа.
 *
 * @property User $customer Покупатель
 * @property User $responsible Ответственный
 */
trait OrderRelationsTrait
{
    /**
     * Связь с пользователем-покупателем
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Связь с ответственным пользователем
     */
    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }
}
