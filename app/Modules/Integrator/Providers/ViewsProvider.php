<?php

namespace App\Modules\Integrator\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

/**
 * Провайдер для регистрации views путей для модуля Integrator
 * 
 * @package App\Modules\Integrator\Providers
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
            $viewsPath = app_path('Modules/Integrator/views');
            
            Log::debug('Integrator ViewsProvider booted callback started', ['path' => $viewsPath]);
            
            if (is_dir($viewsPath))
            {
                // Загружаем представления из папки app/User/views
                $this->loadViewsFrom($viewsPath, 'integrator');
                Log::info('Views зарегистрированы для системного модуля: user');
            }
            else
            {
                Log::warning('Integrator views directory not found', ['path' => $viewsPath]);
            }

            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
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