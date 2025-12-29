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
        // 1. WEB маршруты админ-панели
        'admin_web' => [
            'path' => 'app/Admin/routes/web.php',
            'prefix' => '',
            'middleware' => ['web', 'admin']
        ],
        
        // 2. API маршруты админ-панели
        'admin_api' => [
            'path' => 'app/Admin/routes/api.php',
            'prefix' => 'api',
            'middleware' => ['web']
        ],
        
        // 3. Системные модули WEB
        'system_modules_web' => [
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
            'IBlock' => [
                'path' => 'app/Modules/IBlock/routes/web.php',
                'prefix' => '',
                'middleware' => ['web']
            ]
        ],
        
        // 4. Системные модули API
        'system_modules_api' => [
            'Integrator' => [
                'path' => 'app/Modules/Integrator/routes/api.php',
                'prefix' => 'api/integrator',
                'middleware' => ['api']
            ],
            'MediaLib' => [
                'path' => 'app/Modules/MediaLib/routes/api.php',
                'prefix' => 'api/media',
                'middleware' => ['api']
            ],
            'ModuleGenerator' => [
                'path' => 'app/Modules/ModuleGenerator/routes/api.php',
                'prefix' => 'api/module-generator',
                'middleware' => ['api', 'admin']
            ],
            'Page' => [
                'path' => 'app/Modules/Page/routes/api.php',
                'prefix' => 'api/pages',
                'middleware' => ['web']
            ],
            'Role' => [
                'path' => 'app/Modules/Role/routes/api.php',
                'prefix' => 'api/roles',
                'middleware' => ['api', 'admin']
            ],
            'User' => [
                'path' => 'app/Modules/User/routes/api.php',
                'prefix' => 'api/users',
                'middleware' => ['api', 'admin']
            ],
            'IBlock' => [
                'path' => 'app/Modules/IBlock/routes/api.php',
                'prefix' => 'api/iblocks',
                'middleware' => ['web']
            ]
        ],
        
        // 5. Динамические модули WEB (созданные через ModuleGenerator)
        'dynamic_modules_web' => [
            'base_path' => 'Modules',
            'route_file' => 'routes/web.php',
            'middleware' => ['web']
        ],
        
        // 6. Динамические модули API (созданные через ModuleGenerator)
        'dynamic_modules_api' => [
            'base_path' => 'Modules',
            'route_file' => 'routes/api.php',
            'middleware' => ['web']
        ]
    ];

    /**
     * Загружает все маршруты системы
     */
    public function loadAllRoutes(): void
    {
        Log::info('[RouterLoaderService] Начало загрузки маршрутов');
        
        // Загружаем WEB маршруты
        $this->loadAdminWebRoutes();
        $this->loadSystemModuleWebRoutes();
        $this->loadDynamicModuleWebRoutes();
        
        // Загружаем API маршруты
        $this->loadAdminApiRoutes();
        $this->loadSystemModuleApiRoutes();
        $this->loadDynamicModuleApiRoutes();
        
        Log::info('[RouterLoaderService] Загрузка маршрутов завершена');
    }

    /**
     * Загружает WEB маршруты админ-панели
     */
    protected function loadAdminWebRoutes(): void
    {
        $this->loadAdminRoutes('admin_web', 'Админские WEB маршруты');
    }

    /**
     * Загружает API маршруты админ-панели
     */
    protected function loadAdminApiRoutes(): void
    {
        $this->loadAdminRoutes('admin_api', 'Админские API маршруты');
    }

    /**
     * Общий метод загрузки админских маршрутов
     */
    private function loadAdminRoutes(string $configKey, string $logMessage): void
    {
        $adminConfig = $this->routeConfig[$configKey];
        $adminPath = base_path($adminConfig['path']);
        
        if (File::exists($adminPath)) {
            try {
                Route::prefix($adminConfig['prefix'])
                    ->middleware($adminConfig['middleware'])
                    ->group(function () use ($adminPath) {
                        require $adminPath;
                    });
                
                Log::info("[RouterLoaderService] {$logMessage} загружены", [
                    'path' => $adminConfig['path']
                ]);
            } catch (\Exception $e) {
                Log::error("[RouterLoaderService] Ошибка загрузки {$logMessage}", [
                    'path' => $adminConfig['path'],
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        } else {
            Log::warning("[RouterLoaderService] Файл {$logMessage} не найден", [
                'path' => $adminConfig['path']
            ]);
        }
    }

    /**
     * Загружает WEB маршруты системных модулей
     */
    protected function loadSystemModuleWebRoutes(): void
    {
        $this->loadSystemModuleRoutes('system_modules_web', 'WEB маршруты системного модуля');
    }

    /**
     * Загружает API маршруты системных модулей
     */
    protected function loadSystemModuleApiRoutes(): void
    {
        $this->loadSystemModuleRoutes('system_modules_api', 'API маршруты системного модуля');
    }

    /**
     * Общий метод загрузки маршрутов системных модулей
     */
    private function loadSystemModuleRoutes(string $configKey, string $logMessage): void
    {
        foreach ($this->routeConfig[$configKey] as $moduleName => $config) {
            $modulePath = base_path($config['path']);
            
            if (!File::exists($modulePath)) {
                Log::warning("[RouterLoaderService] Файл {$logMessage} не найден", [
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
                
                Log::info("[RouterLoaderService] {$logMessage} загружены", [
                    'module' => $moduleName,
                    'path' => $config['path']
                ]);
            } catch (\Exception $e) {
                Log::error("[RouterLoaderService] Ошибка загрузки {$logMessage}", [
                    'module' => $moduleName,
                    'path' => $config['path'],
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Загружает WEB маршруты динамических модулей
     */
    protected function loadDynamicModuleWebRoutes(): void
    {
        $this->loadDynamicModuleRoutes('dynamic_modules_web', 'WEB маршруты динамического модуля');
    }

    /**
     * Загружает API маршруты динамических модулей
     */
    protected function loadDynamicModuleApiRoutes(): void
    {
        $this->loadDynamicModuleRoutes('dynamic_modules_api', 'API маршруты динамического модуля');
    }

    /**
     * Общий метод загрузки маршрутов динамических модулей
     */
    private function loadDynamicModuleRoutes(string $configKey, string $logMessage): void
    {
        $dynamicConfig = $this->routeConfig[$configKey];
        $modulesBasePath = base_path($dynamicConfig['base_path']);
        
        if (!File::exists($modulesBasePath) || !File::isDirectory($modulesBasePath)) {
            Log::warning("[RouterLoaderService] Базовая директория динамических модулей не найдена", [
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
                Log::debug("[RouterLoaderService] Файл {$logMessage} не найден", [
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
                
                Log::info("[RouterLoaderService] {$logMessage} загружены", [
                    'module' => $moduleName,
                    'path' => $routeFile
                ]);
            } catch (\Exception $e) {
                Log::error("[RouterLoaderService] Ошибка загрузки {$logMessage}", [
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
            'admin_web' => [
                'config' => $this->routeConfig['admin_web'],
                'loaded' => File::exists(base_path($this->routeConfig['admin_web']['path']))
            ],
            'admin_api' => [
                'config' => $this->routeConfig['admin_api'],
                'loaded' => File::exists(base_path($this->routeConfig['admin_api']['path']))
            ],
            'system_modules_web' => [],
            'system_modules_api' => [],
            'dynamic_modules_web' => [],
            'dynamic_modules_api' => []
        ];
        
        // Информация о системных модулях WEB
        foreach ($this->routeConfig['system_modules_web'] as $module => $config) {
            $info['system_modules_web'][$module] = [
                'config' => $config,
                'loaded' => File::exists(base_path($config['path']))
            ];
        }
        
        // Информация о системных модулях API
        foreach ($this->routeConfig['system_modules_api'] as $module => $config) {
            $info['system_modules_api'][$module] = [
                'config' => $config,
                'loaded' => File::exists(base_path($config['path']))
            ];
        }
        
        // Информация о динамических модулях WEB
        $dynamicWebConfig = $this->routeConfig['dynamic_modules_web'];
        $modulesBasePath = base_path($dynamicWebConfig['base_path']);
        
        if (File::exists($modulesBasePath) && File::isDirectory($modulesBasePath)) {
            $moduleDirectories = File::directories($modulesBasePath);
            
            foreach ($moduleDirectories as $moduleDir) {
                $moduleName = basename($moduleDir);
                $routeFile = $moduleDir . '/' . $dynamicWebConfig['route_file'];
                
                $info['dynamic_modules_web'][$moduleName] = [
                    'path' => $routeFile,
                    'loaded' => File::exists($routeFile),
                    'valid_name' => ctype_upper($moduleName[0])
                ];
            }
        }
        
        // Информация о динамических модулях API
        $dynamicApiConfig = $this->routeConfig['dynamic_modules_api'];
        $modulesBasePath = base_path($dynamicApiConfig['base_path']);
        
        if (File::exists($modulesBasePath) && File::isDirectory($modulesBasePath)) {
            $moduleDirectories = File::directories($modulesBasePath);
            
            foreach ($moduleDirectories as $moduleDir) {
                $moduleName = basename($moduleDir);
                $routeFile = $moduleDir . '/' . $dynamicApiConfig['route_file'];
                
                $info['dynamic_modules_api'][$moduleName] = [
                    'path' => $routeFile,
                    'loaded' => File::exists($routeFile),
                    'valid_name' => ctype_upper($moduleName[0])
                ];
            }
        }
        
        return $info;
    }

    /**
     * Получает конфигурацию маршрутов
     */
    public function getRouteConfig(): array
    {
        return $this->routeConfig;
    }

    /**
     * Устанавливает конфигурацию маршрутов
     */
    public function setRouteConfig(array $config): void
    {
        $this->routeConfig = $config;
    }

    /**
     * Добавляет конфигурацию для нового системного модуля
     */
    public function addSystemModuleConfig(string $moduleName, array $webConfig, array $apiConfig): void
    {
        // Добавляем WEB конфигурацию
        if (!isset($this->routeConfig['system_modules_web'][$moduleName])) {
            $this->routeConfig['system_modules_web'][$moduleName] = $webConfig;
            Log::info('[RouterLoaderService] Добавлена WEB конфигурация для системного модуля', [
                'module' => $moduleName,
                'config' => $webConfig
            ]);
        }
        
        // Добавляем API конфигурацию
        if (!isset($this->routeConfig['system_modules_api'][$moduleName])) {
            $this->routeConfig['system_modules_api'][$moduleName] = $apiConfig;
            Log::info('[RouterLoaderService] Добавлена API конфигурация для системного модуля', [
                'module' => $moduleName,
                'config' => $apiConfig
            ]);
        }
    }
}