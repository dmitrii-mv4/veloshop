<?php

namespace App\Admin\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use App\Modules\ModuleGenerator\Models\Module;

/**
 * Провайдер для регистрации views путей для модуля Admin
 * 
 * @package App\Admin\Providers
 */

class ViewsProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->booted(function ()
        {
            $viewsPath = app_path('Admin/views');
            
            Log::debug('Admin ViewsProvider booted callback started', ['path' => $viewsPath]);
            
            if (is_dir($viewsPath))
            {
                // Загружаем представления
                $this->loadViewsFrom($viewsPath, 'admin');
                Log::info('Views зарегистрированы для системного модуля: admin');
            }
            else
            {
                Log::warning('Admin views directory not found', ['path' => $viewsPath]);
            }
        });

        $this->shareModules();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    protected function shareModules(): void
    {
        try {
            // Получаем только активные модули (status = 1)
            $modules = Module::where('status', 1)->get();
            
            // Передаем модули в шаблон ОДИН РАЗ, вне цикла
            view()->share('modules', $modules);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке модулей для шаблона', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // В случае ошибки передаем пустую коллекцию
            view()->share('modules', collect()); // Используем collect() вместо строки
        }
    }
}