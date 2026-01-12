<?php

namespace App\Core\Services\Router;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use App\Core\Services\ModuleDiscoveryService;

/**
 * Сервис для загрузки web-маршрутов системы
 * 
 * Динамически загружает маршруты из конфигурационных файлов:
 * - app/Admin/config.php (админские маршруты)
 * - app/Modules/{ModuleName}/config.php (модульные маршруты)
 * 
 * Поддерживает загрузку нескольких файлов маршрутов для одного типа
 * 
 * @package App\Core\Services\Router
 */
class RouterLoaderService
{
    /**
     * Сервис обнаружения модулей
     * 
     * @var ModuleDiscoveryService
     */
    protected ModuleDiscoveryService $moduleDiscovery;

    /**
     * Массив для отслеживания загруженных маршрутов
     * 
     * @var array
     */
    protected array $loadedRoutes = [];

    /**
     * Конструктор сервиса
     * 
     * @param ModuleDiscoveryService $moduleDiscovery Сервис обнаружения модулей
     */
    public function __construct(ModuleDiscoveryService $moduleDiscovery)
    {
        $this->moduleDiscovery = $moduleDiscovery;
        Log::info('RouterLoaderService: Сервис инициализирован');
    }

    /**
     * Загружает все web-маршруты системы
     * 
     * Последовательность загрузки:
     * 1. Админские маршруты
     * 2. Маршруты активных модулей
     * 
     * @return void
     */
    public function loadAllRoutes(): void
    {
        Log::info('RouterLoaderService: Начало загрузки web-маршрутов системы');

        try {
            // Загрузка админских маршрутов
            $this->loadAdminRoutes();

            // Загрузка модульных маршрутов
            $this->loadModulesRoutes();

            Log::info('RouterLoaderService: Загрузка web-маршрутов завершена успешно', [
                'total_files_loaded' => count($this->loadedRoutes),
                'loaded_files' => array_keys($this->loadedRoutes)
            ]);

        } catch (\Exception $e) {
            Log::error('RouterLoaderService: Критическая ошибка при загрузке маршрутов', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Загружает админские web-маршруты
     * 
     * @return void
     */
    protected function loadAdminRoutes(): void
    {
        $adminConfigPath = app_path('Admin/config.php');

        if (!File::exists($adminConfigPath)) {
            Log::warning('RouterLoaderService: Конфигурационный файл админки не найден', [
                'path' => $adminConfigPath
            ]);
            return;
        }

        try {
            $config = require $adminConfigPath;
            
            if (!isset($config['routes']['web'])) {
                Log::warning('RouterLoaderService: Секция web-маршрутов не найдена в конфигурации админки');
                return;
            }

            $routeConfigs = $this->normalizeRouteConfigs($config['routes']['web']);
            
            foreach ($routeConfigs as $index => $routeConfig) {
                $this->loadSingleRouteConfig($routeConfig, 'admin', $index);
            }

        } catch (\Exception $e) {
            Log::error('RouterLoaderService: Ошибка загрузки админских маршрутов', [
                'config_path' => $adminConfigPath,
                'message' => $e->getMessage(),
                'exception' => $e
            ]);
        }
    }

    /**
     * Загружает web-маршруты всех активных модулей
     * 
     * @return void
     */
    protected function loadModulesRoutes(): void
    {
        $activeModules = $this->moduleDiscovery->getActiveModules();

        if (empty($activeModules)) {
            Log::info('RouterLoaderService: Активные модули не обнаружены');
            return;
        }

        foreach ($activeModules as $moduleName => $moduleConfig) {
            $this->loadModuleWebRoutes($moduleName, $moduleConfig);
        }

        Log::info('RouterLoaderService: Загружены маршруты модулей', [
            'modules_count' => count($activeModules),
            'modules' => array_keys($activeModules)
        ]);
    }

    /**
     * Загружает web-маршруты конкретного модуля
     * 
     * @param string $moduleName Название модуля
     * @param array $moduleConfig Конфигурация модуля
     * @return void
     */
    protected function loadModuleWebRoutes(string $moduleName, array $moduleConfig): void
    {
        try {
            if (!isset($moduleConfig['routes']['web'])) {
                Log::debug("RouterLoaderService: Модуль {$moduleName} не содержит web-маршрутов");
                return;
            }

            $routeConfigs = $this->normalizeRouteConfigs($moduleConfig['routes']['web']);
            $loadedCount = 0;
            
            foreach ($routeConfigs as $index => $routeConfig) {
                if ($this->loadSingleRouteConfig($routeConfig, $moduleName, $index)) {
                    $loadedCount++;
                }
            }

            if ($loadedCount > 0) {
                Log::info("RouterLoaderService: Web-маршруты модуля {$moduleName} загружены", [
                    'files_loaded' => $loadedCount,
                    'total_files' => count($routeConfigs)
                ]);
            }

        } catch (\Exception $e) {
            Log::error("RouterLoaderService: Ошибка загрузки web-маршрутов модуля {$moduleName}", [
                'module' => $moduleName,
                'message' => $e->getMessage(),
                'exception' => $e
            ]);
        }
    }

    /**
     * Загружает один конфиг маршрута
     * 
     * @param array $routeConfig Конфигурация маршрута
     * @param string $source Источник (модуль или админка)
     * @param int $index Индекс конфига (для нескольких файлов)
     * @return bool Успешность загрузки
     */
    protected function loadSingleRouteConfig(array $routeConfig, string $source, int $index = 0): bool
    {
        try {
            $this->validateRouteConfig($routeConfig, $source, $index);

            Route::prefix($routeConfig['prefix'] ?? '')
                ->middleware($routeConfig['middleware'] ?? [])
                ->group(function () use ($routeConfig) {
                    $this->requireRouteFile($routeConfig['path']);
                });

            $key = "{$source}_web_{$index}";
            $this->loadedRoutes[$key] = [
                'source' => $source,
                'path' => $routeConfig['path'],
                'prefix' => $routeConfig['prefix'] ?? '',
                'middleware' => $routeConfig['middleware'] ?? [],
                'loaded_at' => now()->toISOString()
            ];

            Log::debug("RouterLoaderService: Загружен файл маршрутов", [
                'source' => $source,
                'file' => $routeConfig['path'],
                'prefix' => $routeConfig['prefix'] ?? '',
                'index' => $index
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("RouterLoaderService: Ошибка загрузки конфига маршрута", [
                'source' => $source,
                'index' => $index,
                'config' => $routeConfig,
                'message' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Нормализует конфигурацию маршрутов для обработки
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
     * Валидирует конфигурацию маршрутов
     * 
     * @param array $config Конфигурация маршрутов
     * @param string $source Источник конфигурации (модуль или админка)
     * @param int $index Индекс конфига
     * @return void
     * @throws \InvalidArgumentException Если конфигурация невалидна
     */
    protected function validateRouteConfig(array $config, string $source, int $index = 0): void
    {
        if (!isset($config['path'])) {
            throw new \InvalidArgumentException(
                "Конфигурация маршрутов для {$source} (индекс {$index}) должна содержать путь к файлу маршрутов (ключ 'path')"
            );
        }

        $routePath = base_path($config['path']);
        if (!File::exists($routePath)) {
            throw new \InvalidArgumentException(
                "Файл маршрутов не найден: {$routePath} (источник: {$source}, индекс: {$index})"
            );
        }

        if (isset($config['middleware']) && !is_array($config['middleware'])) {
            throw new \InvalidArgumentException(
                "Middleware для маршрутов {$source} (индекс {$index}) должен быть массивом"
            );
        }

        if (isset($config['prefix']) && !is_string($config['prefix'])) {
            throw new \InvalidArgumentException(
                "Префикс для маршрутов {$source} (индекс {$index}) должен быть строкой"
            );
        }
    }

    /**
     * Подключает файл с маршрутами с обработкой ошибок
     * 
     * @param string $relativePath Относительный путь к файлу маршрутов
     * @return void
     * @throws \Exception Если произошла ошибка при подключении файла
     */
    protected function requireRouteFile(string $relativePath): void
    {
        $absolutePath = base_path($relativePath);

        if (!File::exists($absolutePath)) {
            throw new \RuntimeException("Файл маршрутов не существует: {$absolutePath}");
        }

        require $absolutePath;
    }

    /**
     * Получает статистику загруженных маршрутов
     * 
     * @return array Статистика загруженных маршрутов
     */
    public function getRoutesStats(): array
    {
        $routes = Route::getRoutes();
        $adminRoutes = [];
        $moduleRoutes = [];
        $authRoutes = [];

        foreach ($routes as $route) {
            $uri = $route->uri();
            $name = $route->getName();
            
            // Определяем тип маршрута по URI или имени
            if (str_starts_with($uri, 'admin/')) {
                $adminRoutes[] = [
                    'uri' => $uri,
                    'name' => $name,
                    'methods' => $route->methods()
                ];
            } elseif (in_array($name, ['login', 'logout', 'register', 'password.request'])) {
                $authRoutes[] = [
                    'uri' => $uri,
                    'name' => $name,
                    'methods' => $route->methods()
                ];
            } elseif (!str_starts_with($uri, 'api/')) {
                $moduleRoutes[] = [
                    'uri' => $uri,
                    'name' => $name,
                    'methods' => $route->methods()
                ];
            }
        }

        return [
            'total_routes' => count($routes),
            'admin_routes' => count($adminRoutes),
            'module_routes' => count($moduleRoutes),
            'auth_routes' => count($authRoutes),
            'loaded_files' => $this->loadedRoutes,
            'auth_route_names' => array_column($authRoutes, 'name')
        ];
    }

    /**
     * Проверяет, загружен ли конкретный файл маршрутов
     * 
     * @param string $relativePath Относительный путь к файлу
     * @return bool
     */
    public function isRouteFileLoaded(string $relativePath): bool
    {
        foreach ($this->loadedRoutes as $routeInfo) {
            if ($routeInfo['path'] === $relativePath) {
                return true;
            }
        }
        
        return false;
    }
}