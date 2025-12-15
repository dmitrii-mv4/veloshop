<?php

namespace App\Modules\ModuleGenerator\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

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
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}