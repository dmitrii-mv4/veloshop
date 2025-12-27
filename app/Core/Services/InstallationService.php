<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class InstallationService
{
    /**
     * Пути к миграциям системных модулей в правильном порядке
     */
    protected array $systemMigrations = [
        'database/migrations',
        'app/Admin/database/migrations',
        'app/Modules/Role/database/migrations',
        'app/Modules/User/database/migrations',
        'app/Modules/MediaLib/database/migrations',
        'app/Modules/ModuleGenerator/database/migrations',
        'app/Modules/Page/database/migrations',
        'app/Modules/Integrator/database/migrations',
        'app/Modules/IBlock/database/migrations',
    ];

    /**
     * Классы сидов системных модулей в правильном порядке
     */
    protected array $systemSeeders = [
        'App\Admin\Database\Seeders\SettingsSeeder',
        'App\Modules\Role\Database\Seeders\RoleSeeder',
        'App\Modules\Role\Database\Seeders\RolePermissionSeeder',
        'App\Modules\User\Database\Seeders\UserSeeder',
    ];

    /**
     * Проверка подключения к базе данных
     */
    public function checkDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            Log::error('Database connection failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Генерация ключа приложения
     */
    public function generateAppKey(): bool
    {
        try {
            if (empty(config('app.key'))) {
                Artisan::call('key:generate', ['--force' => true]);
                return true;
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to generate app key: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Получить отфильтрованные пути миграций
     */
    public function getValidMigrationPaths(): array
    {
        $validPaths = [];
        
        foreach ($this->systemMigrations as $path) {
            $fullPath = base_path($path);
            if (file_exists($fullPath) && is_dir($fullPath)) {
                $validPaths[] = $fullPath;
            }
        }
        
        return $this->sortMigrationsByPriority($validPaths);
    }

    /**
     * Сортировка миграций по приоритету
     */
    private function sortMigrationsByPriority(array $paths): array
    {
        usort($paths, function ($a, $b) {
            $priorityOrder = [
                'cache' => 1, // Таблица кэша должна быть первой
                'sessions' => 2,
                'jobs' => 3,
                'role' => 4,
                'permission' => 4,
                'user' => 5,
                'settings' => 6,
                'media' => 7,
                'page' => 8,
            ];
            
            $aPriority = 10;
            $bPriority = 10;
            
            foreach ($priorityOrder as $key => $priority) {
                if (strpos($a, $key) !== false) $aPriority = $priority;
                if (strpos($b, $key) !== false) $bPriority = $priority;
            }
            
            return $aPriority <=> $bPriority;
        });
        
        return $paths;
    }

    /**
     * Получить валидные классы сидов
     */
    public function getValidSeeders(): array
    {
        $validSeeders = [];
        
        foreach ($this->systemSeeders as $seeder) {
            if (class_exists($seeder)) {
                $validSeeders[] = $seeder;
            }
        }
        
        return $this->sortSeedersByPriority($validSeeders);
    }

    /**
     * Сортировка сидов по приоритету
     */
    private function sortSeedersByPriority(array $seeders): array
    {
        usort($seeders, function ($a, $b) {
            $priorityOrder = [
                'RoleSeeder' => 1,
                'Permission' => 2,
                'UserSeeder' => 3,
                'SettingsSeeder' => 4,
            ];
            
            $aPriority = 10;
            $bPriority = 10;
            
            foreach ($priorityOrder as $key => $priority) {
                if (strpos($a, $key) !== false) $aPriority = $priority;
                if (strpos($b, $key) !== false) $bPriority = $priority;
            }
            
            return $aPriority <=> $bPriority;
        });
        
        return $seeders;
    }

    /**
     * Запуск системных миграций
     */
    public function runSystemMigrations(bool $force = false): array
    {
        try {
            // Сначала проверим, существует ли таблица кэша
            if (!Schema::hasTable('cache')) {
                $this->info('Создаем таблицу кэша...');
            }
            
            $paths = $this->getValidMigrationPaths();
            
            foreach ($paths as $path) {
                Artisan::call('migrate', [
                    '--path' => str_replace(base_path() . '/', '', $path),
                    '--force' => $force,
                ]);
            }
            
            return ['status' => 'success', 'message' => 'Миграции выполнены'];
        } catch (\Exception $e) {
            Log::error('Migration failed: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Ошибка миграций: ' . $e->getMessage()];
        }
    }

    /**
     * Запуск системных сидов
     */
    public function runSystemSeeders(bool $force = false): array
    {
        $results = [];
        $seeders = $this->getValidSeeders();
        
        foreach ($seeders as $seeder) {
            try {
                Artisan::call('db:seed', [
                    '--class' => $seeder,
                    '--force' => $force,
                ]);
                $results[$seeder] = ['status' => 'success', 'message' => 'Seeder executed'];
            } catch (\Exception $e) {
                $results[$seeder] = [
                    'status' => 'error', 
                    'message' => $e->getMessage()
                ];
                Log::error("Seeder {$seeder} failed: " . $e->getMessage());
            }
        }
        
        return $results;
    }

    /**
     * Проверка и создание базовых ролей
     */
    public function checkAndCreateRoles(): array
    {
        $results = [];
        
        try {
            if (!Schema::hasTable('roles')) {
                return ['status' => 'warning', 'message' => 'Таблица ролей не существует'];
            }
            
            $roles = [
                ['id' => 1, 'name' => 'Администратор', 'is_system' => true],
                ['id' => 2, 'name' => 'Модератор', 'is_system' => false],
                ['id' => 3, 'name' => 'Пользователь', 'is_system' => true],
            ];
            
            foreach ($roles as $roleData) {
                $roleExists = DB::table('roles')->where('id', $roleData['id'])->exists();
                
                if (!$roleExists) {
                    DB::table('roles')->insert([
                        'id' => $roleData['id'],
                        'name' => $roleData['name'],
                        'is_system' => $roleData['is_system'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $results[$roleData['id']] = ['status' => 'created', 'name' => $roleData['name']];
                } else {
                    $results[$roleData['id']] = ['status' => 'exists', 'name' => $roleData['name']];
                }
            }
            
            return ['status' => 'success', 'results' => $results];
            
        } catch (\Exception $e) {
            Log::error('Role check failed: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Ошибка проверки ролей: ' . $e->getMessage()];
        }
    }

    /**
     * Создание администратора
     */
    public function createAdminUser(array $data = null): array
    {
        try {
            // Проверяем существование роли Администратора
            $roleExists = DB::table('roles')->where('id', 1)->exists();
            if (!$roleExists) {
                return ['status' => 'error', 'message' => 'Роль Администратора (id=1) не существует'];
            }
            
            // Проверяем существование пользователя
            if ($this->adminExists()) {
                return ['status' => 'exists', 'message' => 'Администратор уже существует'];
            }
            
            // Используем предоставленные данные или данные по умолчанию
            if (!$data) {
                $data = [
                    'name' => 'Администратор',
                    'email' => 'admin@kotiks.local',
                    'password' => 'kotiks2025',
                ];
            }
            
            // Используем полное имя класса модели User
            $userClass = '\\App\\Modules\\User\\Models\\User';
            
            if (!class_exists($userClass)) {
                return ['status' => 'error', 'message' => 'Класс User не найден'];
            }
            
            $user = $userClass::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'email_verified_at' => now(),
                'role_id' => 1,
                'password' => Hash::make($data['password']),
                'is_system' => true,
                'is_lang' => 'ru',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return [
                'status' => 'created',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $data['password'],
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Admin creation failed: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Ошибка создания администратора: ' . $e->getMessage()];
        }
    }

    /**
     * Проверяет существует ли администратор
     */
    private function adminExists(): bool
    {
        try {
            $userClass = '\\App\\Modules\\User\\Models\\User';
            return class_exists($userClass) && $userClass::where('email', 'admin@kotiks.local')->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Создание символьной ссылки storage
     */
    public function createStorageLink(): array
    {
        try {
            if (file_exists(public_path('storage'))) {
                return ['status' => 'exists', 'message' => 'Ссылка уже существует'];
            }
            
            Artisan::call('storage:link');
            return ['status' => 'success', 'message' => 'Ссылка создана'];
        } catch (\Exception $e) {
            Log::error('Storage link creation failed: ' . $e->getMessage());
            return ['status' => 'warning', 'message' => 'Ошибка создания ссылки: ' . $e->getMessage()];
        }
    }

    /**
     * Очистка кэша
     */
    public function clearCache(): array
    {
        try {
            Artisan::call('optimize:clear');
            return ['status' => 'success', 'message' => 'Кэш очищен'];
        } catch (\Exception $e) {
            Log::error('Cache clear failed: ' . $e->getMessage());
            return ['status' => 'warning', 'message' => 'Ошибка очистки кэша: ' . $e->getMessage()];
        }
    }

    /**
     * Полная установка CMS
     */
    public function install(array $options = []): array
    {
        $results = [];
        
        // 1. Проверка подключения к БД
        $results['database'] = $this->checkDatabaseConnection() 
            ? ['status' => 'success'] 
            : ['status' => 'error', 'message' => 'Ошибка подключения к БД'];
        
        // 2. Генерация ключа приложения
        $results['app_key'] = $this->generateAppKey()
            ? ['status' => 'success'] 
            : ['status' => 'error', 'message' => 'Ошибка генерации ключа'];
        
        // 3. Запуск миграций (включая таблицу кэша)
        $results['migrations'] = $this->runSystemMigrations($options['force'] ?? false);
        
        // 4. Проверка и создание ролей
        if (!($options['skip_role_check'] ?? false)) {
            $results['roles'] = $this->checkAndCreateRoles();
        }
        
        // 5. Запуск сидов
        if (!($options['no_seed'] ?? false)) {
            $results['seeders'] = $this->runSystemSeeders($options['force'] ?? false);
        }
        
        // 6. Создание storage link
        $results['storage_link'] = $this->createStorageLink();
        
        // 7. Создание администратора
        if (!($options['no_admin'] ?? false)) {
            $results['admin'] = $this->createAdminUser($options['admin_data'] ?? null);
        }
        
        // 8. Очистка кэша
        $results['cache'] = $this->clearCache();
        
        // 9. Дополнительные сиды
        if ($options['seed_all'] ?? false) {
            try {
                Artisan::call('db:seed', ['--force' => true]);
                $results['all_seeders'] = ['status' => 'success', 'message' => 'Все сиды выполнены'];
            } catch (\Exception $e) {
                $results['all_seeders'] = ['status' => 'error', 'message' => $e->getMessage()];
            }
        }
        
        return $results;
    }

    /**
     * Получить информацию об установке
     */
    public function getInstallationInfo(): array
    {
        return [
            'migrations' => [
                'registered' => count($this->systemMigrations),
                'valid' => count($this->getValidMigrationPaths()),
                'paths' => $this->getValidMigrationPaths(),
            ],
            'seeders' => [
                'registered' => count($this->systemSeeders),
                'valid' => count($this->getValidSeeders()),
                'classes' => $this->getValidSeeders(),
            ],
            'system_status' => [
                'database_connected' => $this->checkDatabaseConnection(),
                'app_key_generated' => !empty(config('app.key')),
                'storage_link_exists' => file_exists(public_path('storage')),
                'admin_exists' => $this->adminExists(),
                'roles_exist' => Schema::hasTable('roles') && DB::table('roles')->where('id', 1)->exists(),
                'cache_table_exists' => Schema::hasTable('cache'), // Проверка таблицы кэша
            ]
        ];
    }

    /**
     * Добавить путь миграции
     */
    public function addMigrationPath(string $relativePath): void
    {
        if (!in_array($relativePath, $this->systemMigrations)) {
            $this->systemMigrations[] = $relativePath;
        }
    }

    /**
     * Добавить класс сида
     */
    public function addSeederClass(string $seederClass): void
    {
        if (!in_array($seederClass, $this->systemSeeders)) {
            $this->systemSeeders[] = $seederClass;
        }
    }

    /**
     * Вывод информационного сообщения (для использования в командах)
     */
    private function info(string $message): void
    {
        if (function_exists('info')) {
            info($message);
        } else {
            echo $message . PHP_EOL;
        }
    }
}