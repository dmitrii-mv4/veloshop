<?php

namespace App\Core\Providers;

use Illuminate\Support\ServiceProvider;
use App\Core\Services\RouterLoaderService;
use Illuminate\Support\Facades\Log;

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
        Log::info('[RouterProvider] Регистрация RouterLoaderService');
        
        // Регистрируем RouterLoaderService как синглтон
        $this->app->singleton(RouterLoaderService::class, function ($app) {
            return new RouterLoaderService();
        });
        
        // Также регистрируем фасад для удобства доступа
        $this->app->alias(RouterLoaderService::class, 'router-loader');
        
        Log::info('[RouterProvider] RouterLoaderService зарегистрирован');
    }

    /**
     * Загружает динамические маршруты после регистрации всех сервисов
     */
    public function boot(): void
    {
        Log::info('[RouterProvider] Начало загрузки маршрутов в boot');
        
        // Получаем экземпляр сервиса
        $routerLoader = $this->app->make(RouterLoaderService::class);
        
        // Загружаем все маршруты (WEB и API)
        $routerLoader->loadAllRoutes();
        
        // Для отладки можно добавить маршрут для проверки загруженных маршрутов
        if (config('app.debug')) {
            $this->addDebugRoutes($routerLoader);
        }
        
        Log::info('[RouterProvider] Загрузка маршрутов завершена');
    }

    /**
     * Добавляет отладочные маршруты для проверки загруженных маршрутов
     * Доступны только в режиме отладки
     */
    protected function addDebugRoutes(RouterLoaderService $routerLoader): void
    {
        try {
            // WEB маршрут для информации о маршрутах
            \Illuminate\Support\Facades\Route::get('/debug/routes-info', function () use ($routerLoader) {
                $info = $routerLoader->getLoadedRoutesInfo();
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Информация о загруженных маршрутах',
                    'data' => $info,
                    'timestamp' => now()->toDateTimeString()
                ]);
            })->middleware(['web'])->name('debug.routes-info');
            
            // API маршрут для информации о маршрутах
            \Illuminate\Support\Facades\Route::get('/api/debug/routes-info', function () use ($routerLoader) {
                $info = $routerLoader->getLoadedRoutesInfo();
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'API информация о загруженных маршрутах',
                    'data' => $info,
                    'timestamp' => now()->toDateTimeString()
                ]);
            })->middleware(['api'])->name('api.debug.routes-info');
            
            Log::debug('[RouterProvider] Отладочные маршруты добавлены');
        } catch (\Exception $e) {
            Log::error('[RouterProvider] Ошибка при добавлении отладочных маршрутов', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Получает информацию о зарегистрированных маршрутах (для консольных команд)
     */
    public function getRoutesInfo(): array
    {
        try {
            $routerLoader = $this->app->make(RouterLoaderService::class);
            return $routerLoader->getLoadedRoutesInfo();
        } catch (\Exception $e) {
            Log::error('[RouterProvider] Ошибка при получении информации о маршрутах', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}