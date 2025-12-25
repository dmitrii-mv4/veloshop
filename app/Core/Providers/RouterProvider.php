<?php

namespace App\Core\Providers;

use Illuminate\Support\ServiceProvider;
use App\Core\Services\RouterLoaderService;

/**
 * Провайдер для регистрации и загрузки динамических маршрутов
 * Обеспечивает централизованное управление роутингом всей системы
 */
class RouterProvider extends ServiceProvider
{
    /**
     * Регистрирует сервисы в контейнере
     */
    public function register(): void
    {
        // Регистрируем RouterLoaderService как синглтон
        $this->app->singleton(RouterLoaderService::class, function ($app) {
            return new RouterLoaderService();
        });
        
        // Также регистрируем фасад для удобства доступа
        $this->app->alias(RouterLoaderService::class, 'router-loader');
    }

    /**
     * Загружает динамические маршруты после регистрации всех сервисов
     */
    public function boot(): void
    {
        // Получаем экземпляр сервиса
        $routerLoader = $this->app->make(RouterLoaderService::class);
        
        // Загружаем все маршруты
        $routerLoader->loadAllRoutes();
        
        // Для отладки можно добавить маршрут для проверки загруженных маршрутов
        if (config('app.debug')) {
            $this->addDebugRoute($routerLoader);
        }
    }

    /**
     * Добавляет отладочный маршрут для проверки загруженных маршрутов
     * Доступен только в режиме отладки
     */
    protected function addDebugRoute(RouterLoaderService $routerLoader): void
    {
        \Illuminate\Support\Facades\Route::get('/debug/routes-info', function () use ($routerLoader) {
            $info = $routerLoader->getLoadedRoutesInfo();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Информация о загруженных маршрутах',
                'data' => $info,
                'timestamp' => now()->toDateTimeString()
            ]);
        })->middleware(['web'])->name('debug.routes-info');
    }
}