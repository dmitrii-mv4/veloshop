<?php

namespace App\Modules\Role\Controllers;

use App\Core\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Role\Requests\RoleCreateRequest;
use App\Modules\Role\Requests\RoleEditRequest;
use App\Modules\Role\Models\Role;
use App\Modules\User\Models\User;
use App\Modules\Role\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolesController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $type = $request->get('type', 'all');
        $perPage = $request->get('per_page', 10);
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        $query = Role::with(['permissions'])
            ->withCount(['users', 'permissions']);
        
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        
        if ($type !== 'all') {
            $query->where('is_system', $type === 'system');
        }
        
        if ($sortBy === 'users_count') {
            $query->orderBy('users_count', $sortOrder);
        } elseif ($sortBy === 'permissions_count') {
            $query->orderBy('permissions_count', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }
        
        $roles = $query->paginate($perPage);
        $users = User::with('role')->get();
        
        $totalRoles = Role::count();
        $systemRoles = Role::where('is_system', true)->count();
        $customRoles = Role::where('is_system', false)->count();
        
        return view('role::index', compact(
            'roles',
            'users',
            'search',
            'type',
            'perPage',
            'sortBy',
            'sortOrder',
            'totalRoles',
            'systemRoles',
            'customRoles'
        ));
    }

    public function create()
    {
        $permissions = Permission::all();
        $groupedPermissions = $this->groupPermissionsByModule($permissions);
        
        return view('role::create', compact('groupedPermissions'));
    }

    public function store(RoleCreateRequest $request)
    {
        $validated = $request->validated();

        $role = Role::create([
            'name' => $validated['name'],
            'is_system' => false,
        ]);

        // Получаем выбранные разрешения
        $selectedPermissions = array_filter($validated, function($value, $key) {
            return $key !== 'name' && $value == '1';
        }, ARRAY_FILTER_USE_BOTH);

        $permissionNames = array_keys($selectedPermissions);
        $permissions = Permission::whereIn('name', $permissionNames)->get();

        $role->permissions()->sync($permissions->pluck('id'));

        return redirect()->route('admin.roles')->with('success', 'Роль успешно создана');
    }

    public function edit(Role $role)
    {
        $role->load('permissions');
        
        $permissions = Permission::all();
        $groupedPermissions = $this->groupPermissionsByModule($permissions);
        
        // Проверяем, является ли роль Администратором (id = 1) или системной ролью "Пользователи" (id = 3)
        $isAdminRole = $role->id == 1;
        $isUsersRole = $role->id == 3;
        $isSystemRole = $role->is_system;
        
        return view('role::edit', compact('role', 'groupedPermissions', 'isAdminRole', 'isUsersRole', 'isSystemRole'));
    }

    public function update(Role $role, RoleEditRequest $request)
    {
        // Если роль Администратор (id = 1), запрещаем изменения
        if ($role->id == 1) {
            return redirect()->route('admin.roles')
                ->with('error', 'Роль "Администратор" защищена от изменений');
        }

        // Проверяем, является ли текущий пользователь системным для редактирования системных ролей
        if ($role->is_system && !auth()->user()->is_system) {
            return redirect()->route('admin.roles')
                ->with('error', 'Системные роли нельзя редактировать');
        }

        $validated = $request->validated();

        // Для системных ролей не меняем название
        if (!$role->is_system) {
            $role->update([
                'name' => $validated['name'],
            ]);
        }

        $selectedPermissions = array_filter($validated, function($value, $key) {
            return $key !== 'name' && $value == '1';
        }, ARRAY_FILTER_USE_BOTH);

        $permissionNames = array_keys($selectedPermissions);
        $permissions = Permission::whereIn('name', $permissionNames)->get();

        $role->permissions()->sync($permissions->pluck('id'));

        return redirect()->route('admin.roles')
            ->with('success', 'Роль успешно обновлена');
    }

    public function delete(Role $role)
    {
        // Если роль Администратор (id = 1), запрещаем удаление
        if ($role->id == 1) {
            return redirect()->route('admin.roles')
                ->with('error', 'Роль "Администратор" защищена от удаления');
        }

        // Если роль "Пользователи" (id = 3), запрещаем удаление
        if ($role->id == 3) {
            return redirect()->route('admin.roles')
                ->with('error', 'Роль "Пользователи" является системной и защищена от удаления');
        }

        if ($role->is_system) {
            return redirect()->route('admin.roles')
                ->with('error', 'Системные роли нельзя удалить');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles')
                ->with('error', 'Нельзя удалить роль, к которой привязаны пользователи');
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('admin.roles')
            ->with('success', 'Роль успешно удалена');
    }

    /**
     * Группировка разрешений по модулям
     */
    private function groupPermissionsByModule($permissions)
    {
        $grouped = [];
        
        foreach ($permissions as $permission) {
            $module = $this->extractModuleFromPermission($permission->name);
            
            if (!isset($grouped[$module])) {
                $grouped[$module] = [
                    'module' => $module,
                    'title' => $this->getModuleTitle($module),
                    'permissions' => []
                ];
            }
            
            $grouped[$module]['permissions'][] = $permission;
        }
        
        // Сортируем модули в логическом порядке
        $sortedGrouped = [];
        $priorityModules = ['show', 'users', 'roles'];
        
        foreach ($priorityModules as $module) {
            if (isset($grouped[$module])) {
                $sortedGrouped[$module] = $grouped[$module];
                unset($grouped[$module]);
            }
        }
        
        // Добавляем оставшиеся модули
        foreach ($grouped as $module => $data) {
            $sortedGrouped[$module] = $data;
        }
        
        return $sortedGrouped;
    }

    /**
     * Извлечение названия модуля из имени разрешения
     */
    private function extractModuleFromPermission($permissionName)
    {
        $parts = explode('_', $permissionName);
        
        // Первая часть - это модуль
        return $parts[0] ?? 'general';
    }

    /**
     * Получение заголовка модуля
     */
    private function getModuleTitle($module)
    {
        $titles = [
            'show' => 'Общие',
            'users' => 'Пользователи',
            'roles' => 'Роли',
            'general' => 'Общие'
        ];
        
        return $titles[$module] ?? ucfirst($module);
    }
}