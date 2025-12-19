<?php

namespace App\Modules\ModuleGenerator\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Провайдер для регистрации views путей для модуля ModuleGenerator
 * 
 * @package App\Modules\ModuleGenerator\Providers
 */

class ViewsProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ОТКЛЮЧАЕМ загрузку dynamic modules во время package:discover
        // Она будет загружена позже через middleware или после установки
        
        $this->app->booted(function () {
            // Регистрируем только системные views модуля ModuleGenerator
            $this->registerModuleGeneratorViews();
            
            // НЕ регистрируем динамические модули здесь!
            // Они будут зарегистрированы после установки через отдельный механизм
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Регистрация views системного модуля ModuleGenerator
     */
    protected function registerModuleGeneratorViews(): void
    {
        $viewsPath = app_path('Modules/ModuleGenerator/views');
        
        Log::debug('ModuleGenerator ViewsProvider booted callback started', ['path' => $viewsPath]);
        
        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'module_generator');
            Log::info('Views зарегистрированы для системного модуля: module_generator');
        } else {
            Log::warning('ModuleGenerator views directory not found', ['path' => $viewsPath]);
        }
    }

    /**
     * Регистрация views динамических модулей
     * ВЫЗЫВАЕТСЯ ТОЛЬКО ПОСЛЕ УСТАНОВКИ!
     */
    public static function registerDynamicModulesViews(): void
    {
        try {
            // Проверяем существование таблицы modules
            if (!Schema::hasTable('modules')) {
                Log::warning('Таблица modules не существует, пропускаем регистрацию views для динамических модулей');
                return;
            }

            // Проверяем существование столбца status
            $columns = Schema::getColumnListing('modules');
            $statusColumnExists = in_array('status', $columns);
            
            $query = \App\Modules\ModuleGenerator\Models\Module::query();
            
            if ($statusColumnExists) {
                $query->where('status', 1);
            }
            
            $modules = $query->get();

            if ($modules->isEmpty()) {
                Log::info('Нет активных модулей для регистрации views');
                return;
            }

            foreach ($modules as $module) {
                $moduleName = \Illuminate\Support\Str::ucfirst($module['code_module']);
                $viewsPathModule = base_path('Modules/' . $moduleName . '/views');

                if (is_dir($viewsPathModule)) {
                    app()->loadViewsFrom($viewsPathModule, $module['code_module']);
                    Log::info('Views зарегистрированы для модуля: ' . $moduleName);
                } else {
                    Log::debug('Views directory not found for module: ' . $moduleName, [
                        'path' => $viewsPathModule,
                        'module' => $module->toArray()
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Ошибка при регистрации views для динамических модулей: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}