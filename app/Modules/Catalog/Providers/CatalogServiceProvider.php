<?php

namespace App\Modules\Catalog\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

/**
 * Провайдер сервисов модуля Catalog
 * 
 * Регистрирует маршруты, представления и сервисы модуля.
 */
class CatalogServiceProvider extends ServiceProvider
{
    /**
     * Название модуля
     *
     * @var string
     */
    protected string $moduleName = 'Catalog';

    /**
     * Пространство имен модуля
     *
     * @var string
     */
    protected string $moduleNamespace = 'App\Modules\Catalog';

    /**
     * Регистрация сервисов модуля
     *
     * @return void
     */
    public function register()
    {
        // Регистрация фасада каталога
        $this->app->bind('catalog', function ($app) {
            return new \App\Modules\Catalog\Services\CatalogService();
        });
    }

    /**
     * Загрузка сервисов модуля
     *
     * @return void
     */
    public function boot()
    {
        // Регистрация команд
        $this->registerCommands();

        \Log::info('Catalog module loaded');
    }

    /**
     * Публикация ресурсов модуля
     *
     * @return void
     */
    protected function publishResources()
    {
        // Публикация конфигурации
        $this->publishes([
            module_path($this->moduleName, 'config.php') => config_path('catalog.php'),
        ], 'catalog-config');
    }

    /**
     * Регистрация команд модуля
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                // Здесь можно зарегистрировать команды модуля
            ]);
        }
    }
}