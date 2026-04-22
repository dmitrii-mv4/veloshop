<?php

namespace App\Modules\User\Models;

use Illuminate\Database\Eloquent\Builder;

trait UserScopesTrait
{
    /**
     * Scope для поиска по имени или email
     */
    public function scopeSearch(Builder $query, string $search = null): Builder
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    /**
     * Scope для фильтрации по роли
     */
    public function scopeFilterByRole(Builder $query, $roleId = null): Builder
    {
        if (!$roleId || $roleId === 'all') {
            return $query;
        }

        return $query->where('role_id', $roleId);
    }

    /**
     * Scope для активных пользователей
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
