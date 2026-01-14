<?php

namespace App\Modules\ExchangeOneCVeloshop\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use App\Modules\ExchangeOneCVeloshop\Services\ConnectionCheckService;

/**
 * ExchangeOneCVeloshop сервис-провайдер модуля
 * 
 * Регистрация провайдера происходит автоматически через серевис ModuleProviderService и провайдер ModulesProvider
 * 
 */
class ExchangeOneCProvider extends ServiceProvider
{
    /**
     * Регистрирует сервисы модуля в контейнере приложения
     * 
     * @return void
     */
    public function register(): void
    {
        Log::info('ExchangeOneCProvider: Регистрация сервисов модуля обмена с 1С');

        // Регистрация ConnectionCheckService как синглтона
        $this->app->singleton('exchange1c.connection.check', function ($app) {
            Log::debug('ExchangeOneCProvider: Регистрация ConnectionCheckService');
            return new ConnectionCheckService();
        });

        // Регистрация фасада для удобного доступа к сервису
        $this->app->alias('exchange1c.connection.check', ConnectionCheckService::class);

        // Регистрация DataParserService как синглтона
        $this->app->singleton('exchange1c.data.parser', function ($app) {
            Log::debug('ExchangeOneCProvider: Регистрация DataParserService как синглтона');
            return new DataParserService();
        });

        // Регистрация фасада для удобного доступа к DataParserService
        $this->app->alias('exchange1c.data.parser', DataParserService::class);

        Log::info('ExchangeOneCProvider: Регистрация сервисов модуля обмена с 1С завершена', [
            'registered_services' => [
                'exchange1c.connection.check' => ConnectionCheckService::class,
                'exchange1c.data.parser' => DataParserService::class
            ]
        ]);

        Log::info('ExchangeOneCProvider: Регистрация сервисов завершена');
    }

    /**
     * Загружает сервисы модуля после регистрации всех провайдеров
     * 
     * @return void
     */
    public function boot(): void
    {
        //
    }
}