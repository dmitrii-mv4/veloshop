<?php

namespace App\Core\Providers;

use Illuminate\Support\ServiceProvider;
use App\Core\Services\RouterLoaderService;

/**
    * Роутер маршрутизации всего приложения
*/
class RouterProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Регистрируем сервис загрузки маршрутов
        $this->app->singleton(RouterLoaderService::class, function ($app) {
            return new RouterLoaderService();
        });

        // Регистрируем сервис для работы с модулями
        $this->app->singleton('module.routes', function ($app) {
            return new class {
                private array $loadedModules = [];

                public function isLoaded(string $module): bool
                {
                    return in_array($module, $this->loadedModules);
                }

                public function markAsLoaded(string $module): void
                {
                    $this->loadedModules[] = $module;
                }

                public function getLoadedModules(): array
                {
                    return $this->loadedModules;
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $routerLoader = $this->app->make(RouterLoaderService::class);
        $routerLoader->loadAllRoutes();
    }
}