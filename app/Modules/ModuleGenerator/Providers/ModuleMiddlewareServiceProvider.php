<?php

namespace App\Modules\ModuleGenerator\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use App\Modules\ModuleGenerator\Models\Module;
use Illuminate\Support\Facades\Log;

/**
 * Провайдер который регистрирует Middleware в модулях
 * 
 */

class ModuleMiddlewareServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // $this->app->booting(function () {
        //     $loader = require base_path('vendor/autoload.php');
        //     $loader->addPsr4('Modules\\', base_path('Modules'));
        // });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Регистрируем middleware модулей после полной инициализации приложения
        $this->registerModuleMiddlewares();
    }

    /**
     * Register module middlewares
     */
    private function registerModuleMiddlewares(): void
    {
        try {
            $moduleAliases = $this->loadModuleMiddlewares();
            
            // Регистрируем каждый middleware через router
            foreach ($moduleAliases as $alias => $className) {
                app('router')->aliasMiddleware($alias, $className);
            }

            Log::info('Module middlewares registered: ' . count($moduleAliases));
            
        } catch (\Exception $e) {
            Log::error('Error registering module middlewares: ' . $e->getMessage());
        }
    }

    /**
     * Load module middlewares dynamically
     */
    private function loadModuleMiddlewares(): array
    {
        $moduleMiddlewareAliases = [];

        try {
            $allModuleData = Module::get();

            if (empty($allModuleData)) {
                Log::info('No module data found in database');
                return $moduleMiddlewareAliases;
            }

            foreach ($allModuleData as $key => $module)
            {
                $moduleCode = $module->code_module;
                $studlyName = Str::studly($moduleCode);
                    
                $middlewareClasses = [
                    'index' => "Modules\\{$studlyName}\\Middleware\\{$studlyName}IndexMiddleware",
                    'create' => "Modules\\{$studlyName}\\Middleware\\{$studlyName}CreateMiddleware",
                    'update' => "Modules\\{$studlyName}\\Middleware\\{$studlyName}UpdateMiddleware",
                    'delete' => "Modules\\{$studlyName}\\Middleware\\{$studlyName}DeleteMiddleware",
                ];

                foreach ($middlewareClasses as $action => $className)
                {   
                    if (class_exists($className))
                    {
                        $moduleMiddlewareAliases[$moduleCode . '_' . $action] = $className;
                        Log::info("Middleware registered: {$moduleCode}_{$action}");
                    } else {
                        Log::warning("Middleware class not found: {$className}");
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error('Error loading module middlewares: ' . $e->getMessage());
        }

        return $moduleMiddlewareAliases;
    }
}