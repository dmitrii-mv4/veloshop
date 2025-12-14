<?php

namespace App\Modules\MediaLib\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

/**
 * Провайдер для регистрации views путей для модуля редактор страницы
 * 
 * @package App\Modules\MediaLib\Providers
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
            $viewsPath = app_path('Modules/MediaLib/views');
            
            Log::debug('MediaLib ViewsProvider booted callback started', ['path' => $viewsPath]);
            
            if (is_dir($viewsPath))
            {
                $this->loadViewsFrom($viewsPath, 'media');
                Log::info('Views зарегистрированы для системного модуля: media');
            }
            else
            {
                Log::warning('MediaLib views directory not found', ['path' => $viewsPath]);
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