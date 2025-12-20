<?php

namespace App\Modules\User\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

/**
 * Провайдер для регистрации views путей для модуля User
 * 
 * @package App\Modules\User\Providers
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
            $viewsPath = app_path('Modules/User/views');
            
            Log::debug('User ViewsProvider booted callback started', ['path' => $viewsPath]);
            
            if (is_dir($viewsPath))
            {
                // Загружаем представления из папки app/User/views
                $this->loadViewsFrom($viewsPath, 'user');
                Log::info('Views зарегистрированы для системного модуля: user');

                // Подключаем маршруты аутентификации
                $this->loadRoutesFrom(app_path('Modules/User/routes/auth.php'));
            }
            else
            {
                Log::warning('User views directory not found', ['path' => $viewsPath]);
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