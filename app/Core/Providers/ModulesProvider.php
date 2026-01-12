<?php

namespace App\Core\Providers;

use App\Core\Services\ModuleDiscoveryService;
use Illuminate\Support\ServiceProvider;

/**
 * Провайдер модулей системы
 * 
 * Регистрирует сервисы, связанные с модульной системой,
 * и выполняет начальную инициализацию модулей
 */
class ModulesProvider extends ServiceProvider
{
    /**
     * Регистрирует сервисы в контейнере приложения
     * 
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(ModuleDiscoveryService::class, function ($app) {
            return new ModuleDiscoveryService();
        });
    }

    /**
     * Загружает сервисы после регистрации всех провайдеров
     * 
     * @return void
     */
    public function boot(): void
    {
        //
    }
}