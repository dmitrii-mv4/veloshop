<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

/**
 * Сервис для управления views системных и динамических модулей
 * Содержит всю бизнес-логику регистрации и управления представлениями
 */
class ViewsService
{
    /**
     * Конфигурация системных модулей
     * Формат: 'namespace_view' => 'относительный_путь_к_директории_views'
     */
    private array $systemModules = [
        'admin' => 'app/Admin/views',
        'integrator' => 'app/Modules/Integrator/views',
        'media' => 'app/Modules/MediaLib/views',
        'module_generator' => 'app/Modules/ModuleGenerator/views',
        'page' => 'app/Modules/Page/views',
        'role' => 'app/Modules/Role/views',
        'user' => 'app/Modules/User/views',
    ];

    /**
     * Загружает views всех системных модулей
     * Возвращает информацию о зарегистрированных модулях
     */
    public function loadSystemModulesViews(): array
    {
        Log::info('[ViewsService] Начало загрузки views системных модулей');
        
        $registeredModules = [];
        
        foreach ($this->systemModules as $namespace => $relativePath) {
            $absolutePath = base_path($relativePath);
            
            if (File::isDirectory($absolutePath)) {
                $registeredModules[$namespace] = [
                    'path' => $absolutePath,
                    'success' => true
                ];
                
                Log::info('[ViewsService] Views системного модуля подготовлены к регистрации', [
                    'module' => $namespace,
                    'path' => $absolutePath,
                ]);
            } else {
                Log::warning('[ViewsService] Директория views системного модуля не найдена', [
                    'module' => $namespace,
                    'path' => $absolutePath,
                ]);
            }
        }
        
        Log::info('[ViewsService] Загрузка views системных модулей завершена');
        return $registeredModules;
    }

