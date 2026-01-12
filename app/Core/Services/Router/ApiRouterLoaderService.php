<?php

namespace App\Core\Services\Router;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use App\Core\Services\ModuleDiscoveryService;

/**
 * Сервис для загрузки API-маршрутов системы
 * 
 * Динамически загружает API маршруты из конфигурационных файлов:
 * - app/Admin/config.php (админские API маршруты)
 * - app/Modules/{ModuleName}/config.php (модульные API маршруты)
 * 
 * Поддерживает загрузку нескольких файлов API маршрутов для одного типа
 * 
 * @package App\Core\Services\Router
 */
class ApiRouterLoaderService
{
    /**
     * Сервис обнаружения модулей
     * 
     * @var ModuleDiscoveryService
     */
    protected ModuleDiscoveryService $moduleDiscovery;

    /**
     * Массив для отслеживания загруженных API endpoints
     * 
     * @var array
     */
    protected array $loadedEndpoints = [];

    /**
     * Конструктор сервиса
     * 
     * @param ModuleDiscoveryService $moduleDiscovery Сервис обнаружения модулей
     */
    public function __construct(ModuleDiscoveryService $moduleDiscovery)
    {
        $this->moduleDiscovery = $moduleDiscovery;
        Log::info('ApiRouterLoaderService: Сервис инициализирован');
    }

