<?php

namespace App\Modules\Role\Controllers;

use App\Core\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Role\Requests\RoleCreateRequest;
use App\Modules\Role\Requests\RoleEditRequest;
use App\Modules\Role\Models\Role;
use App\Modules\User\Models\User;
use App\Modules\Role\Models\Permission;
use App\Modules\Role\Models\RoleHasPermissions;

class RolesController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $users = User::with('role')->get();
        $roles = Role::get();

        //$this->authorize('viewAny', \App\Modules\User\Models\User::class);

        return view('role::index', compact('roles', 'users'));
    }

    public function create()
    {
        $roles = Role::get();

        return view('role::create', compact('roles'));
    }

    public function store(RoleCreateRequest $request)
    {
        $validated = $request->validated();

        // Создаем роль
        $role = Role::create([
            'name' => $validated['name'],
        ]);

        // Убираем название роли из массива
        $allPermission = array_diff_key($validated, ['name' => 0]);

        // Получаем массив имен разрешений из ключей массива
        $permissionNames = array_keys($allPermission);

        // Ищем соответствующие разрешения в БД через модель Permission
        $permissions = Permission::whereIn('name', $permissionNames)->get();

        // Подготавливаем данные для вставки в таблицу RoleHasPermissions
        $rolePermissionsData = [];
        
        foreach ($permissions as $permission) {
            $rolePermissionsData[] = [
                'role_id' => $role->id,
                'permission_id' => $permission->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Массово добавляем связи в таблицу RoleHasPermissions
        RoleHasPermissions::insert($rolePermissionsData);

        return redirect()->route('admin.roles')->with('success', 'Новая роль создана');
    }

    public function edit(Role $role)
    {
        return view('role::edit', compact('role'));
    }

    public function update(Role $role, RoleEditRequest $request)
    {
        $validated = $request->validated();

        // Обновляем роль
        $role->update([
            'name' => $validated['name'],
        ]);

        // Убираем название роли из массива
        $allPermission = array_diff_key($validated, ['name' => 0]);

        // Получаем массив имен разрешений из ключей массива
        $permissionNames = array_keys($allPermission);

        // Ищем соответствующие разрешения в БД через модель Permission
        $permissions = Permission::whereIn('name', $permissionNames)->get();

        // Подготавливаем данные для вставки в таблицу RoleHasPermissions
        $rolePermissionsData = [];
        
        foreach ($permissions as $permission) {
            $rolePermissionsData[] = [
                'role_id' => $role->id,
                'permission_id' => $permission->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Удаляем старые разрешения роли
        RoleHasPermissions::where('role_id', $role->id)->delete();

        // Массово добавляем новые связи в таблицу RoleHasPermissions
        RoleHasPermissions::insert($rolePermissionsData);

        return redirect()->route('admin.roles')->with('success', 'Роль изменена');
    }

    public function delete(Role $role)
    {
        // Удаление всех связанных разрешений
        $role->permissions()->detach();

        // Удаление роли
        $role->delete();

        return redirect()->route('admin.roles')->with('success', 'Роль удалена');
    }
}
