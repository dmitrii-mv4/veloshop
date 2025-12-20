<?php

namespace App\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use App\Core\Services\InstallationService;
use App\Core\Console\Commands\InstallKotiksCMSCommand;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Регистрируем PSR-4 для модулей
        $this->app->booting(function () {
            $loader = require base_path('vendor/autoload.php');
            $loader->addPsr4('Modules\\', base_path('Modules'));
        });

        // Регистрируем InstallationService
        $this->app->singleton(InstallationService::class, function ($app) {
            return new InstallationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Регистрируем команды Artisan
        $this->commands([
            InstallKotiksCMSCommand::class,
        ]);
        
        // Загрузка системных миграций через InstallationService
        $this->loadSystemMigrations();
        
        // Настройка отображения settings
        $this->setupSettingsView();
    }

    /**
     * Загрузка системных миграций
     */
    protected function loadSystemMigrations(): void
    {
        $this->app->booted(function () {
            $installationService = $this->app->make(InstallationService::class);
            $migrations = $installationService->getValidMigrationPaths();
            
            if (!empty($migrations)) {
                foreach ($migrations as $path) {
                    $this->loadMigrationsFrom($path);
                }
            }
        });
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