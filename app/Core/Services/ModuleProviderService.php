<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use Illuminate\Foundation\Application;

/**
 * Сервис для автоматической регистрации провайдеров модулей
 * 
 * Отвечает за обнаружение и регистрацию сервис-провайдеров
 * активных модулей системы. Сканирует директории провайдеров
 * модулей и регистрирует их в контейнере приложения.
 * 
 * Принцип работы:
 * 1. Получает список активных модулей через ModuleDiscoveryService
 * 2. Для каждого модуля сканирует директорию Providers
 * 3. Автоматически регистрирует все найденные провайдеры
 * 4. Выполняет порядок загрузки с учетом зависимостей
 */
class ModuleProviderService
{
    /**
     * Сервис обнаружения модулей
     * 
     * @var ModuleDiscoveryService
     */
    protected ModuleDiscoveryService $moduleDiscovery;

    /**
     * Массив зарегистрированных провайдеров
     * 
     * @var array
     */
    protected array $registeredProviders = [];

    /**
     * Массив зарегистрированных модулей с их провайдерами
     * 
     * @var array
     */
    protected array $moduleProviders = [];

    /**
     * Инстанс приложения Laravel
     * 
     * @var Application
     */
    protected Application $app;

    /**
     * Конструктор сервиса
     * 
     * @param ModuleDiscoveryService $moduleDiscovery Сервис обнаружения модулей
     * @param Application $app Инстанс приложения
     */
    public function __construct(ModuleDiscoveryService $moduleDiscovery, Application $app)
    {
        $this->moduleDiscovery = $moduleDiscovery;
        $this->app = $app;
        
        Log::info('ModuleProviderService: Сервис инициализирован');
    }

