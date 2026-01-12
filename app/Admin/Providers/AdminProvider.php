<?php

namespace App\Admin\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use App\Admin\Services\LanguageService;
use App\Admin\Services\AdminViewsService;
use App\Core\Services\ModuleDiscoveryService;
use App\Admin\Models\Settings;

class AdminProvider extends ServiceProvider
{
    /**
     * Счетчик для генерации уникальных ID collapse
     */
    protected static $collapseCounter = 0;
    
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
        
        // Регистрация AdminViewsService как синглтона
        $this->app->singleton(AdminViewsService::class, function ($app) {
            return new AdminViewsService();
        });
        
        // Регистрация псевдонима для удобства
        $this->app->alias(LanguageService::class, 'admin.language');
        $this->app->alias(AdminViewsService::class, 'admin.views');
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

        // Регистрируем представления админ-панели
        $this->registerAdminViews();
        
        // Получаем активные модули для общего доступа в представлениях
        $this->shareActiveModules();
        
        // Получаем настройки сайта для общего доступа в представлениях
        $this->shareSettings();
    }

    /**
     * Регистрирует представления админ-панели
     * 
     * @return void
     */
    protected function registerAdminViews(): void
    {
        try {
            $adminViewsService = $this->app->make(AdminViewsService::class);
            $adminViewsService->registerAdminViews();
            
            Log::info('[AdminProvider] Представления админ-панели успешно зарегистрированы');
        } catch (\Exception $e) {
            Log::error('[AdminProvider] Ошибка регистрации представлений админ-панели', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Делится информацией об активных модулях с представлениями
     * 
     * @return void
     */
    protected function shareActiveModules(): void
    {
        try {
            $moduleService = app(ModuleDiscoveryService::class);
            $allModules = $moduleService->getActiveModules();
            
            // Делимся информацией об активных модулях с представлениями
            view()->share('modules', $allModules);
            
            Log::info('[AdminProvider] Информация об активных модулей загружена', [
                'modules_count' => count($allModules)
            ]);
        } catch (\Exception $e) {
            Log::error('[AdminProvider] Ошибка получения активных модулей', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Делится настройками сайта с представлениями
     * 
     * @return void
     */
    protected function shareSettings(): void
    {
        try {
            // Получаем настройки сайта из базы данных
            $settings = Settings::first();
            
            if ($settings) {
                // Преобразуем объект модели в массив для удобного доступа в шаблонах
                $settingsArray = $settings->toArray();
                
                // Делимся настройками с представлениями
                view()->share('settings', $settingsArray);
                
                Log::info('[AdminProvider] Настройки сайта загружены и переданы в представления', [
                    'site_name' => $settingsArray['name_site'] ?? 'Не указано',
                    'site_url' => $settingsArray['url_site'] ?? '/'
                ]);
            } else {
                // Если настройки не найдены, создаем дефолтные
                $defaultSettings = [
                    'name_site' => 'Kotiks CMS',
                    'url_site' => '/',
                    'description_site' => 'Система управления контентом',
                ];
                
                view()->share('settings', $defaultSettings);
                
                Log::warning('[AdminProvider] Настройки сайта не найдены, используются значения по умолчанию');
            }
        } catch (\Exception $e) {
            // В случае ошибки (например, таблица не существует) используем дефолтные настройки
            $defaultSettings = [
                'name_site' => 'Kotiks CMS',
                'url_site' => '/',
                'description_site' => 'Система управления контентом',
            ];
            
            view()->share('settings', $defaultSettings);
            
            Log::error('[AdminProvider] Ошибка загрузки настроек сайта', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}