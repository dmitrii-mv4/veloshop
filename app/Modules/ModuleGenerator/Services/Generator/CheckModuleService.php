<?php

namespace App\Modules\ModuleGenerator\Services\Generator;

use App\Modules\ModuleGenerator\Models\Module;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

/**
 * Сервис для проверки существования модулей
 */
class CheckModuleService
{
    /**
     * Список зарезервированных имен таблиц и системных модулей
     * которые нельзя использовать для пользовательских модулей
     */
    private array $reservedTables = [
        // Laravel системные таблицы
        'cache',
        'cache_locks', 
        'failed_jobs',
        'job_batches',
        'jobs',
        'migrations',
        'password_reset_tokens',
        'sessions',
        
        // Системные таблицы Kotiks
        'media',
        'media_folders',
        'modules',
        'pages',
        'permissions',
        'role_has_permissions',
        'roles',
        'settings',
        'users',
        
        // Дополнительные таблицы для будущего расширения
        'admin',
        'core',
        'system',
        'config',
        'logs',
        'notifications',
        'translations',
        'locales',
        'countries',
        'cities',
        'regions',
        
        // Запрещенные префиксы (начинаются с)
        '_temp_', // Временные таблицы
        'temp_',
        'test_',
        'backup_',
        'old_',
        'new_',
        'tmp_',
    ];
    
    /**
     * Основной метод проверки модуля
     * 
     * @param string $moduleCode Код модуля (например, "news")
     * @param string $slug Slug модуля (например, "novosti")
     * @return array Результат проверки
     */
    public function main(string $moduleCode, string $slug): array
    {
        Log::info('CheckModuleService: проверка модуля', [
            'module_code' => $moduleCode,
            'slug' => $slug
        ]);

        // 0. Проверка на зарезервированные имена (самая важная, делаем первой)
        $reservedCheck = $this->checkReservedNames($moduleCode, $slug);
        if (!$reservedCheck['success']) {
            // Добавляем флаг, что это ошибка зарезервированного имени
            $reservedCheck['is_reserved_error'] = true;
            return $reservedCheck;
        }

        // 1. Проверка по коду модуля в таблице modules
        $codeExists = $this->checkModuleCode($moduleCode);
        
        // 2. Проверка по slug в таблице modules
        $slugExists = $this->checkModuleSlug($slug);
        
        // 3. Проверка существования таблицы в БД
        $tableExists = $this->checkModuleTable($moduleCode);
        
        // 4. Проверка существования директории модуля
        $directoryExists = $this->checkModuleDirectory($moduleCode);
        
        // 5. Проверка существования миграций
        $migrationsExist = $this->checkModuleMigrations($moduleCode);
        
        // 6. Проверка существования прав доступа
        $permissionsExist = $this->checkModulePermissions($moduleCode);
        
        // Собираем все проверки
        $checks = [
            'reserved_names' => false, // Эта проверка уже прошла успешно
            'code_in_modules_table' => $codeExists,
            'slug_in_modules_table' => $slugExists,
            'table_in_database' => $tableExists,
            'directory_in_filesystem' => $directoryExists,
            'migrations_in_database' => $migrationsExist,
            'permissions_in_database' => $permissionsExist,
        ];
        
        // Проверяем, есть ли хотя бы одна положительная проверка
        $moduleExists = in_array(true, $checks, true);
        
        Log::info('CheckModuleService: результаты проверки', [
            'module_code' => $moduleCode,
            'module_exists' => $moduleExists,
            'checks' => $checks
        ]);
        
        if ($moduleExists) {
            return $this->generateErrorResponse($moduleCode, $slug, $checks);
        }
        
        return [
            'success' => true,
            'message' => 'Модуль может быть создан',
            'checks' => $checks
        ];
    }
    
    /**
     * Проверка на зарезервированные имена таблиц и модулей
     * 
     * @param string $moduleCode Код модуля
     * @param string $slug Slug модуля
     * @return array Результат проверки
     */
    private function checkReservedNames(string $moduleCode, string $slug): array
    {
        $moduleCodeLower = strtolower($moduleCode);
        $slugLower = strtolower($slug);
        
        $conflicts = [];
        
        // Проверяем код модуля
        foreach ($this->reservedTables as $reserved) {
            if (str_starts_with($reserved, '_')) {
                // Проверяем префиксы
                if (str_starts_with($moduleCodeLower, substr($reserved, 1))) {
                    $conflicts[] = "Код модуля '{$moduleCode}' начинается с зарезервированного префикса '{$reserved}'";
                }
            } else {
                // Полное совпадение
                if ($moduleCodeLower === $reserved) {
                    $conflicts[] = "Код модуля '{$moduleCode}' совпадает с зарезервированным именем '{$reserved}'";
                }
            }
        }
        
        // Проверяем slug (но менее строго, так как slug используется в URL)
        $slugReserved = ['admin', 'api', 'auth', 'login', 'logout', 'register', 'password', 'email', 'verify'];
        foreach ($slugReserved as $reserved) {
            if ($slugLower === $reserved) {
                $conflicts[] = "Slug '{$slug}' совпадает с зарезервированным маршрутом '{$reserved}'";
            }
        }
        
        // Дополнительные проверки для slug
        if (in_array($slugLower, ['create', 'edit', 'delete', 'update', 'store', 'destroy'])) {
            $conflicts[] = "Slug '{$slug}' совпадает с зарезервированным методом контроллера";
        }
        
        // Проверяем на SQL ключевые слова (дополнительная защита)
        $sqlKeywords = ['select', 'insert', 'update', 'delete', 'drop', 'create', 'alter', 'table', 'database'];
        if (in_array($moduleCodeLower, $sqlKeywords)) {
            $conflicts[] = "Код модуля '{$moduleCode}' является SQL ключевым словом";
        }
        
        if (count($conflicts) > 0) {
            Log::warning('CheckModuleService: обнаружены конфликты с зарезервированными именами', [
                'module_code' => $moduleCode,
                'slug' => $slug,
                'conflicts' => $conflicts
            ]);
            
            return [
                'success' => false,
                'message' => 'Невозможно создать модуль: ' . implode('; ', $conflicts),
                'module_code' => $moduleCode,
                'slug' => $slug,
                'conflicts' => $conflicts,
                'reserved_tables' => $this->reservedTables
            ];
        }
        
        return ['success' => true];
    }
    
