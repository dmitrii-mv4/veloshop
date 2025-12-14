<?php

namespace App\Modules\Role\Policies;

use App\Modules\Role\Models\Role;
use App\Modules\User\Models\User;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Получаем все разрешения пользователя
        $permissions = $user->permissions;

        // Поиск конкретного разрешения по имени
        $showPermission = $permissions->firstWhere('name', 'roles_viewAny');

        if ($showPermission)
        {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $role): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Получаем все разрешения пользователя
        $permissions = $user->permissions;

        // Поиск конкретного разрешения по имени
        $showPermission = $permissions->firstWhere('name', 'roles_create');

        if ($showPermission)
        {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        // Получаем все разрешения пользователя
        $permissions = $user->permissions;

        // Поиск конкретного разрешения по имени
        $showPermission = $permissions->firstWhere('name', 'roles_update');

        if ($showPermission)
        {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        // Получаем все разрешения пользователя
        $permissions = $user->permissions;

        // Поиск конкретного разрешения по имени
        $showPermission = $permissions->firstWhere('name', 'roles_delete');

        if ($showPermission)
        {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Role $role): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Role $role): bool
    {
        return false;
    }
}
