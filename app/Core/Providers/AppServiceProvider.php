<?php

namespace App\Core\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Core\Services\Database\DatabaseColumnTypeService;
use App\Admin\Models\Settings;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use App\Modules\ModuleGenerator\Models\Module;
use Illuminate\Support\Facades\File;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Для интеграций модулей
        $this->app->singleton(MigrationService::class, function ($app) {
            return new MigrationService();
        });

        // Регистрируем PSR-4 для модулей
        $this->app->booting(function () {
            $loader = require base_path('vendor/autoload.php');
            $loader->addPsr4('Modules\\', base_path('Modules'));
        });

        // Регистрация сервиса DatabaseColumnTypeService
        $this->app->singleton(DatabaseColumnTypeService::class, function ($app) {
            return new DatabaseColumnTypeService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Регистрируем кастомные пути миграций для команды kotiks::install
        $this->loadMigrationsFrom([
            base_path('app/Modules/MediaLib/database/migrations'),
            base_path('app/Modules/Role/database/migrations'),
            base_path('app/Modules/User/database/migrations'),
            base_path('app/Modules/ModuleGenerator/database/migrations'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Core\Console\Commands\InstallKotiksCMSCommand::class,
            ]);
        }

        // Выводим информацию о сайте с обработкой ошибок
        $this->app->booted(function ()
        {
            try {
                // Проверяем подключение к БД
                DB::connection()->getPdo();
                
                if (Schema::hasTable('settings')) {
                    $settings = Settings::first();
                    View::share('settings', $settings ? $settings->toArray() : []);
                } else {
                    View::share('settings', []);
                }
            } catch (\Exception $e) {
                // В случае ошибки (например, таблицы не существует) передаем пустой массив
                View::share('settings', []);
            }
        });
    }
}