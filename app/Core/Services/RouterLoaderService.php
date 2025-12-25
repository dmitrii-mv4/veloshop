<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

/**
 * Сервис для динамической загрузки маршрутов из различных источников
 * Обрабатывает маршруты админ-панели, системных и пользовательских модулей
 */
class RouterLoaderService
{
    /**
     * Конфигурация путей к маршрутам
     * Массивы разделены по типам для легкого расширения
     */
    protected array $routeConfig = [
        // 1. Маршруты админ-панели
        'admin' => [
            'path' => 'app/Admin/routes/web.php',
            'prefix' => 'admin',
            'middleware' => ['web', 'admin']
        ],
        
        // 2. Системные модули
        'system_modules' => [
            'Integrator' => [
                'path' => 'app/Modules/Integrator/routes/web.php',
                'prefix' => '',
                'middleware' => ['web']
            ],
            'MediaLib' => [
                'path' => 'app/Modules/MediaLib/routes/web.php',
                'prefix' => '',
                'middleware' => ['web']
            ],
            'ModuleGenerator' => [
                'path' => 'app/Modules/ModuleGenerator/routes/web.php',
                'prefix' => 'module-generator',
                'middleware' => ['web', 'admin']
            ],
            'Page' => [
                'path' => 'app/Modules/Page/routes/web.php',
                'prefix' => '',
                'middleware' => ['web']
            ],
            'Role' => [
                'path' => 'app/Modules/Role/routes/web.php',
                'prefix' => 'roles',
                'middleware' => ['web', 'admin']
            ],
            'User' => [
                'path' => 'app/Modules/User/routes/web.php',
                'prefix' => 'users',
                'middleware' => ['web', 'admin']
            ],
            'InfoBlock' => [
                'path' => 'app/Modules/InfoBlock/routes/web.php',
                'prefix' => 'info_block',
                'middleware' => ['web']
            ]
        ],
        
        // 3. Динамические модули (созданные через ModuleGenerator)
        'dynamic_modules' => [
            'base_path' => 'Modules',
            'route_file' => 'routes/web.php',
            'middleware' => ['web']
        ]
    ];

    /**
     * Загружает все маршруты системы
     */
    public function loadAllRoutes(): void
    {
        Log::info('[RouterLoaderService] Начало загрузки маршрутов');
        
        $this->loadAdminRoutes();
        $this->loadSystemModuleRoutes();
        $this->loadDynamicModuleRoutes();
        
        Log::info('[RouterLoaderService] Загрузка маршрутов завершена');
    }

