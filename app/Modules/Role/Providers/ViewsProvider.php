<?php

namespace App\Modules\Role\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

/**
 * Провайдер для регистрации views путей для модуля Role
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
            $viewsPath = app_path('Modules/Role/views');
            
            Log::debug('Role ViewsProvider booted callback started', ['path' => $viewsPath]);
            
            if (is_dir($viewsPath))
            {
                $this->loadViewsFrom($viewsPath, 'role');
                Log::info('Views зарегистрированы для системного модуля: role');
            }
            else
            {
                Log::warning('Role views directory not found', ['path' => $viewsPath]);
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