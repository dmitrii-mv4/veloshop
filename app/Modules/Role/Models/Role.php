<?php

namespace App\Modules\Role\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\User\Models\User; // Добавляем импорт

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';
    protected $guarded = false;

    protected $fillable = [
        'name',
        'is_system'
    ];

    // Отношение с пользователями
    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'id');
    }

    // Отношение с разрешениями через промежуточную таблицу
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'role_has_permissions',
            'role_id',
            'permission_id'
        );
    }

    // Быстрый доступ к количеству пользователей
    public function getUsersCountAttribute()
    {
        if (!array_key_exists('users_count', $this->attributes)) {
            $this->attributes['users_count'] = $this->users()->count();
        }
        return $this->attributes['users_count'];
    }

    // Быстрый доступ к количеству разрешений
    public function getPermissionsCountAttribute()
    {
        if (!array_key_exists('permissions_count', $this->attributes)) {
            $this->attributes['permissions_count'] = $this->permissions()->count();
        }
        return $this->attributes['permissions_count'];
    }

    // Проверка, является ли роль системной
    public function isSystem(): bool
    {
        return (bool) $this->is_system;
    }

    // Проверка, можно ли удалить роль
    public function canBeDeleted(): bool
    {
        return !$this->isSystem() && $this->users_count == 0;
    }
}