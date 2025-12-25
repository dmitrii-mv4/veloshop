<?php

namespace App\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use App\Core\Services\ViewsService;

/**
 * Провайдер для регистрации views всех системных и динамических модулей
 * Использует ViewsService для управления бизнес-логикой
 */
class ViewsProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * Регистрирует провайдер и загружает системные модули views
     */
    public function register(): void
    {
        Log::info('[ViewsProvider] Начало регистрации views провайдера');
        
        // Регистрируем сервис ViewsService
        $this->app->singleton(ViewsService::class, function ($app) {
            return new ViewsService();
        });
        
        // Регистрируем системные модули views
        $this->registerSystemModulesViews();
        
        Log::info('[ViewsProvider] Views провайдер зарегистрирован');
    }

    /**
     * Bootstrap any application services.
     * Загружаем динамические модули и настраиваем views
     */
    public function boot(): void
    {
        Log::info('[ViewsProvider] Запуск boot метода');
        
        // Загружаем views динамических модулей
        $this->registerDynamicModulesViews();
        
        // Передаем активные модули в шаблоны
        $this->shareActiveModules();
        
        // Настройка отображения settings
        $this->setupSettingsView();
        
        // Добавляем отладочный маршрут для проверки зарегистрированных views
        if (config('app.debug')) {
            $this->addDebugRoute();
        }
        
        Log::info('[ViewsProvider] Boot метод завершен');
    }

    /**
     * Регистрация views системных модулей
     */
    private function registerSystemModulesViews(): void
    {
        try {
            /** @var ViewsService $viewsService */
            $viewsService = $this->app->make(ViewsService::class);
            $modules = $viewsService->loadSystemModulesViews();
            
            foreach ($modules as $namespace => $moduleInfo) {
                if ($moduleInfo['success'] ?? false) {
                    $this->loadViewsFrom($moduleInfo['path'], $namespace);
                    Log::info('[ViewsProvider] Views системного модуля зарегистрированы', [
                        'module' => $namespace,
                        'path' => $moduleInfo['path'],
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('[ViewsProvider] Ошибка при регистрации системных модулей views', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Регистрация views динамических модулей
     */
    private function registerDynamicModulesViews(): void
    {
        try {
            /** @var ViewsService $viewsService */
            $viewsService = $this->app->make(ViewsService::class);
            $modules = $viewsService->loadDynamicModulesViews();
            
            foreach ($modules as $moduleInfo) {
                if ($moduleInfo['success'] ?? false) {
                    // Проверяем, не зарегистрирован ли уже этот namespace
                    $viewFinder = $this->app->make('view')->getFinder();
                    $hints = $viewFinder->getHints();
                    
                    if (!isset($hints[$moduleInfo['namespace']])) {
                        $this->loadViewsFrom($moduleInfo['path'], $moduleInfo['namespace']);
                        
                        Log::info('[ViewsProvider] Views динамического модуля зарегистрированы', [
                            'module' => $moduleInfo['module_name'],
                            'namespace' => $moduleInfo['namespace'],
                            'path' => $moduleInfo['path'],
                            'id' => $moduleInfo['id']
                        ]);
                    } else {
                        Log::debug('[ViewsProvider] Namespace уже зарегистрирован, пропускаем', [
                            'module' => $moduleInfo['module_name'],
                            'namespace' => $moduleInfo['namespace']
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('[ViewsProvider] Ошибка при регистрации динамических модулей views', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Передача активных модулей в шаблоны
     */
    private function shareActiveModules(): void
    {
        try {
            /** @var ViewsService $viewsService */
            $viewsService = $this->app->make(ViewsService::class);
            $modules = $viewsService->getActiveModules();
            
            Log::info('[ViewsProvider] Передача активных модулей в шаблоны, количество: ' . $modules->count());
            
            $this->shareToAllViews('modules', $modules);
            
        } catch (\Exception $e) {
            Log::error('[ViewsProvider] Ошибка при передаче модулей в шаблоны', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Безопасное использование view()->share()
     * Проверяет доступность фасада View перед использованием
     */
    private function shareToAllViews(string $key, $value): void
    {
        if (app()->bound('view')) {
            try {
                view()->share($key, $value);
                Log::debug('[ViewsProvider] Данные успешно переданы в шаблоны', ['key' => $key]);
            } catch (\Exception $e) {
                Log::error('[ViewsProvider] Ошибка при передаче данных в шаблоны', [
                    'key' => $key,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            Log::warning('[ViewsProvider] Фасад View не доступен для передачи данных');
        }
    }

    /**
     * Настройка отображения настроек через View composer
     */
    private function setupSettingsView(): void
    {
        try {
            /** @var ViewsService $viewsService */
            $viewsService = $this->app->make(ViewsService::class);
            $viewsService->setupSettingsView();
            
            Log::debug('[ViewsProvider] View composer для settings установлен через сервис');
        } catch (\Exception $e) {
            Log::error('[ViewsProvider] Ошибка при установке View composer для settings', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Добавляет отладочный маршрут для проверки зарегистрированных views
     */
    private function addDebugRoute(): void
    {
        try {
            if (app()->bound('router')) {
                \Illuminate\Support\Facades\Route::get('/debug/views-info', function () {
                    /** @var ViewsService $viewsService */
                    $viewsService = app()->make(ViewsService::class);
                    $info = $viewsService->getRegisteredModulesInfo();
                    
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Информация о зарегистрированных views',
                        'data' => $info,
                        'timestamp' => now()->toDateTimeString()
                    ]);
                })->middleware(['web'])->name('debug.views-info');
                
                Log::debug('[ViewsProvider] Отладочный маршрут /debug/views-info добавлен');
            }
        } catch (\Exception $e) {
            Log::error('[ViewsProvider] Ошибка при добавлении отладочного маршрута', [
                'error' => $e->getMessage()
            ]);
        }
    }
}