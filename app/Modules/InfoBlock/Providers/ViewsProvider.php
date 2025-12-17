<?php

namespace App\Modules\InfoBlock\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

/**
 * Провайдер для регистрации views путей для модуля Инфоблок
 * 
 * @package App\Modules\InfoBlock\Providers
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
            $viewsPath = app_path('Modules/InfoBlock/views');
            
            Log::debug('InfoBlock ViewsProvider booted callback started', ['path' => $viewsPath]);
            
            if (is_dir($viewsPath))
            {
                $this->loadViewsFrom($viewsPath, 'info_block');
                Log::info('Views зарегистрированы для системного модуля: info_block');
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