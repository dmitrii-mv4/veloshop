<?php

namespace App\Admin\Providers;

use Illuminate\Support\ServiceProvider;
use App\Admin\Services\LanguageService;

class AdminProvider extends ServiceProvider
{
    /**
     * Регистрация сервисов
     * @return void
     */
    public function register(): void
    {
        // Регистрация LanguageService как синглтона
        $this->app->singleton(LanguageService::class, function ($app) {
            return new LanguageService();
        });
        
        // Регистрация псевдонима для удобства
        $this->app->alias(LanguageService::class, 'admin.language');
    }
    
    /**
     * Загрузка сервисов
     * @return void
     */
    public function boot(): void
    {
        // Регистрируем хелпер
        require_once __DIR__ . '/../Helpers/language_helper.php';
        
        // Middleware для установки языка
        $this->app['router']->pushMiddlewareToGroup('web', \App\Admin\Middleware\SetAdminLocale::class);
    }
}