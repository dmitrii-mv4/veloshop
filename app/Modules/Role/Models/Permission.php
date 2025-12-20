<?php

namespace App\Modules\Role\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'title'];

    // Отношение с ролями через промежуточную таблицу
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'role_has_permissions',
            'permission_id',
            'role_id'
        );
    }

    // Ищем по коду модуля
    public static function findByCodeModule($moduleForPermission)
    {
        return self::where('name', 'like', $moduleForPermission . '_%')->get();
    }

    // Получаем все разрешения сгруппированные по модулям
    public static function getGroupedByModule()
    {
        $permissions = self::all();
        $grouped = [];

        foreach ($permissions as $permission) {
            $parts = explode('_', $permission->name);
            $module = $parts[0] ?? 'general';
            
            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }
            
            $grouped[$module][] = $permission;
        }

        return $grouped;
    }

    // Получаем разрешения для определенного модуля
    public static function getForModule($moduleName)
    {
        return self::where('name', 'like', $moduleName . '_%')->get();
    }
}