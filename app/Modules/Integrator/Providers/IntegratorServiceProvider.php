<?php

namespace App\Modules\Integrator\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Integrator\Services\DriverDiscoveryService;
use Illuminate\Support\Facades\Blade;

class IntegratorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Регистрация сервиса обнаружения драйверов
        $this->app->singleton(DriverDiscoveryService::class, function ($app) {
            return new DriverDiscoveryService();
        });

        // Алиас для удобства использования
        $this->app->alias(DriverDiscoveryService::class, 'integrator.drivers');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Загрузка маршрутов, миграций, представлений...
        
        // Публикация конфигураций, если необходимо
        // $this->publishes([
        //     __DIR__ . '/../Config/integrator.php' => config_path('integrator.php'),
        // ], 'integrator-config');
        
        // Загрузка миграций
        //$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        
        // Загрузка переводов
        //$this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'integrator');
        
        // Регистрация команд Artisan
        // if ($this->app->runningInConsole()) {
        //     $this->commands([
        //         // Команды модуля интеграции
        //     ]);
        // }
    }
}