<?php

namespace App\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;

/**
 * Единый провайдер для регистрации views всех системных модулей
 * Заменяет отдельные ViewsProvider в каждом модуле
 */
class ViewsProvider extends ServiceProvider
{
    /**
     * Конфигурация системных модулей
     * Формат: 'namespace_view' => 'относительный_путь_к_директории_views'
     */
    private array $systemModules = [
        'admin' => 'app/Admin/views',
        'integrator' => 'app/Modules/Integrator/views',
        'media' => 'app/Modules/MediaLib/views',
        'module_generator' => 'app/Modules/ModuleGenerator/views',
        'page' => 'app/Modules/Page/views',
        'role' => 'app/Modules/Role/views',
        'user' => 'app/Modules/User/views',
    ];

    /**
     * Register any application services.
     * Регистрирует провайдер и планирует загрузку views после инициализации
     */
    public function register(): void
    {
        // Регистрируем загрузку views после полной инициализации приложения
        $this->app->booted(function () {
            $this->loadSystemModulesViews();
            $this->shareActiveModules();
        });
    }

    /**
     * Загрузка views всех системных модулей
     * Проверяет существование директорий перед регистрацией
     */
    private function loadSystemModulesViews(): void
    {
        foreach ($this->systemModules as $namespace => $relativePath) {
            $absolutePath = base_path($relativePath);
            
            if (File::isDirectory($absolutePath)) {
                $this->loadViewsFrom($absolutePath, $namespace);
                
                \Log::debug('Views зарегистрированы', [
                    'module' => $namespace,
                    'path' => $absolutePath,
                ]);
            }
        }
    }

    /**
     * Передача активных динамических модулей в шаблоны
     * Использует try-catch для безопасной работы при отсутствии таблицы модулей
     */
    private function shareActiveModules(): void
    {
        try {
            // Проверяем существование модели Module
            if (!class_exists('App\Modules\ModuleGenerator\Models\Module')) {
                $this->shareEmptyModules();
                return;
            }
            
            // Пытаемся получить активные модули
            $modules = \App\Modules\ModuleGenerator\Models\Module::where('status', 1)->get();
            $this->shareToAllViews('modules', $modules);
            
        } catch (\Exception $e) {
            // В случае ошибки (например, таблицы не существует) передаем пустую коллекцию
            $this->shareEmptyModules();
        }
    }

    /**
     * Безопасное использование view()->share()
     * Проверяет доступность фасада View перед использованием
     */
    private function shareToAllViews(string $key, $value): void
    {
        if (app()->bound('view')) {
            view()->share($key, $value);
        }
    }

    /**
     * Передача пустой коллекции модулей
     */
    private function shareEmptyModules(): void
    {
        $this->shareToAllViews('modules', collect());
    }

    /**
     * Получение информации о зарегистрированных модулях (для отладки)
     */
    public function getRegisteredModulesInfo(): array
    {
        $info = [];
        
        foreach ($this->systemModules as $namespace => $path) {
            $info[] = [
                'namespace' => $namespace,
                'path' => base_path($path),
                'exists' => File::isDirectory(base_path($path)),
            ];
        }
        
        return $info;
    }

    /**
     * Bootstrap any application services.
     * Пустой, так как вся логика в register()
     */
    public function boot(): void
    {
        // Настройка отображения settings
        $this->setupSettingsView();
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