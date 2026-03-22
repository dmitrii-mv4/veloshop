<?php

namespace App\Modules\Role\Controllers\Api;

use App\Core\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Modules\Role\Models\Role;
use App\Modules\Role\Models\Permission;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    /**
     * Получение раздельных данных ролей и разрешений
     * Возвращает роли, разрешения и связи между ними в структурированном формате
     *
     * @return JsonResponse
     */
    public function getSeparate(): JsonResponse
    {
        try {
            Log::info('API Role: получение раздельных данных ролей и разрешений');
            
            // Получаем все роли с их разрешениями
            $roles = Role::with('permissions')->get();
            
            // Получаем все разрешения
            $permissions = Permission::all();
            
            // Получаем связи между ролями и разрешениями
            $rolePermissions = DB::table('role_has_permissions')->get();
            
            // Получаем статистику по пользователям для каждой роли
            $usersCountByRole = User::select('role_id', DB::raw('COUNT(*) as user_count'))
                ->where('is_active', true)
                ->groupBy('role_id')
                ->pluck('user_count', 'role_id');
            
            // Если нет ролей
            if ($roles->isEmpty()) {
                Log::info('API Role: роли не найдены');
                return response()->json([
                    'success' => true,
                    'data' => [
                        'roles' => [],
                        'permissions' => [],
                        'role_has_permissions' => [],
                        'users_by_role' => []
                    ]
                ]);
            }
            
            // Структурируем данные
            $structuredData = $this->structureRolesData($roles, $permissions, $rolePermissions, $usersCountByRole);
            
            Log::info('API Role: данные успешно получены', [
                'roles_count' => $roles->count(),
                'permissions_count' => $permissions->count(),
                'role_permissions_count' => $rolePermissions->count(),
                'system_roles_count' => $roles->where('is_system', true)->count()
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $structuredData
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Role: ошибка получения раздельных данных', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении данных ролей и разрешений',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Структурирование данных ролей и разрешений
     *
     * @param \Illuminate\Database\Eloquent\Collection $roles
     * @param \Illuminate\Database\Eloquent\Collection $permissions
     * @param \Illuminate\Support\Collection $rolePermissions
     * @param \Illuminate\Support\Collection $usersCountByRole
     * @return array
     */
    private function structureRolesData($roles, $permissions, $rolePermissions, $usersCountByRole): array
    {
        // Преобразуем роли
        $structuredRoles = $roles->map(function($role) use ($usersCountByRole) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $this->generateSlug($role->name),
                'description' => $role->description ?? null,
                'is_system' => (bool)$role->is_system,
                'permissions' => $role->permissions->map(function($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'title' => $permission->title
                    ];
                })->toArray(),
                'permission_ids' => $role->permissions->pluck('id')->toArray(),
                'users_count' => $usersCountByRole[$role->id] ?? 0,
                'is_protected' => $role->is_system || ($usersCountByRole[$role->id] ?? 0) > 0,
                'created_at' => $role->created_at ? $role->created_at->toISOString() : null,
                'updated_at' => $role->updated_at ? $role->updated_at->toISOString() : null
            ];
        })->toArray();
        
        // Преобразуем разрешения
        $structuredPermissions = $permissions->map(function($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'title' => $permission->title,
                'module' => $this->extractPermissionModule($permission->name),
                'action' => $this->extractPermissionAction($permission->name),
                'created_at' => $permission->created_at ? $permission->created_at->toISOString() : null,
                'updated_at' => $permission->updated_at ? $permission->updated_at->toISOString() : null
            ];
        })->toArray();
        
        // Преобразуем связи ролей и разрешений
        $structuredRolePermissions = $rolePermissions->map(function($rolePermission) {
            return [
                'id' => $rolePermission->id,
                'role_id' => $rolePermission->role_id,
                'permission_id' => $rolePermission->permission_id
                // В таблице role_has_permissions нет полей created_at и updated_at
            ];
        })->toArray();
        
        // Разделяем роли на системные и пользовательские
        $systemRoles = array_filter($structuredRoles, function($role) {
            return $role['is_system'];
        });
        
        $userRoles = array_filter($structuredRoles, function($role) {
            return !$role['is_system'];
        });
        
        // Разделяем разрешения по модулям
        $permissionsByModule = [];
        foreach ($structuredPermissions as $permission) {
            $module = $permission['module'] ?? 'other';
            if (!isset($permissionsByModule[$module])) {
                $permissionsByModule[$module] = [
                    'module' => $module,
                    'permissions' => [],
                    'count' => 0
                ];
            }
            $permissionsByModule[$module]['permissions'][] = $permission;
            $permissionsByModule[$module]['count']++;
        }
        $permissionsByModuleFormatted = array_values($permissionsByModule);
        
        // Статистика
        $statistics = [
            'total_roles' => count($structuredRoles),
            'system_roles' => count($systemRoles),
            'user_roles' => count($userRoles),
            'total_permissions' => count($structuredPermissions),
            'total_role_permissions' => count($structuredRolePermissions),
            'protected_roles' => count(array_filter($structuredRoles, function($role) {
                return $role['is_protected'];
            })),
            'average_permissions_per_role' => count($structuredRoles) > 0 
                ? round(count($structuredRolePermissions) / count($structuredRoles), 2) 
                : 0,
            'modules_with_permissions' => count($permissionsByModuleFormatted)
        ];
        
        // Группировка разрешений по ролям для удобства
        $permissionsByRole = [];
        foreach ($structuredRoles as $role) {
            $permissionsByRole[$role['id']] = [
                'role_id' => $role['id'],
                'role_name' => $role['name'],
                'permissions' => $role['permissions'],
                'permission_ids' => $role['permission_ids'],
                'permissions_count' => count($role['permissions'])
            ];
        }
        
        return [
            'roles' => $structuredRoles,
            // 'permissions' => $structuredPermissions,
            // 'role_has_permissions' => $structuredRolePermissions,
            // 'system_roles' => array_values($systemRoles),
            // 'user_roles' => array_values($userRoles),
            // 'permissions_by_module' => $permissionsByModuleFormatted,
            // 'permissions_by_role' => array_values($permissionsByRole),
            'statistics' => $statistics,
            'meta' => [
                'timestamp' => now()->toISOString()
            ]
        ];
    }
    
    /**
     * Генерация slug для роли
     *
     * @param string $name
     * @return string
     */
    private function generateSlug(string $name): string
    {
        // Транслитерация кириллицы
        $translit = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
        ];
        
        $name = mb_strtolower($name, 'UTF-8');
        $name = strtr($name, $translit);
        $name = preg_replace('/[^a-z0-9]+/', '_', $name);
        $name = trim($name, '_');
        
        return $name;
    }
    
    /**
     * Извлечение модуля из имени разрешения
     *
     * @param string $permissionName
     * @return string
     */
    private function extractPermissionModule(string $permissionName): string
    {
        // Пример: users_viewAny -> users
        if (strpos($permissionName, '_') !== false) {
            $parts = explode('_', $permissionName);
            return $parts[0];
        }
        
        return 'general';
    }
    
    /**
     * Извлечение действия из имени разрешения
     *
     * @param string $permissionName
     * @return string
     */
    private function extractPermissionAction(string $permissionName): string
    {
        // Пример: users_viewAny -> viewAny
        if (strpos($permissionName, '_') !== false) {
            $parts = explode('_', $permissionName);
            array_shift($parts);
            return implode('_', $parts);
        }
        
        return $permissionName;
    }
    
    /**
     * Получение роли по ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getById(int $id): JsonResponse
    {
        try {
            Log::info('API Role: получение роли по ID', ['id' => $id]);
            
            $role = Role::with('permissions')->find($id);
            
            if (!$role) {
                Log::warning('API Role: роль не найдена', ['id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Роль не найдена'
                ], 404);
            }
            
            // Количество пользователей с этой ролью
            $usersCount = User::where('role_id', $id)
                ->where('is_active', true)
                ->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'role' => [
                        'id' => $role->id,
                        'name' => $role->name,
                        'slug' => $this->generateSlug($role->name),
                        'description' => $role->description ?? null,
                        'is_system' => (bool)$role->is_system,
                        'permissions' => $role->permissions->map(function($permission) {
                            return [
                                'id' => $permission->id,
                                'name' => $permission->name,
                                'title' => $permission->title,
                                'module' => $this->extractPermissionModule($permission->name),
                                'action' => $this->extractPermissionAction($permission->name)
                            ];
                        })->toArray(),
                        'permission_ids' => $role->permissions->pluck('id')->toArray(),
                        'users_count' => $usersCount,
                        'is_protected' => $role->is_system || $usersCount > 0,
                        'created_at' => $role->created_at ? $role->created_at->toISOString() : null,
                        'updated_at' => $role->updated_at ? $role->updated_at->toISOString() : null
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Role: ошибка получения роли по ID', [
                'id' => $id,
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении роли'
            ], 500);
        }
    }
    
    /**
     * Получение системных ролей
     *
     * @return JsonResponse
     */
    public function getSystemRoles(): JsonResponse
    {
        try {
            Log::info('API Role: получение системных ролей');
            
            $roles = Role::with('permissions')
                ->where('is_system', true)
                ->get();
            
            $structuredRoles = $roles->map(function($role) {
                // Количество пользователей с этой ролью
                $usersCount = User::where('role_id', $role->id)
                    ->where('is_active', true)
                    ->count();
                
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'slug' => $this->generateSlug($role->name),
                    'description' => $role->description ?? null,
                    'permissions_count' => $role->permissions->count(),
                    'users_count' => $usersCount,
                    'created_at' => $role->created_at ? $role->created_at->toISOString() : null,
                    'updated_at' => $role->updated_at ? $role->updated_at->toISOString() : null
                ];
            })->toArray();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'roles' => $structuredRoles,
                    'count' => count($structuredRoles)
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Role: ошибка получения системных ролей', [
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении системных ролей'
            ], 500);
        }
    }
    
    /**
     * Получение пользовательских ролей
     *
     * @return JsonResponse
     */
    public function getUserRoles(): JsonResponse
    {
        try {
            Log::info('API Role: получение пользовательских ролей');
            
            $roles = Role::with('permissions')
                ->where('is_system', false)
                ->get();
            
            $structuredRoles = $roles->map(function($role) {
                // Количество пользователей с этой ролью
                $usersCount = User::where('role_id', $role->id)
                    ->where('is_active', true)
                    ->count();
                
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'slug' => $this->generateSlug($role->name),
                    'description' => $role->description ?? null,
                    'permissions' => $role->permissions->map(function($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                            'title' => $permission->title
                        ];
                    })->toArray(),
                    'permissions_count' => $role->permissions->count(),
                    'users_count' => $usersCount,
                    'created_at' => $role->created_at ? $role->created_at->toISOString() : null,
                    'updated_at' => $role->updated_at ? $role->updated_at->toISOString() : null
                ];
            })->toArray();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'roles' => $structuredRoles,
                    'count' => count($structuredRoles)
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Role: ошибка получения пользовательских ролей', [
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении пользовательских ролей'
            ], 500);
        }
    }
    
    /**
     * Получение разрешений по модулю
     *
     * @param string $module
     * @return JsonResponse
     */
    public function getPermissionsByModule(string $module): JsonResponse
    {
        try {
            Log::info('API Role: получение разрешений по модулю', ['module' => $module]);
            
            $permissions = Permission::all();
            
            $filteredPermissions = $permissions->filter(function($permission) use ($module) {
                return $this->extractPermissionModule($permission->name) === $module;
            });
            
            $structuredPermissions = $filteredPermissions->map(function($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'title' => $permission->title,
                    'module' => $this->extractPermissionModule($permission->name),
                    'action' => $this->extractPermissionAction($permission->name)
                ];
            })->toArray();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'module' => $module,
                    'permissions' => $structuredPermissions,
                    'count' => count($structuredPermissions)
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Role: ошибка получения разрешений по модулю', [
                'module' => $module,
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении разрешений'
            ], 500);
        }
    }
    
    /**
     * Получение статистики по ролям и разрешениям
     *
     * @return JsonResponse
     */
    public function getStats(): JsonResponse
    {
        try {
            Log::info('API Role: получение статистики по ролям и разрешениям');
            
            $totalRoles = Role::count();
            $systemRoles = Role::where('is_system', true)->count();
            $userRoles = Role::where('is_system', false)->count();
            
            $totalPermissions = Permission::count();
            $totalRolePermissions = DB::table('role_has_permissions')->count();
            
            // Распределение разрешений по модулям
            $permissions = Permission::all();
            $moduleStats = [];
            foreach ($permissions as $permission) {
                $module = $this->extractPermissionModule($permission->name);
                if (!isset($moduleStats[$module])) {
                    $moduleStats[$module] = 0;
                }
                $moduleStats[$module]++;
            }
            
            $modulesFormatted = [];
            foreach ($moduleStats as $module => $count) {
                $modulesFormatted[] = [
                    'module' => $module,
                    'permissions_count' => $count
                ];
            }
            
            // Статистика по пользователям для ролей
            $roleUsersStats = User::select('role_id', DB::raw('COUNT(*) as user_count'))
                ->where('is_active', true)
                ->groupBy('role_id')
                ->get()
                ->map(function($item) {
                    return [
                        'role_id' => $item->role_id,
                        'user_count' => $item->user_count
                    ];
                });
            
            // Роли с наибольшим количеством пользователей
            $popularRoles = $roleUsersStats->sortByDesc('user_count')->take(5)->values();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'roles' => [
                        'total' => $totalRoles,
                        'system' => $systemRoles,
                        'user_created' => $userRoles
                    ],
                    'permissions' => [
                        'total' => $totalPermissions,
                        'assigned' => $totalRolePermissions,
                        'average_per_role' => $totalRoles > 0 ? round($totalRolePermissions / $totalRoles, 2) : 0
                    ],
                    'modules' => $modulesFormatted,
                    'popular_roles' => $popularRoles,
                    'meta' => [
                        'timestamp' => now()->toISOString()
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Role: ошибка получения статистики', [
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении статистики'
            ], 500);
        }
    }
    
    /**
     * Поиск ролей по названию
     *
     * @param string $query
     * @return JsonResponse
     */
    public function search(string $query): JsonResponse
    {
        try {
            Log::info('API Role: поиск ролей', ['query' => $query]);
            
            $roles = Role::with('permissions')
                ->where('name', 'ILIKE', "%{$query}%")
                ->get();
            
            $structuredRoles = $roles->map(function($role) {
                // Количество пользователей с этой ролью
                $usersCount = User::where('role_id', $role->id)
                    ->where('is_active', true)
                    ->count();
                
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'description' => $role->description ?? null,
                    'is_system' => (bool)$role->is_system,
                    'permissions_count' => $role->permissions->count(),
                    'users_count' => $usersCount,
                    'created_at' => $role->created_at ? $role->created_at->toISOString() : null
                ];
            })->toArray();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'query' => $query,
                    'roles' => $structuredRoles,
                    'count' => $roles->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Role: ошибка поиска ролей', [
                'query' => $query,
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при поиске ролей'
            ], 500);
        }
    }
}