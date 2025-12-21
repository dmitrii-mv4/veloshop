<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use App\Core\Services\ModuleLoaderService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Регистрируем PSR-4 для модулей
        $this->app->booting(function () {
            $loader = require base_path('vendor/autoload.php');
            $loader->addPsr4('Modules\\', base_path('Modules'));
            $loader->addPsr4('App\\Modules\\', app_path('Modules'));
        });

        // Регистрируем ModuleLoaderService как синглтон
        $this->app->singleton(ModuleLoaderService::class);
        
        // Регистрируем команды Artisan только после полной загрузки
        $this->registerCommands();
    }

    /**
     * Регистрация команд Artisan
     */
    protected function registerCommands(): void
    {
        $this->app->booted(function () {
            // Команда установки Kotiks CMS
            if (class_exists(\App\Core\Console\Commands\InstallKotiksCMSCommand::class)) {
                $this->commands([
                    \App\Core\Console\Commands\InstallKotiksCMSCommand::class,
                ]);
                Log::debug('Команда InstallKotiksCMSCommand зарегистрирована');
            } else {
                Log::error('Класс InstallKotiksCMSCommand не найден');
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Загружаем модули при запуске приложения
        $this->loadModulesOnBoot();
        
        // Настройка отображения settings
        $this->setupSettingsView();
    }

    /**
     * Загружает модули при запуске приложения
     */
    protected function loadModulesOnBoot(): void
    {
        $this->app->booted(function () {
            try {
                // Проверяем подключение к базе данных
                DB::connection()->getPdo();
                
                // Проверяем существование таблицы миграций
                if (Schema::hasTable('migrations')) {
                    $moduleLoader = $this->app->make(ModuleLoaderService::class);
                    $moduleLoader->loadAllModules();
                }
                
            } catch (\Exception $e) {
                Log::info('База данных не готова, модули не загружены: ' . $e->getMessage());
            }
        });
    }

    /**
     * Настройка отображения настроек
     */
    protected function setupSettingsView(): void
    {
        View::composer('*', function ($view) {
            try {
                if (Schema::hasTable('settings')) {
                    $settings = \App\Admin\Models\Settings::first();
                    $view->with('settings', $settings ? $settings->toArray() : []);
                } else {
                    $view->with('settings', []);
                }
            } catch (\Exception $e) {
                $view->with('settings', []);
            }
        });
    }
}