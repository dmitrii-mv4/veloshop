<?php

namespace App\Modules\MediaLib\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use App\Modules\MediaLib\Services\MediaService;

/**
 * Провайдер сервисов медиабиблиотеки
 * Регистрирует команды и задачи планировщика
 */
class MediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MediaService::class);
    }

    public function boot(): void
    {
        // 
    }
}