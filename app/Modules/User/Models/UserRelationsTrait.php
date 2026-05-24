<?php

namespace App\Modules\User\Models;

use App\Modules\Role\Models\Role;

trait UserRelationsTrait
{
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function permissions()
    {
        return $this->role->permissions();
    }

    public function hasPermission($permissionName)
    {
        return $this->permissions()->where('name', $permissionName)->exists();
    }
}
