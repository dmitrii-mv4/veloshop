<?php

namespace App\Modules\User\Controllers\Api;

use App\Core\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Modules\User\Models\User;
use App\Modules\Role\Models\Role;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    /**
     * Получение раздельных данных пользователей
     * Возвращает пользователей с их ролями в структурированном формате
     *
     * @return JsonResponse
     */
    public function getSeparate(): JsonResponse
    {
        try {
            Log::info('API User: получение раздельных данных пользователей');
            
            // Получаем всех пользователей с их ролями
            $users = User::with('role')->get();
            
            // Получаем все роли отдельно
            $roles = Role::all();
            
            // Если нет пользователей
            if ($users->isEmpty()) {
                Log::info('API User: пользователи не найдены');
                return response()->json([
                    'success' => true,
                    'data' => [
                        'users' => [],
                        'active_users' => [],
                        'inactive_users' => [],
                        'system_users' => [],
                        'non_system_users' => []
                    ]
                ]);
            }
            
            // Структурируем данные
            $structuredData = $this->structureUsersData($users, $roles);
            
            Log::info('API User: данные успешно получены', [
                'users_count' => $users->count(),
                'active_count' => $users->where('is_active', true)->count(),
                'inactive_count' => $users->where('is_active', false)->count(),
                'system_count' => $users->where('is_system', true)->count()
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $structuredData
            ]);
            
        } catch (\Exception $e) {
            Log::error('API User: ошибка получения раздельных данных', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении данных пользователей',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Структурирование данных пользователей
     *
     * @param \Illuminate\Database\Eloquent\Collection $users
     * @param \Illuminate\Database\Eloquent\Collection $roles
     * @return array
     */
    private function structureUsersData($users, $roles): array
    {
        // Преобразуем роли
        $structuredRoles = $roles->map(function($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'description' => $role->description,
                'permissions' => $role->permissions ?? [],
                'created_at' => $role->created_at->toISOString(),
                'updated_at' => $role->updated_at->toISOString()
            ];
        })->toArray();
        
        // Преобразуем пользователей
        $structuredUsers = $users->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at ? $user->email_verified_at->toISOString() : null,
                'role' => $user->role ? [
                    'id' => $user->role->id,
                    'name' => $user->role->name,
                    'slug' => $user->role->slug,
                    'description' => $user->role->description
                ] : null,
                'role_id' => $user->role_id,
                'avatar' => $this->getAvatarUrl($user->avatar),
                'avatar_raw' => $user->avatar,
                'phone' => $user->phone,
                'position' => $user->position,
                'bio' => $user->bio,
                'is_active' => (bool)$user->is_active,
                'is_system' => (bool)$user->is_system,
                'language' => $user->is_lang ?? 'ru',
                'last_login_at' => $user->last_login_at ? $user->last_login_at->toISOString() : null,
                'activity_status' => $this->getActivityStatus($user),
                'permissions' => $this->getUserPermissions($user),
                'created_at' => $user->created_at->toISOString(),
                'updated_at' => $user->updated_at->toISOString()
            ];
        })->toArray();
        
        // Разделяем пользователей по различным критериям
        $activeUsers = array_filter($structuredUsers, function($user) {
            return $user['is_active'];
        });
        
        $inactiveUsers = array_filter($structuredUsers, function($user) {
            return !$user['is_active'];
        });
        
        $systemUsers = array_filter($structuredUsers, function($user) {
            return $user['is_system'];
        });
        
        $nonSystemUsers = array_filter($structuredUsers, function($user) {
            return !$user['is_system'];
        });
        
        // Группируем пользователей по ролям
        $usersByRole = [];
        foreach ($structuredUsers as $user) {
            $roleName = $user['role']['name'] ?? 'Без роли';
            if (!isset($usersByRole[$roleName])) {
                $usersByRole[$roleName] = [
                    'role' => $user['role'] ?? null,
                    'users' => [],
                    'count' => 0
                ];
            }
            $usersByRole[$roleName]['users'][] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'is_active' => $user['is_active']
            ];
            $usersByRole[$roleName]['count']++;
        }
        
        // Преобразуем в индексный массив
        $usersByRoleFormatted = array_values($usersByRole);
        
        // Статистика
        $statistics = [
            'total_users' => count($structuredUsers),
            'active_count' => count($activeUsers),
            'inactive_count' => count($inactiveUsers),
            'system_count' => count($systemUsers),
            'non_system_count' => count($nonSystemUsers),
            'email_verified_count' => count(array_filter($structuredUsers, function($user) {
                return !is_null($user['email_verified_at']);
            })),
            'roles_distribution' => $usersByRoleFormatted
        ];
        
        return [
            'users' => $structuredUsers,
            'statistics' => $statistics,
            'meta' => [
                'timestamp' => now()->toISOString()
            ]
        ];
    }
    
    /**
     * Генерация URL для аватара
     *
     * @param string|null $avatar
     * @return string|null
     */
    private function getAvatarUrl(?string $avatar): ?string
    {
        if (empty($avatar)) {
            // Генерируем аватар по умолчанию с инициалами
            // Можно использовать Gravatar или стандартное изображение
            return null; // Или URL стандартного аватара
        }
        
        // Если аватар - полный URL
        if (filter_var($avatar, FILTER_VALIDATE_URL)) {
            return $avatar;
        }
        
        // Если аватар - путь относительно storage
        if (strpos($avatar, 'storage/') === 0) {
            return asset($avatar);
        }
        
        // Если аватар - путь относительно public
        if (strpos($avatar, '/') === 0) {
            return asset(ltrim($avatar, '/'));
        }
        
        // По умолчанию возвращаем как есть
        return $avatar;
    }
    
    /**
     * Определение статуса активности пользователя
     *
     * @param User $user
     * @return array
     */
    private function getActivityStatus(User $user): array
    {
        if (!$user->is_active) {
            return [
                'status' => 'inactive',
                'label' => 'Неактивен',
                'color' => 'danger'
            ];
        }
        
        if ($user->last_login_at) {
            $lastLogin = $user->last_login_at;
            $now = now();
            $diffInHours = $lastLogin->diffInHours($now);
            
            if ($diffInHours < 1) {
                return [
                    'status' => 'online',
                    'label' => 'В сети',
                    'color' => 'success',
                    'last_seen' => 'только что'
                ];
            } elseif ($diffInHours < 24) {
                return [
                    'status' => 'recent',
                    'label' => 'Недавно был в сети',
                    'color' => 'info',
                    'last_seen' => $diffInHours . ' ч. назад'
                ];
            } else {
                return [
                    'status' => 'offline',
                    'label' => 'Давно не в сети',
                    'color' => 'warning',
                    'last_seen' => $lastLogin->format('d.m.Y H:i')
                ];
            }
        }
        
        return [
            'status' => 'never',
            'label' => 'Никогда не входил',
            'color' => 'secondary'
        ];
    }
    
    /**
     * Получение прав пользователя
     *
     * @param User $user
     * @return array
     */
    private function getUserPermissions(User $user): array
    {
        $permissions = [];
        
        // Базовые права
        $basePermissions = [
            'view_dashboard' => true,
            'edit_profile' => true,
            'change_password' => true
        ];
        
        // Права из роли
        if ($user->role && isset($user->role->permissions)) {
            $rolePermissions = is_array($user->role->permissions) 
                ? $user->role->permissions 
                : json_decode($user->role->permissions, true);
            
            if (is_array($rolePermissions)) {
                $permissions = array_merge($basePermissions, $rolePermissions);
            }
        }
        
        // Дополнительные права для системных пользователей
        if ($user->is_system) {
            $permissions['access_admin'] = true;
            $permissions['manage_users'] = true;
            $permissions['manage_roles'] = true;
            $permissions['system_settings'] = true;
        }
        
        // Активность влияет на некоторые права
        if (!$user->is_active) {
            $permissions['access_admin'] = false;
            $permissions['login'] = false;
        }
        
        return $permissions;
    }
    
    /**
     * Получение пользователя по ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getById(int $id): JsonResponse
    {
        try {
            Log::info('API User: получение пользователя по ID', ['id' => $id]);
            
            $user = User::with('role')->find($id);
            
            if (!$user) {
                Log::warning('API User: пользователь не найден', ['id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не найден'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'email_verified_at' => $user->email_verified_at ? $user->email_verified_at->toISOString() : null,
                        'role' => $user->role ? [
                            'id' => $user->role->id,
                            'name' => $user->role->name,
                            'slug' => $user->role->slug
                        ] : null,
                        'role_id' => $user->role_id,
                        'avatar' => $this->getAvatarUrl($user->avatar),
                        'phone' => $user->phone,
                        'position' => $user->position,
                        'bio' => $user->bio,
                        'is_active' => (bool)$user->is_active,
                        'is_system' => (bool)$user->is_system,
                        'language' => $user->is_lang,
                        'last_login_at' => $user->last_login_at ? $user->last_login_at->toISOString() : null,
                        'activity_status' => $this->getActivityStatus($user),
                        'permissions' => $this->getUserPermissions($user),
                        'created_at' => $user->created_at->toISOString(),
                        'updated_at' => $user->updated_at->toISOString()
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API User: ошибка получения пользователя по ID', [
                'id' => $id,
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении пользователя'
            ], 500);
        }
    }
    
    /**
     * Получение активных пользователей
     *
     * @return JsonResponse
     */
    public function getActive(): JsonResponse
    {
        try {
            Log::info('API User: получение активных пользователей');
            
            $users = User::with('role')
                ->where('is_active', true)
                ->orderBy('name', 'asc')
                ->get();
            
            $structuredUsers = $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ? $user->role->name : null,
                    'avatar' => $this->getAvatarUrl($user->avatar),
                    'position' => $user->position,
                    'last_login_at' => $user->last_login_at ? $user->last_login_at->toISOString() : null
                ];
            })->toArray();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $structuredUsers,
                    'count' => count($structuredUsers)
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API User: ошибка получения активных пользователей', [
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении активных пользователей'
            ], 500);
        }
    }
    
    /**
     * Получение пользователей по роли
     *
     * @param int $roleId
     * @return JsonResponse
     */
    public function getByRole(int $roleId): JsonResponse
    {
        try {
            Log::info('API User: получение пользователей по роли', ['role_id' => $roleId]);
            
            $role = Role::find($roleId);
            
            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Роль не найдена'
                ], 404);
            }
            
            $users = User::with('role')
                ->where('role_id', $roleId)
                ->where('is_active', true)
                ->orderBy('name', 'asc')
                ->get();
            
            $structuredUsers = $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $this->getAvatarUrl($user->avatar),
                    'position' => $user->position,
                    'last_login_at' => $user->last_login_at ? $user->last_login_at->toISOString() : null
                ];
            })->toArray();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'role' => [
                        'id' => $role->id,
                        'name' => $role->name,
                        'slug' => $role->slug
                    ],
                    'users' => $structuredUsers,
                    'count' => $users->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API User: ошибка получения пользователей по роли', [
                'role_id' => $roleId,
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении пользователей по роли'
            ], 500);
        }
    }
    
    /**
     * Поиск пользователей по имени или email
     *
     * @param string $query
     * @return JsonResponse
     */
    public function search(string $query): JsonResponse
    {
        try {
            Log::info('API User: поиск пользователей', ['query' => $query]);
            
            $users = User::with('role')
                ->where(function($q) use ($query) {
                    $q->where('name', 'ILIKE', "%{$query}%")
                      ->orWhere('email', 'ILIKE', "%{$query}%")
                      ->orWhere('phone', 'ILIKE', "%{$query}%")
                      ->orWhere('position', 'ILIKE', "%{$query}%");
                })
                ->where('is_active', true)
                ->orderBy('name', 'asc')
                ->limit(50)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'query' => $query,
                    'users' => $users->map(function($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'role' => $user->role ? $user->role->name : null,
                            'avatar' => $this->getAvatarUrl($user->avatar),
                            'position' => $user->position,
                            'phone' => $user->phone
                        ];
                    }),
                    'count' => $users->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API User: ошибка поиска пользователей', [
                'query' => $query,
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при поиске пользователей'
            ], 500);
        }
    }
    
    /**
     * Получение статистики по пользователям
     *
     * @return JsonResponse
     */
    public function getStats(): JsonResponse
    {
        try {
            Log::info('API User: получение статистики по пользователям');
            
            $totalUsers = User::count();
            $activeUsers = User::where('is_active', true)->count();
            $inactiveUsers = User::where('is_active', false)->count();
            $systemUsers = User::where('is_system', true)->count();
            $emailVerified = User::whereNotNull('email_verified_at')->count();
            
            // Статистика по ролям
            $rolesStats = User::select('role_id')
                ->selectRaw('COUNT(*) as user_count')
                ->where('is_active', true)
                ->groupBy('role_id')
                ->with('role')
                ->get()
                ->map(function($item) {
                    return [
                        'role_id' => $item->role_id,
                        'role_name' => $item->role ? $item->role->name : 'Без роли',
                        'user_count' => $item->user_count
                    ];
                })
                ->sortByDesc('user_count')
                ->values()
                ->toArray();
            
            // Активность за последние 30 дней
            $recentActivity = User::where('last_login_at', '>=', now()->subDays(30))
                ->count();
            
            // Новые пользователи за последние 30 дней
            $newUsers = User::where('created_at', '>=', now()->subDays(30))
                ->count();
            
            // Пользователи с аватарками
            $withAvatar = User::whereNotNull('avatar')
                ->where('avatar', '!=', '')
                ->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_users' => $totalUsers,
                    'email_verified_users' => $emailVerified,
                    'users_with_avatar' => $withAvatar,
                    'recent_activity' => $recentActivity,
                    'new_users_last_30_days' => $newUsers,
                    'roles_distribution' => $rolesStats,
                    'meta' => [
                        'timestamp' => now()->toISOString()
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API User: ошибка получения статистики', [
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении статистики'
            ], 500);
        }
    }
}