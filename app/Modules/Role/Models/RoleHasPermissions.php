<?php

namespace App\Modules\Role\Models;

use Illuminate\Database\Eloquent\Model;

class RoleHasPermissions extends Model
{
    protected $table = 'role_has_permissions';
    
    protected $fillable = [
        'role_id',
        'permission_id'
    ];
}
