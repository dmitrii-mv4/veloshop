<?php

namespace App\Modules\Role\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'title'];

    // Ищем по коду модуля
    public static function findByCodeModule($moduleForPermission)
    {
        return self::where('name', 'like', $moduleForPermission . '_%')->get();
    }
}
