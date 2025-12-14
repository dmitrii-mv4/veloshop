<?php

namespace App\Modules\Page\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

/**
 * Провайдер для регистрации views путей для модуля редактор страницы
 * 
 * @package App\Modules\Page\Providers
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
            $viewsPath = app_path('Modules/Page/views');
            
            Log::debug('Page ViewsProvider booted callback started', ['path' => $viewsPath]);
            
            if (is_dir($viewsPath))
            {
                $this->loadViewsFrom($viewsPath, 'page');
                Log::info('Views зарегистрированы для системного модуля: page');
            }
            else
            {
                Log::warning('Page views directory not found', ['path' => $viewsPath]);
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