<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RouterLoaderService
{
    /**
     * Базовые пути для статических маршрутов
     */
    private array $staticPaths = [
        'admin' => 'app/Admin/routes/web.php',
        'modules' => 'app/Modules',
    ];

    /**
     * Динамические модули (пользовательские)
     */
    private string $dynamicModulesPath = 'Modules';

    /**
     * Загружает все маршруты системы
     */
    public function loadAllRoutes(): void
    {
        $this->loadStaticRoutes();
        $this->loadDynamicModuleRoutes();
    }

    /**
     * Загрузка статических маршрутов
     */
    private function loadStaticRoutes(): void
    {
        // 1. Основные маршруты админки
        $adminRoutes = base_path($this->staticPaths['admin']);
        if (File::exists($adminRoutes)) {
            Route::middleware(['web', 'auth', 'admin'])
                ->group($adminRoutes);
        }

        // 2. Маршруты системных модулей
        $modulesPath = base_path($this->staticPaths['modules']);
        if (File::exists($modulesPath)) {
            $modules = File::directories($modulesPath);
            
            foreach ($modules as $modulePath) {
                $moduleName = basename($modulePath);
                
                // Загружаем web.php маршруты для ВСЕХ модулей, включая User
                $webRoutes = $modulePath . '/routes/web.php';
                if (File::exists($webRoutes)) {
                    $this->loadModuleWebRoutes($moduleName, $webRoutes);
                }
                
                // Загружаем api.php маршруты (если есть)
                $apiRoutes = $modulePath . '/routes/api.php';
                if (File::exists($apiRoutes)) {
                    $this->loadModuleApiRoutes($moduleName, $apiRoutes);
                }
            }
        }
    }

    /**
     * Загрузка маршрутов динамических модулей
     */
    private function loadDynamicModuleRoutes(): void
    {
        $modulesPath = base_path($this->dynamicModulesPath);
        
        if (!File::exists($modulesPath)) {
            return;
        }

        $modules = File::directories($modulesPath);
        
        foreach ($modules as $modulePath) {
            $moduleName = basename($modulePath);
            
            // Проверяем, что название модуля начинается с заглавной буквы
            if (!ctype_upper($moduleName[0])) {
                continue;
            }
            
            // Проверяем, активен ли модуль
            if (!$this->isModuleActive($moduleName)) {
                continue;
            }
            
            // Загружаем web.php маршруты
            $webRoutes = $modulePath . '/routes/web.php';
            if (File::exists($webRoutes)) {
                $this->loadDynamicModuleWebRoutes($moduleName, $webRoutes);
            }
            
            $this->markModuleAsLoaded($moduleName);
        }
    }

    /**
     * Загрузка web маршрутов системного модуля
     */
    private function loadModuleWebRoutes(string $moduleName, string $routeFile): void
    {
        $content = File::get($routeFile);
        
        // Анализируем содержимое файла маршрутов
        $hasPrefixAdmin = Str::contains($content, "prefix('admin") || Str::contains($content, 'Route::prefix');
        $hasMiddleware = Str::contains($content, "middleware(['web', 'auth', 'admin']") || 
                        Str::contains($content, 'middleware("web", "auth", "admin")');
        
        // Для модуля User маршруты уже имеют префикс /users и middleware
        if ($moduleName === 'User') {
            // Если уже есть middleware, просто подключаем
            if ($hasMiddleware) {
                require $routeFile;
            } else {
                // Добавляем middleware к существующим маршрутам
                Route::middleware(['web', 'auth', 'admin'])->group($routeFile);
            }
        }
        // Если маршруты уже имеют префикс admin и middleware, просто подключаем
        else if ($hasPrefixAdmin && $hasMiddleware) {
            require $routeFile;
        } 
        // Если есть только префикс admin, добавляем middleware
        else if ($hasPrefixAdmin) {
            Route::middleware(['web', 'auth', 'admin'])->group($routeFile);
        } 
        // Для остальных модулей добавляем префикс и middleware
        else {
            Route::middleware(['web', 'auth', 'admin'])
                ->prefix('admin/' . strtolower($moduleName))
                ->name('admin.' . strtolower($moduleName) . '.')
                ->group($routeFile);
        }
    }

    /**
     * Загрузка API маршрутов системного модуля
     */
    private function loadModuleApiRoutes(string $moduleName, string $routeFile): void
    {
        Route::middleware('api')
            ->prefix('api/' . strtolower($moduleName))
            ->name('api.' . strtolower($moduleName) . '.')
            ->group($routeFile);
    }

    /**
     * Загрузка web маршрутов динамического модуля
     */
    private function loadDynamicModuleWebRoutes(string $moduleName, string $routeFile): void
    {
        Route::middleware(['web', 'auth', 'admin'])
            ->prefix('admin/' . strtolower($moduleName))
            ->name('admin.' . strtolower($moduleName) . '.')
            ->group($routeFile);
    }

    /**
     * Проверка активности модуля (из БД)
     */
    private function isModuleActive(string $moduleName): bool
    {
        $configFile = base_path("Modules/{$moduleName}/module.json");
        
        if (File::exists($configFile)) {
            $config = json_decode(File::get($configFile), true);
            return $config['active'] ?? true;
        }
        
        return true;
    }

    /**
     * Помечает модуль как загруженный
     */
    private function markModuleAsLoaded(string $moduleName): void
    {
        app('module.routes')->markAsLoaded($moduleName);
    }

    /**
     * Возвращает список загруженных модулей
     */
    public function getLoadedModules(): array
    {
        return app('module.routes')->getLoadedModules();
    }

    /**
     * Проверяет, загружен ли модуль
     */
    public function isModuleLoaded(string $module): bool
    {
        return app('module.routes')->isLoaded($module);
    }

    /**
     * Регистрирует маршруты для конкретного модуля (для командной строки)
     */
    public function loadModuleRoutes(string $moduleName): bool
    {
        // Системные модули
        $systemPath = base_path("app/Modules/{$moduleName}/routes/web.php");
        if (File::exists($systemPath)) {
            $this->loadModuleWebRoutes($moduleName, $systemPath);
            return true;
        }

        // Динамические модули
        $dynamicPath = base_path("Modules/{$moduleName}/routes/web.php");
        if (File::exists($dynamicPath)) {
            $this->loadDynamicModuleWebRoutes($moduleName, $dynamicPath);
            return true;
        }

        return false;
    }

    /**
     * Получает список всех доступных модулей
     */
    public function getAvailableModules(): array
    {
        $modules = [];
        
        // Системные модули
        $systemPath = base_path($this->staticPaths['modules']);
        if (File::exists($systemPath)) {
            $systemModules = File::directories($systemPath);
            foreach ($systemModules as $modulePath) {
                $modules[] = [
                    'name' => basename($modulePath),
                    'type' => 'system',
                    'path' => $modulePath,
                ];
            }
        }
        
        // Динамические модули
        $dynamicPath = base_path($this->dynamicModulesPath);
        if (File::exists($dynamicPath)) {
            $dynamicModules = File::directories($dynamicPath);
            foreach ($dynamicModules as $modulePath) {
                $moduleName = basename($modulePath);
                if (ctype_upper($moduleName[0])) {
                    $modules[] = [
                        'name' => $moduleName,
                        'type' => 'dynamic',
                        'path' => $modulePath,
                    ];
                }
            }
        }
        
        return $modules;
    }

    // Добавьте этот метод в app/Core/Services/RouterLoaderService.php
    /**
     * Проверяет наличие маршрутов у модуля
     * Задача: Определить, есть ли у модуля файлы маршрутов
     * Проверяет:
     *   - Системные модули: app/Modules/{name}/routes/{type}.php
     *   - Динамические модули: Modules/{name}/routes/{type}.php
     */
    public function moduleHasRoutes(string $moduleName, string $type = 'web'): bool
    {
        // Проверяем системные модули
        $systemPath = base_path("app/Modules/{$moduleName}/routes/{$type}.php");
        if (File::exists($systemPath)) {
            return true;
        }
        
        // Проверяем динамические модули
        $dynamicPath = base_path("Modules/{$moduleName}/routes/{$type}.php");
        return File::exists($dynamicPath);
    }
}