<?php

namespace App\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use App\Core\Services\ModuleDiscoveryService;
use App\Core\Services\ModuleProviderService;

/**
 * Провайдер модулей системы
 * 
 * Регистрирует сервисы, связанные с модульной системой,
 * и выполняет автоматическую регистрацию провайдеров активных модулей
 * 
 * Порядок работы:
 * 1. Регистрирует ModuleDiscoveryService для обнаружения модулей
 * 2. Регистрирует ModuleProviderService для управления провайдерами модулей
 * 3. Автоматически регистрирует провайдеры всех активных модулей
 */
class ModulesProvider extends ServiceProvider
{
    /**
     * Зарегистрированные провайдеры модулей
     * 
     * @var array
     */
    protected array $registeredModuleProviders = [];

    /**
     * Регистрирует сервисы в контейнере приложения
     * 
     * @return void
     */
    public function register(): void
    {
        Log::info('ModulesProvider: Начало регистрации сервисов модульной системы');

        // Регистрация ModuleDiscoveryService
        $this->app->singleton(ModuleDiscoveryService::class, function ($app) {
            Log::debug('ModulesProvider: Регистрация ModuleDiscoveryService');
            return new ModuleDiscoveryService();
        });

        // Регистрация ModuleProviderService
        $this->app->singleton(ModuleProviderService::class, function ($app) {
            Log::debug('ModulesProvider: Регистрация ModuleProviderService');
            return new ModuleProviderService(
                $app->make(ModuleDiscoveryService::class),
                $app
            );
        });

        // Автоматическая регистрация провайдеров модулей
        $this->registerModuleProviders();

        Log::info('ModulesProvider: Регистрация сервисов модульной системы завершена');
    }

    /**
     * Загружает сервисы после регистрации всех провайдеров
     * 
     * @return void
     */
    public function boot(): void
    {
        Log::info('ModulesProvider: Загрузка модульной системы');

        // Получаем статистику зарегистрированных провайдеров
        $providerService = $this->app->make(ModuleProviderService::class);
        $stats = $providerService->getRegistrationStats();

        Log::info('ModulesProvider: Статистика зарегистрированных провайдеров', [
            'total_registered_providers' => $stats['total_registered'] ?? 0,
            'modules_with_providers' => $stats['modules_with_providers'] ?? 0,
            'registered_providers_count' => count($this->registeredModuleProviders)
        ]);
    }

    /**
     * Регистрирует провайдеры активных модулей
     * 
     * @return void
     */
    protected function registerModuleProviders(): void
    {
        try {
            Log::info('ModulesProvider: Начало автоматической регистрации провайдеров модулей');

            // Получаем ModuleProviderService
            $providerService = $this->app->make(ModuleProviderService::class);
            
            // Регистрируем все провайдеры модулей
            $providerService->registerAllModuleProviders();
            
            // Получаем статистику для логирования
            $stats = $providerService->getRegistrationStats();
            $this->registeredModuleProviders = $stats['registered_providers'] ?? [];

            Log::info('ModulesProvider: Автоматическая регистрация провайдеров модулей завершена', [
                'total_providers_registered' => count($this->registeredModuleProviders),
                'providers' => $this->registeredModuleProviders
            ]);

        } catch (\Exception $e) {
            Log::error('ModulesProvider: Ошибка при регистрации провайдеров модулей', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Продолжаем работу приложения, даже если есть ошибки в регистрации модулей
            // Это позволяет системе работать даже при проблемах в отдельных модулях
            Log::warning('ModulesProvider: Продолжение работы приложения без некоторых провайдеров модулей');
        }
    }

    /**
     * Получает список зарегистрированных провайдеров модулей
     * 
     * @return array
     */
    public function getRegisteredModuleProviders(): array
    {
        return $this->registeredModuleProviders;
    }

    /**
     * Проверяет, зарегистрирован ли конкретный провайдер модуля
     * 
     * @param string $providerClass Полное имя класса провайдера
     * @return bool
     */
    public function isModuleProviderRegistered(string $providerClass): bool
    {
        return in_array($providerClass, $this->registeredModuleProviders);
    }

    /**
     * Перезагружает провайдеры модулей
     * 
     * Может быть использован в консольных командах или при обновлении модулей
     * 
     * @return void
     */
    public function reloadModuleProviders(): void
    {
        Log::info('ModulesProvider: Перезагрузка провайдеров модулей');

        $providerService = $this->app->make(ModuleProviderService::class);
        $providerService->reloadModuleProviders();

        // Обновляем список зарегистрированных провайдеров
        $stats = $providerService->getRegistrationStats();
        $this->registeredModuleProviders = $stats['registered_providers'] ?? [];

        Log::info('ModulesProvider: Перезагрузка провайдеров модулей завершена', [
            'total_providers' => count($this->registeredModuleProviders)
        ]);
    }
}