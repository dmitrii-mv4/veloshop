<?php

namespace App\Modules\ModuleGenerator\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use App\Modules\ModuleGenerator\Models\Module;
use Illuminate\Support\Str;

/**
 * Провайдер для регистрации views путей для модуля ModuleGenerator
 * 
 * @package App\Modules\ModuleGenerator\Providers
 */

class ViewsProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->booted(function ()
        {
            $viewsPath = app_path('Modules/ModuleGenerator/views');
            
            Log::debug('ModuleGenerator ViewsProvider booted callback started', ['path' => $viewsPath]);
            
            if (is_dir($viewsPath))
            {
                $this->loadViewsFrom($viewsPath, 'module_generator');
                Log::info('Views зарегистрированы для системного модуля: module_generator');
            }
            else
            {
                Log::warning('Info block views directory not found', ['path' => $viewsPath]);
            }

            // Регистрируем views для динамических модулей
            $this->registerViewsModules();
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Регистрация views динамических модулей
     */
    protected function registerViewsModules(): void
    {
        $modules = Module::where('status', 1)->get();

        foreach ($modules as $module)
        {
            $moduleName = Str::ucfirst($module['code_module']);
            $viewsPathModule = base_path('Modules/' . $moduleName . '/views');

            if (is_dir($viewsPathModule))
            {
                $this->loadViewsFrom($viewsPathModule, $module['code_module']);
                Log::info('Views зарегистрированы для модуля:' . $moduleName);
            }
            else
            {
                Log::warning('Info block views directory not found', ['path' => $viewsPathModule]);
            }
        }
    }
}