<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DynamicModulesService
{
    /**
     * Загружает все динамические модули (views, routes, providers)
     */
    public static function loadAll(): void
    {
        if (!self::canLoadModules()) {
            Log::warning('Невозможно загрузить динамические модули: таблица modules не существует');
            return;
        }

        self::loadViews();
        self::loadRoutes();
        self::loadServiceProviders();
    }

    /**
     * Проверяет возможность загрузки модулей
     */
    public static function canLoadModules(): bool
    {
        return Schema::hasTable('modules');
    }

    /**
     * Загружает views динамических модулей
     */
    public static function loadViews(): void
    {
        if (!self::canLoadModules()) {
            return;
        }

        try {
            $modules = \App\Modules\ModuleGenerator\Models\Module::where('status', 1)->get();

            foreach ($modules as $module) {
                $moduleName = Str::ucfirst($module['code_module']);
                $viewsPath = base_path('Modules/' . $moduleName . '/views');

                if (is_dir($viewsPath)) {
                    app()->loadViewsFrom($viewsPath, $module['code_module']);
                    Log::info('Views загружены для модуля: ' . $moduleName);
                }
            }
        } catch (\Exception $e) {
            Log::error('Ошибка загрузки views модулей: ' . $e->getMessage());
        }
    }

    /**
     * Загружает routes динамических модулей
     */
    public static function loadRoutes(): void
    {
        if (!self::canLoadModules()) {
            return;
        }

        try {
            $modules = \App\Modules\ModuleGenerator\Models\Module::where('status', 1)->get();

            foreach ($modules as $module) {
                $moduleName = Str::ucfirst($module['code_module']);
                $routesFile = base_path('Modules/' . $moduleName . '/routes.php');

                if (file_exists($routesFile)) {
                    require $routesFile;
                    Log::info('Routes загружены для модуля: ' . $moduleName);
                }
            }
        } catch (\Exception $e) {
            Log::error('Ошибка загрузки routes модулей: ' . $e->getMessage());
        }
    }

    /**
     * Загружает Service Providers динамических модулей
     */
    public static function loadServiceProviders(): void
    {
        if (!self::canLoadModules()) {
            return;
        }

        try {
            $modules = \App\Modules\ModuleGenerator\Models\Module::where('status', 1)->get();

            foreach ($modules as $module) {
                $moduleName = Str::ucfirst($module['code_module']);
                $providerClass = "Modules\\{$moduleName}\\Providers\\{$moduleName}ServiceProvider";

                if (class_exists($providerClass)) {
                    app()->register($providerClass);
                    Log::info('Service Provider зарегистрирован для модуля: ' . $moduleName);
                }
            }
        } catch (\Exception $e) {
            Log::error('Ошибка загрузки Service Providers модулей: ' . $e->getMessage());
        }
    }
}