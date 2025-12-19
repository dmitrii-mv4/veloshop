<?php

namespace App\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
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

        // Регистрируем команды в Laravel 12
        $this->app->singleton(InstallKotiksCMSCommand::class, function ($app) {
            return new InstallKotiksCMSCommand();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Регистрируем команду (Laravel 12 способ)
        $this->commands([
            InstallKotiksCMSCommand::class,
        ]);

        // 2. Регистрируем кастомные пути миграций
        $this->loadMigrationsFrom([
            base_path('app/Modules/MediaLib/database/migrations'),
            base_path('app/Modules/Role/database/migrations'),
            base_path('app/Modules/User/database/migrations'),
            base_path('app/Modules/ModuleGenerator/database/migrations'),
        ]);

        // 3. Выводим информацию о сайте с обработкой ошибок
        $this->app->booted(function () {
            try {
                // Проверяем подключение к БД
                DB::connection()->getPdo();
                
                if (Schema::hasTable('settings')) {
                    $settings = \App\Admin\Models\Settings::first();
                    View::share('settings', $settings ? $settings->toArray() : []);
                } else {
                    View::share('settings', []);
                }
            } catch (\Exception $e) {
                // В случае ошибки передаем пустой массив
                View::share('settings', []);
            }
        });
    }
}