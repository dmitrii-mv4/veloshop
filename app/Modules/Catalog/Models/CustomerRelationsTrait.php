<?php

namespace App\Modules\Catalog\Models;


use App\Modules\User\Models\User;

/**
 * Трейт связей покупателя.
 *
 * @property User $user
 */
trait CustomerRelationsTrait
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