    /**
     * Загружает маршруты админ-панели
     */
    protected function loadAdminRoutes(): void
    {
        $adminConfig = $this->routeConfig['admin'];
        $adminPath = base_path($adminConfig['path']);
        
        if (File::exists($adminPath)) {
            try {
                Route::prefix($adminConfig['prefix'])
                    ->middleware($adminConfig['middleware'])
                    ->group(function () use ($adminPath) {
                        require $adminPath;
                    });
                
                Log::info('[RouterLoaderService] Админские маршруты загружены', [
                    'path' => $adminConfig['path']
                ]);
            } catch (\Exception $e) {
                Log::error('[RouterLoaderService] Ошибка загрузки админских маршрутов', [
                    'path' => $adminConfig['path'],
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        } else {
            Log::warning('[RouterLoaderService] Файл админских маршрутов не найден', [
                'path' => $adminConfig['path']
            ]);
        }
    }

    /**
     * Загружает маршруты системных модулей
     */
    protected function loadSystemModuleRoutes(): void
    {
        foreach ($this->routeConfig['system_modules'] as $moduleName => $config) {
            $modulePath = base_path($config['path']);
            
            if (!File::exists($modulePath)) {
                Log::warning('[RouterLoaderService] Файл маршрутов системного модуля не найден', [
                    'module' => $moduleName,
                    'path' => $config['path']
                ]);
                continue;
            }
            
            try {
                Route::prefix($config['prefix'])
                    ->middleware($config['middleware'])
                    ->group(function () use ($modulePath) {
                        require $modulePath;
                    });
                
                Log::info('[RouterLoaderService] Маршруты системного модуля загружены', [
                    'module' => $moduleName,
                    'path' => $config['path']
                ]);
            } catch (\Exception $e) {
                Log::error('[RouterLoaderService] Ошибка загрузки маршрутов системного модуля', [
                    'module' => $moduleName,
                    'path' => $config['path'],
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Загружает маршруты динамических модулей
     */
    protected function loadDynamicModuleRoutes(): void
    {
        $dynamicConfig = $this->routeConfig['dynamic_modules'];
        $modulesBasePath = base_path($dynamicConfig['base_path']);
        
        if (!File::exists($modulesBasePath) || !File::isDirectory($modulesBasePath)) {
            Log::warning('[RouterLoaderService] Базовая директория динамических модулей не найдена', [
                'path' => $dynamicConfig['base_path']
            ]);
            return;
        }
        
        // Получаем все директории в Modules (только первого уровня)
        $moduleDirectories = File::directories($modulesBasePath);
        
        foreach ($moduleDirectories as $moduleDir) {
            $moduleName = basename($moduleDir);
            
            // Проверяем, что имя модуля начинается с заглавной буквы
            if (!ctype_upper($moduleName[0])) {
                Log::warning('[RouterLoaderService] Имя модуля должно начинаться с заглавной буквы', [
                    'module' => $moduleName,
                    'path' => $moduleDir
                ]);
                continue;
            }
            
            $routeFile = $moduleDir . '/' . $dynamicConfig['route_file'];
            
            if (!File::exists($routeFile)) {
                Log::debug('[RouterLoaderService] Файл маршрутов динамического модуля не найден', [
                    'module' => $moduleName,
                    'path' => $routeFile
                ]);
                continue;
            }
            
            try {
                Route::middleware($dynamicConfig['middleware'])
                    ->group(function () use ($routeFile) {
                        require $routeFile;
                    });
                
                Log::info('[RouterLoaderService] Маршруты динамического модуля загружены', [
                    'module' => $moduleName,
                    'path' => $routeFile
                ]);
            } catch (\Exception $e) {
                Log::error('[RouterLoaderService] Ошибка загрузки маршрутов динамического модуля', [
                    'module' => $moduleName,
                    'path' => $routeFile,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Возвращает информацию о загруженных маршрутах (для отладки)
     */
    public function getLoadedRoutesInfo(): array
    {
        $info = [
            'admin' => [
                'config' => $this->routeConfig['admin'],
                'loaded' => File::exists(base_path($this->routeConfig['admin']['path']))
            ],
            'system_modules' => [],
            'dynamic_modules' => []
        ];
        
        // Информация о системных модулях
        foreach ($this->routeConfig['system_modules'] as $module => $config) {
            $info['system_modules'][$module] = [
                'config' => $config,
                'loaded' => File::exists(base_path($config['path']))
            ];
        }
        
        // Информация о динамических модулях
        $dynamicConfig = $this->routeConfig['dynamic_modules'];
        $modulesBasePath = base_path($dynamicConfig['base_path']);
        
        if (File::exists($modulesBasePath) && File::isDirectory($modulesBasePath)) {
            $moduleDirectories = File::directories($modulesBasePath);
            
            foreach ($moduleDirectories as $moduleDir) {
                $moduleName = basename($moduleDir);
                $routeFile = $moduleDir . '/' . $dynamicConfig['route_file'];
                
                $info['dynamic_modules'][$moduleName] = [
                    'path' => $routeFile,
                    'loaded' => File::exists($routeFile),
                    'valid_name' => ctype_upper($moduleName[0])
                ];
            }
        }
        
        return $info;
    }
}