    /**
     * Загружает все API-маршруты системы
     * 
     * Последовательность загрузки:
     * 1. Админские API маршруты
     * 2. Маршруты активных модулей
     * 
     * @return void
     */
    public function loadAllRoutes(): void
    {
        Log::info('ApiRouterLoaderService: Начало загрузки API-маршрутов системы');

        try {
            // Загрузка админских API маршрутов
            $this->loadAdminApiRoutes();

            // Загрузка модульных API маршрутов
            $this->loadModulesApiRoutes();

            // Загрузка системных API маршрутов (всегда доступны)
            $this->loadSystemApiRoutes();

            Log::info('ApiRouterLoaderService: Загрузка API-маршрутов завершена успешно', [
                'loaded_endpoints' => array_keys($this->loadedEndpoints),
                'total_files_loaded' => $this->countLoadedFiles()
            ]);

        } catch (\Exception $e) {
            Log::error('ApiRouterLoaderService: Критическая ошибка при загрузке API маршрутов', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // Резервный маршрут для ошибок API
            $this->loadFallbackApiRoutes();
            
            throw $e;
        }
    }

    /**
     * Загружает админские API-маршруты
     * 
     * @return void
     */
    protected function loadAdminApiRoutes(): void
    {
        $adminConfigPath = app_path('Admin/config.php');

        if (!File::exists($adminConfigPath)) {
            Log::warning('ApiRouterLoaderService: Конфигурационный файл админки не найден', [
                'path' => $adminConfigPath
            ]);
            return;
        }

        try {
            $config = require $adminConfigPath;
            
            if (!isset($config['routes']['api'])) {
                Log::warning('ApiRouterLoaderService: Секция API-маршрутов не найдена в конфигурации админки');
                return;
            }

            $routeConfigs = $this->normalizeRouteConfigs($config['routes']['api']);
            
            foreach ($routeConfigs as $index => $routeConfig) {
                $this->loadSingleApiRouteConfig($routeConfig, 'admin', $index);
            }

        } catch (\Exception $e) {
            Log::error('ApiRouterLoaderService: Ошибка загрузки админских API маршрутов', [
                'config_path' => $adminConfigPath,
                'message' => $e->getMessage(),
                'exception' => $e
            ]);
        }
    }

    /**
     * Загружает API-маршруты всех активных модулей
     * 
     * @return void
     */
    protected function loadModulesApiRoutes(): void
    {
        $activeModules = $this->moduleDiscovery->getActiveModules();

        if (empty($activeModules)) {
            Log::info('ApiRouterLoaderService: Активные модули не обнаружены для загрузки API маршрутов');
            return;
        }

        foreach ($activeModules as $moduleName => $moduleConfig) {
            $this->loadModuleApiRoutes($moduleName, $moduleConfig);
        }

        Log::info('ApiRouterLoaderService: Загружены API маршруты модулей', [
            'modules_count' => count($activeModules)
        ]);
    }

    /**
     * Загружает API-маршруты конкретного модуля
     * 
     * @param string $moduleName Название модуля
     * @param array $moduleConfig Конфигурация модуля
     * @return void
     */
    protected function loadModuleApiRoutes(string $moduleName, array $moduleConfig): void
    {
        try {
            if (!isset($moduleConfig['routes']['api'])) {
                Log::debug("ApiRouterLoaderService: Модуль {$moduleName} не содержит API маршрутов");
                return;
            }

            $routeConfigs = $this->normalizeRouteConfigs($moduleConfig['routes']['api']);
            $loadedCount = 0;
            
            foreach ($routeConfigs as $index => $routeConfig) {
                if ($this->loadSingleApiRouteConfig($routeConfig, $moduleName, $index)) {
                    $loadedCount++;
                }
            }

            if ($loadedCount > 0) {
                $this->loadedEndpoints[$moduleName] = [
                    'module' => $moduleName,
                    'files_loaded' => $loadedCount,
                    'total_files' => count($routeConfigs)
                ];
            }

        } catch (\Exception $e) {
            Log::error("ApiRouterLoaderService: Ошибка загрузки API маршрутов модуля {$moduleName}", [
                'module' => $moduleName,
                'message' => $e->getMessage(),
                'exception' => $e
            ]);
        }
    }

    /**
     * Загружает один конфиг API маршрута
     * 
     * @param array $routeConfig Конфигурация маршрута
     * @param string $source Источник (модуль или админка)
     * @param int $index Индекс конфига
     * @return bool Успешность загрузки
     */
    protected function loadSingleApiRouteConfig(array $routeConfig, string $source, int $index = 0): bool
    {
        try {
            $this->validateApiRouteConfig($routeConfig, $source, $index);

            Route::prefix($routeConfig['prefix'] ?? 'api')
                ->middleware($routeConfig['middleware'] ?? ['api'])
                ->group(function () use ($routeConfig, $source) {
                    $this->requireApiRouteFile($routeConfig['path'], $source);
                });

            $key = "{$source}_api_{$index}";
            if (!isset($this->loadedEndpoints[$source])) {
                $this->loadedEndpoints[$source] = [
                    'prefix' => $routeConfig['prefix'] ?? 'api',
                    'middleware' => $routeConfig['middleware'] ?? ['api'],
                    'files' => []
                ];
            }

            $this->loadedEndpoints[$source]['files'][] = [
                'path' => $routeConfig['path'],
                'prefix' => $routeConfig['prefix'] ?? 'api',
                'middleware' => $routeConfig['middleware'] ?? ['api'],
                'index' => $index,
                'loaded_at' => now()->toISOString()
            ];

            Log::debug("ApiRouterLoaderService: Загружен файл API маршрутов", [
                'source' => $source,
                'file' => $routeConfig['path'],
                'prefix' => $routeConfig['prefix'] ?? 'api',
                'index' => $index
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("ApiRouterLoaderService: Ошибка загрузки конфига API маршрута", [
                'source' => $source,
                'index' => $index,
                'config' => $routeConfig,
                'message' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Нормализует конфигурацию API маршрутов для обработки
     * 
     * @param mixed $routeConfigs Конфигурация маршрутов (массив или одиночный конфиг)
     * @return array Нормализованный массив конфигов
     */
    protected function normalizeRouteConfigs($routeConfigs): array
    {
        // Если это массив с числовыми ключами - уже массив конфигов
        if (is_array($routeConfigs) && isset($routeConfigs[0])) {
            return $routeConfigs;
        }
        
        // Если это одиночный конфиг, оборачиваем в массив
        if (is_array($routeConfigs) && isset($routeConfigs['path'])) {
            return [$routeConfigs];
        }
        
        // Если это невалидная структура, возвращаем пустой массив
        return [];
    }

    /**
     * Загружает системные API маршруты (всегда доступны)
     * 
     * @return void
     */
    protected function loadSystemApiRoutes(): void
    {
        Route::prefix('api')->group(function () {
            // Health check endpoint
            Route::get('/system/health', function () {
                return response()->json([
                    'status' => 'healthy',
                    'service' => 'kotiks-cms',
                    'version' => config('app.version', '1.0.0'),
                    'timestamp' => now()->toISOString(),
                    'environment' => config('app.env'),
                    'loaded_modules' => array_keys($this->loadedEndpoints),
                    'loaded_files_count' => $this->countLoadedFiles()
                ]);
            })->name('api.system.health');

            // System info endpoint
            Route::get('/system/info', function () {
                return response()->json([
                    'app_name' => config('app.name'),
                    'environment' => config('app.env'),
                    'debug_mode' => config('app.debug'),
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                    'loaded_modules_count' => count($this->loadedEndpoints),
                    'loaded_modules' => array_keys($this->loadedEndpoints),
                    'timestamp' => now()->toISOString()
                ]);
            })->name('api.system.info');

            // API documentation endpoint
            Route::get('/system/routes', function () {
                $routes = [];
                foreach (Route::getRoutes() as $route) {
                    if (str_starts_with($route->uri(), 'api/')) {
                        $routes[] = [
                            'uri' => $route->uri(),
                            'methods' => $route->methods(),
                            'name' => $route->getName(),
                            'middleware' => $route->middleware()
                        ];
                    }
                }

                return response()->json([
                    'routes' => $routes,
                    'count' => count($routes),
                    'timestamp' => now()->toISOString()
                ]);
            })->middleware(['api'])->name('api.system.routes');
        });

        Log::info('ApiRouterLoaderService: Системные API маршруты загружены');
    }

    /**
     * Загружает резервные API маршруты на случай ошибок
     * 
     * @return void
     */
    protected function loadFallbackApiRoutes(): void
    {
        Route::prefix('api')->group(function () {
            Route::get('/error', function () {
                return response()->json([
                    'status' => 'error',
                    'message' => 'API service initialization failed',
                    'timestamp' => now()->toISOString(),
                    'support' => 'Check application logs for details'
                ], 500);
            })->name('api.fallback.error');

            Route::get('/health', function () {
                return response()->json([
                    'status' => 'degraded',
                    'message' => 'API service in fallback mode',
                    'timestamp' => now()->toISOString()
                ], 503);
            })->name('api.fallback.health');
        });

        Log::warning('ApiRouterLoaderService: Загружены резервные API маршруты');
    }

    /**
     * Валидирует конфигурацию API маршрутов
     * 
     * @param array $config Конфигурация API маршрутов
     * @param string $source Источник конфигурации
     * @param int $index Индекс конфига
     * @return void
     * @throws \InvalidArgumentException Если конфигурация невалидна
     */
    protected function validateApiRouteConfig(array $config, string $source, int $index = 0): void
    {
        if (!isset($config['path'])) {
            throw new \InvalidArgumentException(
                "Конфигурация API маршрутов для {$source} (индекс {$index}) должна содержать путь к файлу маршрутов (ключ 'path')"
            );
        }

        $routePath = base_path($config['path']);
        if (!File::exists($routePath)) {
            throw new \InvalidArgumentException(
                "Файл API маршрутов не найден: {$routePath} (источник: {$source}, индекс: {$index})"
            );
        }

        // Проверяем, что у API маршрутов есть префикс
        if (!isset($config['prefix']) || empty($config['prefix'])) {
            Log::warning("ApiRouterLoaderService: API маршруты для {$source} (индекс {$index}) не имеют префикса. Рекомендуется установить префикс.");
        }

        if (isset($config['middleware']) && !is_array($config['middleware'])) {
            throw new \InvalidArgumentException(
                "Middleware для API маршрутов {$source} (индекс {$index}) должен быть массивом"
            );
        }
    }

    /**
     * Подключает файл с API маршрутами
     * 
     * @param string $relativePath Относительный путь к файлу маршрутов
     * @param string $source Источник (модуль или админка)
     * @return void
     * @throws \Exception Если произошла ошибка при подключении файла
     */
    protected function requireApiRouteFile(string $relativePath, string $source): void
    {
        $absolutePath = base_path($relativePath);

        if (!File::exists($absolutePath)) {
            throw new \RuntimeException("Файл API маршрутов не существует: {$absolutePath} (источник: {$source})");
        }

        // Регистрируем контекст для отладки
        $context = [
            'source' => $source,
            'file' => $relativePath,
            'timestamp' => now()->toISOString()
        ];

        Log::debug("ApiRouterLoaderService: Загрузка API маршрутов из файла", $context);

        require $absolutePath;
    }

    /**
     * Подсчитывает общее количество загруженных файлов
     * 
     * @return int
     */
    protected function countLoadedFiles(): int
    {
        $count = 0;
        foreach ($this->loadedEndpoints as $endpoint) {
            if (isset($endpoint['files'])) {
                $count += count($endpoint['files']);
            }
        }
        return $count;
    }

    /**
     * Получает информацию о загруженных API endpoints
     * 
     * @return array Информация о загруженных API endpoints
     */
    public function getApiEndpointsInfo(): array
    {
        $filesCount = $this->countLoadedFiles();
        
        return [
            'loaded_endpoints' => $this->loadedEndpoints,
            'total_endpoints' => count($this->loadedEndpoints),
            'total_files_loaded' => $filesCount,
            'modules_with_api' => array_keys(array_filter(
                $this->loadedEndpoints,
                fn($endpoint) => isset($endpoint['module'])
            )),
            'admin_api_loaded' => isset($this->loadedEndpoints['admin']),
            'system_endpoints' => ['health', 'info', 'routes']
        ];
    }

    /**
     * Проверяет, доступен ли API модуля
     * 
     * @param string $moduleName Название модуля
     * @return bool
     */
    public function isModuleApiAvailable(string $moduleName): bool
    {
        return isset($this->loadedEndpoints[$moduleName]);
    }
}