    /**
     * Загружает views динамических модулей (созданных через ModuleGenerator)
     * Возвращает информацию о зарегистрированных модулях
     */
    public function loadDynamicModulesViews(): array
    {
        Log::info('[ViewsService] Начало загрузки views динамических модулей');
        
        $registeredModules = [];
        
        try {
            // Проверяем существование модели Module
            if (!class_exists('App\Modules\ModuleGenerator\Models\Module')) {
                Log::warning('[ViewsService] Модель Module не найдена, пропускаем загрузку динамических модулей');
                return $registeredModules;
            }
            
            // Проверяем существование таблицы modules
            if (!Schema::hasTable('modules')) {
                Log::warning('[ViewsService] Таблица modules не существует, пропускаем загрузку динамических модулей');
                return $registeredModules;
            }
            
            // Получаем активные модули
            $activeModules = \App\Modules\ModuleGenerator\Models\Module::where('status', 1)->get();
            
            Log::info('[ViewsService] Найдено активных модулей: ' . $activeModules->count());
            
            // Для отладки: логируем структуру первого модуля
            if ($activeModules->count() > 0) {
                $firstModule = $activeModules->first();
                Log::debug('[ViewsService] Структура первого модуля для отладки', [
                    'attributes' => $firstModule->getAttributes(),
                    'toArray' => $firstModule->toArray()
                ]);
            }
            
            foreach ($activeModules as $module) {
                $moduleInfo = $this->prepareDynamicModuleViews($module);
                if ($moduleInfo) {
                    $registeredModules[$moduleInfo['namespace']] = $moduleInfo;
                }
            }
            
            Log::info('[ViewsService] Загрузка views динамических модулей завершена');
            
        } catch (\Exception $e) {
            Log::error('[ViewsService] Ошибка при загрузке views динамических модулей', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        return $registeredModules;
    }

    /**
     * Подготавливает информацию о views динамического модуля для регистрации
     */
    private function prepareDynamicModuleViews($module): ?array
    {
        try {
            // Используем безопасное получение имени модуля
            $moduleName = null;
            $moduleId = null;
            
            if (is_object($module)) {
                $moduleId = $module->id ?? 'unknown';
                
                // Пробуем разные варианты получения имени модуля
                // Используем code_module вместо name
                $moduleName = $module->code_module ?? $module->name ?? $module->title ?? $module->module_name ?? null;
                
                if (empty($moduleName)) {
                    Log::error('[ViewsService] Не удалось получить имя модуля (code_module)', [
                        'module_id' => $moduleId,
                        'module_class' => get_class($module),
                        'module_data' => json_encode($module->toArray())
                    ]);
                    return null;
                }
                
                // Преобразуем code_module к правильному формату (первая буква заглавная)
                // Например: "news" -> "News"
                $moduleName = ucfirst($moduleName);
                
            } elseif (is_array($module)) {
                $moduleId = $module['id'] ?? 'unknown';
                $moduleName = $module['code_module'] ?? $module['name'] ?? $module['title'] ?? $module['module_name'] ?? null;
                
                if (empty($moduleName)) {
                    Log::error('[ViewsService] Не удалось получить имя модуля из массива', [
                        'module_data' => json_encode($module)
                    ]);
                    return null;
                }
                
                $moduleName = ucfirst($moduleName);
            } else {
                Log::error('[ViewsService] Модуль не является объектом или массивом', [
                    'module_type' => gettype($module),
                    'module_value' => $module
                ]);
                return null;
            }
            
            // Проверяем, что имя модуля начинается с заглавной буквы
            if (empty($moduleName) || !ctype_upper($moduleName[0])) {
                Log::warning('[ViewsService] Имя модуля должно начинаться с заглавной буквы', [
                    'module' => $moduleName,
                    'id' => $moduleId
                ]);
                return null;
            }
            
            // Путь к директории модуля
            $moduleDir = base_path("Modules/{$moduleName}");
            
            // Проверяем существование директории модуля
            if (!File::isDirectory($moduleDir)) {
                Log::warning('[ViewsService] Директория модуля не найдена', [
                    'module' => $moduleName,
                    'path' => $moduleDir,
                    'id' => $moduleId
                ]);
                return null;
            }
            
            // Путь к views модуля
            $viewsPath = $moduleDir . '/views';
            
            // Проверяем существование директории views
            if (!File::isDirectory($viewsPath)) {
                Log::debug('[ViewsService] Директория views модуля не найдена (может быть пустой)', [
                    'module' => $moduleName,
                    'path' => $viewsPath,
                    'id' => $moduleId
                ]);
                return null;
            }
            
            // Используем имя модуля в нижнем регистре для namespace (например: 'news')
            $namespace = strtolower($moduleName);
            
            Log::info('[ViewsService] Views динамического модуля подготовлены к регистрации', [
                'module' => $moduleName,
                'namespace' => $namespace,
                'path' => $viewsPath,
                'id' => $moduleId
            ]);
            
            return [
                'module_name' => $moduleName,
                'namespace' => $namespace,
                'path' => $viewsPath,
                'id' => $moduleId,
                'success' => true
            ];
            
        } catch (\Exception $e) {
            Log::error('[ViewsService] Ошибка при подготовке views динамического модуля', [
                'module_id' => $moduleId ?? 'unknown',
                'module_name' => $moduleName ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Получает активные модули для передачи в шаблоны
     */
    public function getActiveModules(): \Illuminate\Support\Collection
    {
        try {
            // Проверяем существование модели Module
            if (!class_exists('App\Modules\ModuleGenerator\Models\Module')) {
                Log::warning('[ViewsService] Модель Module не найдена при получении активных модулей');
                return collect();
            }
            
            // Проверяем существование таблицы modules
            if (!Schema::hasTable('modules')) {
                Log::warning('[ViewsService] Таблица modules не существует при получении активных модулей');
                return collect();
            }
            
            // Пытаемся получить активные модули
            $modules = \App\Modules\ModuleGenerator\Models\Module::where('status', 1)->get();
            
            Log::info('[ViewsService] Получено активных модулей: ' . $modules->count());
            
            return $modules;
            
        } catch (\Exception $e) {
            // В случае ошибки (например, таблицы не существует) возвращаем пустую коллекцию
            Log::error('[ViewsService] Ошибка при получении активных модулей', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return collect();
        }
    }

    /**
     * Получение информации о зарегистрированных модулях (для отладки)
     */
    public function getRegisteredModulesInfo(): array
    {
        $info = [
            'system_modules' => [],
            'dynamic_modules' => []
        ];
        
        // Информация о системных модулях
        foreach ($this->systemModules as $namespace => $path) {
            $info['system_modules'][$namespace] = [
                'path' => base_path($path),
                'exists' => File::isDirectory(base_path($path)),
            ];
        }
        
        // Информация о динамических модулях
        try {
            if (class_exists('App\Modules\ModuleGenerator\Models\Module') && Schema::hasTable('modules')) {
                $activeModules = \App\Modules\ModuleGenerator\Models\Module::where('status', 1)->get();
                
                foreach ($activeModules as $module) {
                    // Используем code_module и преобразуем к правильному формату
                    $rawModuleName = $module->code_module ?? $module->name ?? $module->title ?? $module->module_name ?? 'unknown';
                    $moduleName = ucfirst($rawModuleName);
                    
                    $viewsPath = base_path("Modules/{$moduleName}/views");
                    $moduleDir = base_path("Modules/{$moduleName}");
                    
                    $info['dynamic_modules'][$moduleName] = [
                        'id' => $module->id,
                        'status' => $module->status,
                        'raw_name' => $rawModuleName,
                        'formatted_name' => $moduleName,
                        'module_dir_exists' => File::isDirectory($moduleDir),
                        'views_dir_exists' => File::isDirectory($viewsPath),
                        'namespace' => strtolower($moduleName),
                        'valid_name' => !empty($moduleName) && ctype_upper($moduleName[0])
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('[ViewsService] Ошибка при получении информации о динамических модулях', [
                'error' => $e->getMessage()
            ]);
        }
        
        return $info;
    }

    /**
     * Настройка отображения настроек через View composer
     */
    public function setupSettingsView(): void
    {
        try {
            View::composer('*', function ($view) {
                try {
                    if (Schema::hasTable('settings')) {
                        $settings = \App\Admin\Models\Settings::first();
                        $view->with('settings', $settings ? $settings->toArray() : []);
                    } else {
                        $view->with('settings', []);
                    }
                } catch (\Exception $e) {
                    Log::error('[ViewsService] Ошибка в View composer для settings', [
                        'error' => $e->getMessage()
                    ]);
                    $view->with('settings', []);
                }
            });
            
            Log::debug('[ViewsService] View composer для settings установлен');
        } catch (\Exception $e) {
            Log::error('[ViewsService] Ошибка при установке View composer для settings', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Возвращает конфигурацию системных модулей
     */
    public function getSystemModulesConfig(): array
    {
        return $this->systemModules;
    }
}