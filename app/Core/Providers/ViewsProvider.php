<?php

namespace App\Core\Providers;

use Illuminate\Support\ServiceProvider;
use App\Core\Services\ModuleDiscoveryService;
use App\Core\Services\ViewsService;
use Illuminate\Support\Facades\Log;

/**
 * Провайдер для регистрации сервиса видов в контейнере приложения
 * 
 * Обеспечивает инициализацию ViewsService на ранней стадии загрузки приложения
 * для корректной регистрации пространств имен видов модулей.
 */
class ViewsProvider extends ServiceProvider
{
    /**
     * Регистрирует сервисы в контейнере приложения
     * 
     * @return void
     */
    public function register(): void
    {
        try {
            // Регистрируем синглтон для ViewsService
            $this->app->singleton(ViewsService::class, function ($app) {
                $moduleDiscovery = $app->make(ModuleDiscoveryService::class);
                return new ViewsService($moduleDiscovery);
            });
            
            Log::info('ViewsService зарегистрирован в контейнере приложения');
            
        } catch (\Exception $e) {
            Log::error('Ошибка при регистрации ViewsService', [
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Загружает сервисы после регистрации всех провайдеров
     * 
     * @return void
     */
    public function boot(): void
    {
        try {
            // Получаем экземпляр ViewsService
            $viewsService = $this->app->make(ViewsService::class);
            
            // Регистрируем все виды
            $viewsService->registerAllViews();
            
            Log::info('ViewsProvider загружен и виды зарегистрированы');
            
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке ViewsProvider', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }
}