    /**
     * Добавление новых зарезервированных имен в список
     * 
     * @param array $tables Массив новых зарезервированных имен
     * @return void
     */
    public function addReservedTables(array $tables): void
    {
        $this->reservedTables = array_unique(array_merge($this->reservedTables, $tables));
    }
    
    /**
     * Получение списка зарезервированных таблиц (для отладки и UI)
     * 
     * @return array
     */
    public function getReservedTables(): array
    {
        return $this->reservedTables;
    }
    
    /**
     * Проверка существования модуля по коду в таблице modules
     */
    private function checkModuleCode(string $moduleCode): bool
    {
        try {
            return Module::codeExists($moduleCode);
        } catch (\Exception $e) {
            Log::error('Ошибка при проверке кода модуля', [
                'module_code' => $moduleCode,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Проверка существования модуля по slug в таблице modules
     */
    private function checkModuleSlug(string $slug): bool
    {
        try {
            return Module::where('slug', $slug)->exists();
        } catch (\Exception $e) {
            Log::error('Ошибка при проверке slug модуля', [
                'slug' => $slug,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Проверка существования таблицы модуля в БД
     */
    private function checkModuleTable(string $moduleCode): bool
    {
        try {
            $tableName = strtolower($moduleCode); // Без префикса __module
            return Schema::hasTable($tableName);
        } catch (\Exception $e) {
            Log::error('Ошибка при проверке таблицы модуля', [
                'module_code' => $moduleCode,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Проверка существования директории модуля
     */
    private function checkModuleDirectory(string $moduleCode): bool
    {
        try {
            $moduleName = ucfirst($moduleCode); // Например, "News"
            $modulePath = base_path("Modules/{$moduleName}");
            return File::exists($modulePath);
        } catch (\Exception $e) {
            Log::error('Ошибка при проверке директории модуля', [
                'module_code' => $moduleCode,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Проверка существования миграций модуля
     */
    private function checkModuleMigrations(string $moduleCode): bool
    {
        try {
            $tableName = strtolower($moduleCode);
            
            // Проверяем записи в таблице migrations
            $migrationCount = DB::table('migrations')
                ->where('migration', 'like', "%{$moduleCode}%")
                ->orWhere('migration', 'like', "%{$tableName}%")
                ->count();
            
            return $migrationCount > 0;
        } catch (\Exception $e) {
            Log::error('Ошибка при проверке миграций модуля', [
                'module_code' => $moduleCode,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Проверка существования прав доступа модуля
     */
    private function checkModulePermissions(string $moduleCode): bool
    {
        try {
            $permissionCount = DB::table('permissions')
                ->where('name', 'like', "module_{$moduleCode}_%")
                ->count();
            
            return $permissionCount > 0;
        } catch (\Exception $e) {
            Log::warning('Ошибка при проверке прав доступа модуля', [
                'module_code' => $moduleCode,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Генерация ответа с ошибкой
     */
    private function generateErrorResponse(string $moduleCode, string $slug, array $checks): array
    {
        $moduleName = ucfirst($moduleCode);
        $tableName = strtolower($moduleCode);
        
        // Определяем, какие проверки не прошли
        $failedChecks = array_filter($checks);
        $failedCheckNames = array_keys($failedChecks);
        
        // Формируем сообщение об ошибке
        $errorMessages = [];
        
        if ($checks['code_in_modules_table']) {
            $errorMessages[] = "Модуль с кодом '{$moduleCode}' уже существует в базе данных.";
        }
        
        if ($checks['slug_in_modules_table']) {
            $errorMessages[] = "Модуль с адресом (slug) '{$slug}' уже существует.";
        }
        
        if ($checks['table_in_database']) {
            $errorMessages[] = "Таблица '{$tableName}' уже существует в базе данных.";
        }
        
        if ($checks['directory_in_filesystem']) {
            $errorMessages[] = "Директория модуля '{$moduleName}' уже существует в файловой системе.";
        }
        
        if ($checks['migrations_in_database']) {
            $errorMessages[] = "Миграции для модуля '{$moduleCode}' уже существуют в базе данных.";
        }
        
        if ($checks['permissions_in_database']) {
            $errorMessages[] = "Права доступа для модуля '{$moduleCode}' уже существуют.";
        }
        
        $errorMessage = implode(' ', $errorMessages);
        
        Log::warning('CheckModuleService: модуль уже существует', [
            'module_code' => $moduleCode,
            'slug' => $slug,
            'failed_checks' => $failedCheckNames,
            'error_message' => $errorMessage
        ]);
        
        return [
            'success' => false,
            'message' => $errorMessage,
            'module_code' => $moduleCode,
            'slug' => $slug,
            'failed_checks' => $failedCheckNames,
            'details' => [
                'table_name' => $tableName,
                'directory_name' => $moduleName,
                'module_path' => base_path("Modules/{$moduleName}"),
                'checks' => $checks
            ]
        ];
    }
    
    /**
     * Дополнительный метод для детальной проверки (для отладки)
     */
    public function detailedCheck(string $moduleCode, string $slug): array
    {
        // Сначала проверяем на зарезервированные имена
        $reservedCheck = $this->checkReservedNames($moduleCode, $slug);
        if (!$reservedCheck['success']) {
            return array_merge($reservedCheck, ['detailed' => true]);
        }
        
        $moduleName = ucfirst($moduleCode);
        $tableName = strtolower($moduleCode);
        
        $details = [
            'module_code' => $moduleCode,
            'module_name' => $moduleName,
            'slug' => $slug,
            'table_name' => $tableName,
            'reserved_check' => $reservedCheck,
            'checks' => []
        ];
        
        // Детальная проверка по коду
        try {
            $module = Module::getByCode($moduleCode);
            $details['checks']['code_in_modules_table'] = [
                'exists' => !is_null($module),
                'module_data' => $module ? $module->toArray() : null
            ];
        } catch (\Exception $e) {
            $details['checks']['code_in_modules_table'] = [
                'exists' => false,
                'error' => $e->getMessage()
            ];
        }
        
        // Детальная проверка по slug
        try {
            $modulesWithSlug = Module::where('slug', $slug)->get();
            $details['checks']['slug_in_modules_table'] = [
                'exists' => $modulesWithSlug->isNotEmpty(),
                'count' => $modulesWithSlug->count(),
                'modules' => $modulesWithSlug->toArray()
            ];
        } catch (\Exception $e) {
            $details['checks']['slug_in_modules_table'] = [
                'exists' => false,
                'error' => $e->getMessage()
            ];
        }
        
        // Детальная проверка таблицы
        try {
            $tableExists = Schema::hasTable($tableName);
            $details['checks']['table_in_database'] = [
                'exists' => $tableExists,
                'columns' => $tableExists ? Schema::getColumnListing($tableName) : []
            ];
        } catch (\Exception $e) {
            $details['checks']['table_in_database'] = [
                'exists' => false,
                'error' => $e->getMessage()
            ];
        }
        
        // Детальная проверка директории
        try {
            $modulePath = base_path("Modules/{$moduleName}");
            $dirExists = File::exists($modulePath);
            $details['checks']['directory_in_filesystem'] = [
                'exists' => $dirExists,
                'path' => $modulePath,
                'files' => $dirExists ? File::allFiles($modulePath) : []
            ];
        } catch (\Exception $e) {
            $details['checks']['directory_in_filesystem'] = [
                'exists' => false,
                'error' => $e->getMessage()
            ];
        }
        
        // Детальная проверка миграций
        try {
            $migrations = DB::table('migrations')
                ->where('migration', 'like', "%{$moduleCode}%")
                ->orWhere('migration', 'like', "%{$tableName}%")
                ->get();
            
            $details['checks']['migrations_in_database'] = [
                'exists' => $migrations->isNotEmpty(),
                'count' => $migrations->count(),
                'migrations' => $migrations->toArray()
            ];
        } catch (\Exception $e) {
            $details['checks']['migrations_in_database'] = [
                'exists' => false,
                'error' => $e->getMessage()
            ];
        }
        
        // Детальная проверка прав доступа
        try {
            $permissions = DB::table('permissions')
                ->where('name', 'like', "module_{$moduleCode}_%")
                ->get();
            
            $details['checks']['permissions_in_database'] = [
                'exists' => $permissions->isNotEmpty(),
                'count' => $permissions->count(),
                'permissions' => $permissions->toArray()
            ];
        } catch (\Exception $e) {
            $details['checks']['permissions_in_database'] = [
                'exists' => false,
                'error' => $e->getMessage()
            ];
        }
        
        return $details;
    }
    
    /**
     * Проверка только на зарезервированные имена (публичный метод для UI)
     * 
     * @param string $moduleCode
     * @param string $slug
     * @return array
     */
    public function checkOnlyReserved(string $moduleCode, string $slug): array
    {
        return $this->checkReservedNames($moduleCode, $slug);
    }
}