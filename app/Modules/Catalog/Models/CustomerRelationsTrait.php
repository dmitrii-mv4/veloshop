<?php

namespace App\Modules\Catalog\Models;


use App\Modules\User\Models\User;

/**
 * Трейт связей покупателя.
 *
 * @property User $user
 * @property CustomerType $type
 * @property User $creator
 * @property User $updater
 * @property User $deletor
 */
trait CustomerRelationsTrait
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function type()
    {
        return $this->belongsTo(CustomerType::class, 'type_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