    /**
     * Регистрирует провайдеры всех активных модулей
     * 
     * Основной метод сервиса, который выполняет:
     * 1. Получение списка активных модулей
     * 2. Проверку зависимостей между модулями
     * 3. Построение порядка загрузки
     * 4. Регистрацию провайдеров в правильном порядке
     * 
     * @return void
     */
    public function registerAllModuleProviders(): void
    {
        Log::info('ModuleProviderService: Начало регистрации провайдеров модулей');

        try {
            // Получаем активные модули
            $activeModules = $this->moduleDiscovery->getActiveModules();
            
            if (empty($activeModules)) {
                Log::info('ModuleProviderService: Нет активных модулей для регистрации провайдеров');
                return;
            }

            Log::info('ModuleProviderService: Активные модули обнаружены', [
                'modules_count' => count($activeModules),
                'modules' => array_keys($activeModules)
            ]);

            // Собираем информацию о провайдерах всех модулей
            $this->collectModuleProviders($activeModules);

            // Строим порядок загрузки с учетом зависимостей
            $loadOrder = $this->buildLoadOrder();

            // Регистрируем провайдеры в правильном порядке
            $this->registerProvidersInOrder($loadOrder);

            Log::info('ModuleProviderService: Регистрация провайдеров модулей завершена успешно', [
                'total_providers_registered' => count($this->registeredProviders),
                'modules_with_providers' => count($this->moduleProviders),
                'registered_providers' => $this->registeredProviders
            ]);

        } catch (\Exception $e) {
            Log::error('ModuleProviderService: Ошибка при регистрации провайдеров модулей', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Собирает информацию о провайдерах всех модулей
     * 
     * @param array $activeModules Массив активных модулей
     * @return void
     */
    protected function collectModuleProviders(array $activeModules): void
    {
        foreach ($activeModules as $moduleName => $moduleConfig) {
            $providers = $this->discoverModuleProviders($moduleName, $moduleConfig);
            
            if (!empty($providers)) {
                $this->moduleProviders[$moduleName] = [
                    'providers' => $providers,
                    'dependencies' => $moduleConfig['module']['dependencies'] ?? [],
                    'priority' => $moduleConfig['module']['priority'] ?? 100
                ];
                
                Log::debug('ModuleProviderService: Обнаружены провайдеры модуля', [
                    'module' => $moduleName,
                    'providers_count' => count($providers),
                    'providers' => $providers
                ]);
            } else {
                Log::debug('ModuleProviderService: Провайдеры не обнаружены для модуля', [
                    'module' => $moduleName
                ]);
            }
        }
    }

    /**
     * Обнаруживает провайдеры конкретного модуля
     * 
     * Сканирует директорию Providers модуля и возвращает
     * список полных имен классов провайдеров
     * 
     * @param string $moduleName Название модуля
     * @param array $moduleConfig Конфигурация модуля
     * @return array Массив полных имен классов провайдеров
     */
    protected function discoverModuleProviders(string $moduleName, array $moduleConfig): array
    {
        $modulePath = $moduleConfig['path'] ?? $this->moduleDiscovery->getModulePath($moduleName);
        
        if (!$modulePath) {
            Log::warning('ModuleProviderService: Не удалось получить путь модуля', [
                'module' => $moduleName
            ]);
            return [];
        }

        $providersPath = $modulePath . DIRECTORY_SEPARATOR . 'Providers';
        
        if (!File::exists($providersPath) || !File::isDirectory($providersPath)) {
            Log::debug('ModuleProviderService: Директория провайдеров не найдена', [
                'module' => $moduleName,
                'path' => $providersPath
            ]);
            return [];
        }

        $providers = [];
        $files = File::files($providersPath);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $filename = $file->getFilenameWithoutExtension();
            
            // Формируем полное имя класса провайдера
            $className = "App\\Modules\\{$moduleName}\\Providers\\{$filename}";
            
            // Проверяем, что класс существует
            if (class_exists($className)) {
                $providers[] = $className;
                
                Log::debug('ModuleProviderService: Обнаружен провайдер', [
                    'module' => $moduleName,
                    'provider' => $className,
                    'file' => $file->getFilename()
                ]);
            } else {
                Log::warning('ModuleProviderService: Класс провайдера не найден', [
                    'module' => $moduleName,
                    'expected_class' => $className,
                    'file' => $file->getFilename()
                ]);
            }
        }

        return $providers;
    }

    /**
     * Строит порядок загрузки провайдеров с учетом зависимостей
     * 
     * Использует топологическую сортировку для определения
     * порядка загрузки, учитывая зависимости между модулями
     * 
     * @return array Массив модулей в порядке загрузки
     */
    protected function buildLoadOrder(): array
    {
        $graph = [];
        $inDegree = [];
        
        // Инициализация графа зависимостей
        foreach ($this->moduleProviders as $moduleName => $moduleInfo) {
            $graph[$moduleName] = [];
            $inDegree[$moduleName] = 0;
        }
        
        // Построение графа зависимостей
        foreach ($this->moduleProviders as $moduleName => $moduleInfo) {
            foreach ($moduleInfo['dependencies'] as $dependency) {
                if (isset($graph[$dependency])) {
                    $graph[$dependency][] = $moduleName;
                    $inDegree[$moduleName]++;
                }
            }
        }
        
        // Топологическая сортировка (алгоритм Кана)
        $queue = new \SplQueue();
        $result = [];
        
        // Добавляем модули без зависимостей
        foreach ($inDegree as $moduleName => $degree) {
            if ($degree === 0) {
                $queue->enqueue($moduleName);
            }
        }
        
        while (!$queue->isEmpty()) {
            $moduleName = $queue->dequeue();
            $result[] = $moduleName;
            
            foreach ($graph[$moduleName] as $dependentModule) {
                $inDegree[$dependentModule]--;
                if ($inDegree[$dependentModule] === 0) {
                    $queue->enqueue($dependentModule);
                }
            }
        }
        
        // Проверка на циклические зависимости
        if (count($result) !== count($this->moduleProviders)) {
            $errorMsg = 'Обнаружены циклические зависимости между модулями';
            Log::error('ModuleProviderService: ' . $errorMsg, [
                'modules_count' => count($this->moduleProviders),
                'sorted_count' => count($result),
                'modules_with_dependencies' => $this->moduleProviders
            ]);
            
            throw new \RuntimeException($errorMsg);
        }
        
        Log::info('ModuleProviderService: Порядок загрузки модулей построен', [
            'load_order' => $result
        ]);
        
        return $result;
    }

    /**
     * Регистрирует провайдеры в определенном порядке
     * 
     * @param array $loadOrder Порядок загрузки модулей
     * @return void
     */
    protected function registerProvidersInOrder(array $loadOrder): void
    {
        foreach ($loadOrder as $moduleName) {
            if (!isset($this->moduleProviders[$moduleName])) {
                continue;
            }
            
            foreach ($this->moduleProviders[$moduleName]['providers'] as $providerClass) {
                $this->registerProvider($providerClass, $moduleName);
            }
        }
    }

    /**
     * Регистрирует отдельный провайдер в приложении
     * 
     * @param string $providerClass Полное имя класса провайдера
     * @param string $moduleName Название модуля
     * @return void
     */
    protected function registerProvider(string $providerClass, string $moduleName): void
    {
        try {
            // Проверяем, не зарегистрирован ли уже этот провайдер
            if (in_array($providerClass, $this->registeredProviders)) {
                Log::debug('ModuleProviderService: Провайдер уже зарегистрирован', [
                    'module' => $moduleName,
                    'provider' => $providerClass
                ]);
                return;
            }
            
            // Регистрируем провайдер в контейнере приложения
            $this->app->register($providerClass);
            
            // Добавляем в список зарегистрированных
            $this->registeredProviders[] = $providerClass;
            
            Log::info('ModuleProviderService: Провайдер успешно зарегистрирован', [
                'module' => $moduleName,
                'provider' => $providerClass
            ]);
            
        } catch (\Exception $e) {
            Log::error('ModuleProviderService: Ошибка регистрации провайдера', [
                'module' => $moduleName,
                'provider' => $providerClass,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Не прерываем выполнение, продолжаем регистрацию остальных провайдеров
        }
    }

    /**
     * Проверяет, зарегистрирован ли конкретный провайдер
     * 
     * @param string $providerClass Полное имя класса провайдера
     * @return bool
     */
    public function isProviderRegistered(string $providerClass): bool
    {
        return in_array($providerClass, $this->registeredProviders);
    }

    /**
     * Получает статистику зарегистрированных провайдеров
     * 
     * @return array Статистика зарегистрированных провайдеров
     */
    public function getRegistrationStats(): array
    {
        $stats = [
            'total_registered' => count($this->registeredProviders),
            'modules_with_providers' => count($this->moduleProviders),
            'registered_providers' => $this->registeredProviders,
            'module_providers' => []
        ];
        
        foreach ($this->moduleProviders as $moduleName => $moduleInfo) {
            $stats['module_providers'][$moduleName] = [
                'providers_count' => count($moduleInfo['providers']),
                'providers' => $moduleInfo['providers'],
                'dependencies' => $moduleInfo['dependencies']
            ];
        }
        
        return $stats;
    }

    /**
     * Очищает список зарегистрированных провайдеров
     * 
     * Используется для тестирования или перезагрузки провайдеров
     * 
     * @return void
     */
    public function clearRegisteredProviders(): void
    {
        $this->registeredProviders = [];
        $this->moduleProviders = [];
        
        Log::info('ModuleProviderService: Список зарегистрированных провайдеров очищен');
    }

    /**
     * Перезагружает провайдеры модулей
     * 
     * Очищает текущие данные и заново регистрирует провайдеры
     * 
     * @return void
     */
    public function reloadModuleProviders(): void
    {
        Log::info('ModuleProviderService: Перезагрузка провайдеров модулей');
        
        $this->clearRegisteredProviders();
        $this->registerAllModuleProviders();
    }

    /**
     * Получает список зарегистрированных провайдеров
     * 
     * @return array
     */
    public function getRegisteredProviders(): array
    {
        return $this->registeredProviders;
    }
}