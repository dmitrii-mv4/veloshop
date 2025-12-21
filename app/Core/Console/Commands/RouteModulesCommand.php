<?php

namespace App\Core\Console\Commands;

use Illuminate\Console\Command;
use App\Core\Services\RouterLoaderService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Exception;

class RouteModulesCommand extends Command
{
    /**
     * Сигнатура команды с описанием опций
     * - --list (-l): Показать список всех модулей
     * - --module (-m): Загрузить конкретный модуль
     * - --refresh (-r): Перезагрузить все маршруты
     * - --details (-d): Показать детальную информацию
     * - --type: Фильтр по типу маршрутов
     */
    protected $signature = 'route:modules 
                          {--l|list : List all available modules}
                          {--m|module= : Load specific module routes}
                          {--r|refresh : Reload all routes}
                          {--d|details : Show detailed route information}
                          {--type= : Filter by route type (web, api, auth)}';

    /**
     * Описание команды
     */
    protected $description = 'Manage module routes in Kotiks CMS';

    /**
     * Основной метод выполнения команды
     * Распределяет выполнение по опциям командной строки
     */
    public function handle(RouterLoaderService $routerLoader): void
    {
        if ($this->option('list')) {
            $this->listModules($routerLoader);
            return;
        }

        if ($module = $this->option('module')) {
            $this->loadModule($routerLoader, $module);
            return;
        }

        if ($this->option('refresh')) {
            $this->refreshRoutes($routerLoader);
            return;
        }

        if ($this->option('details')) {
            $this->showRouteDetails();
            return;
        }

        $this->showLoadedModules($routerLoader);
    }

    /**
     * Метод 1: Список всех доступных модулей
     *  Задача: Показать таблицу с информацией о всех модулях
     *  Действия:
     *   1. Получает список модулей через RouterLoaderService
     *   2. Форматирует вывод с эмодзи и цветами
     *   3. Отображает таблицу с типами, путями и статусами
     */
    private function listModules(RouterLoaderService $routerLoader): void
    {
        $modules = $routerLoader->getAvailableModules();
        
        $this->info('Available Modules:');
        $this->newLine();
        
        if (empty($modules)) {
            $this->warn('No modules found.');
            return;
        }
        
        $rows = [];
        foreach ($modules as $module) {
            $rows[] = [
                $module['type'] === 'system' ? '🔧 System' : '📦 Dynamic',
                $module['name'],
                $module['path'],
                $this->getModuleStatus($routerLoader, $module['name'])
            ];
        }
        
        $this->table(
            ['Type', 'Module', 'Path', 'Status'],
            $rows
        );
    }

    /**
     *  Метод 2: Загрузка конкретного модуля
     *  Задача: Динамически загрузить маршруты указанного модуля
     *  Действия:
     *   1. Вызывает loadModuleRoutes из RouterLoaderService
     *   2. Проверяет результат и выводит соответствующее сообщение
     *   3. Работает как для системных, так и для пользовательских модулей
     */
    private function loadModule(RouterLoaderService $routerLoader, string $moduleName): void
    {
        $this->info("Loading routes for module: <comment>{$moduleName}</comment>");
        
        if ($routerLoader->loadModuleRoutes($moduleName)) {
            $this->info("Routes loaded successfully");
        } else {
            $this->error("Module '{$moduleName}' not found or has no routes");
        }
    }

    /**
     * Метод 3: Обновление всех маршрутов
     * Задача: Очистить кэш и перезагрузить все маршруты системы
     * Особенность: Не использует стандартный route:list для избежания ошибок
     */
    private function refreshRoutes(RouterLoaderService $routerLoader): void
    {
        $this->call('route:clear');
        $this->info('Route cache cleared');
        
        $this->info('Reloading all routes...');
        
        // Перезагружаем маршруты через сервис
        $routerLoader->loadAllRoutes();
        
        $this->info('Routes reloaded');
        
        // Показываем только загруженные модули, а не все маршруты
        $this->showLoadedModules($routerLoader);
    }

    /**
     *  Метод 4: Показать загруженные модули
     *  Задача: Отобразить текущее состояние загруженных модулей
     *  Вывод: Простая таблица с именами модулей
     */
    private function showLoadedModules(RouterLoaderService $routerLoader): void
    {
        $loaded = $routerLoader->getLoadedModules();
        
        $this->info('Loaded Modules: ' . count($loaded));
        
        if (count($loaded) > 0) {
            $this->table(['Module'], array_map(function ($module) {
                return [$module];
            }, $loaded));
        } else {
            $this->warn('No modules loaded yet.');
        }
    }

    /**
     * Метод 5: Получить статус модуля (БЕЗ moduleHasRoutes)
     * Задача: Определить текстовый и цветовой статус модуля
     * Решение проблемы: Использует прямое проверку файлов вместо вызова несуществующего метода
     */
    private function getModuleStatus(RouterLoaderService $routerLoader, string $moduleName): string
    {
        if ($routerLoader->isModuleLoaded($moduleName)) {
            return '<fg=green>Loaded</>';
        }
        
        // 🔧 Временное решение: прямая проверка файлов
        // TODO: Добавить метод moduleHasRoutes() в RouterLoaderService
        $hasRoutes = file_exists(base_path("app/Modules/{$moduleName}/routes/web.php")) ||
                    file_exists(base_path("Modules/{$moduleName}/routes/web.php"));
        
        if ($hasRoutes) {
            return '<fg=yellow>Available</>';
        }
        
        return '<fg=gray>No routes</>';
    }

    /**
     * Метод 6: Показать детальную информацию о маршрутах
     * Задача: Отобразить список маршрутов с обработкой исключений
     * Безопасность: Ловит исключения Reflection при отсутствии контроллеров
     */
    private function showRouteDetails(): void
    {
        $this->info('Loading route details...');
        
        try {
            // Пробуем вызвать route:list с обработкой исключений
            $this->call('route:list', [
                '--name' => 'admin.',
                '--sort' => 'uri',
                '--except-path' => 'Modules/Katalog', // Исключаем проблемный модуль
            ]);
        } catch (Exception $e) {
            $this->error('Error loading route details: ' . $e->getMessage());
            $this->warn('Showing custom route list instead...');
            
            // Показываем пользовательский список маршрутов
            $this->showCustomRouteList();
        }
    }

    /**
     * Метод 7: Пользовательский список маршрутов (без Reflection)
     * Задача: Обойти ошибку Reflection при отсутствии классов контроллеров
     * Реализация: Собирает информацию напрямую из объектов Route
     * Ограничение: Не показывает путь к файлам контроллеров
     */
    private function showCustomRouteList(): void
    {
        $routes = Route::getRoutes();
        $routeData = [];
        
        foreach ($routes as $route) {
            // Фильтруем только админские маршруты
            $uri = $route->uri();
            $name = $route->getName();
            
            if (strpos($name ?? '', 'admin.') === 0) {
                $routeData[] = [
                    'Method' => implode('|', $route->methods()),
                    'URI' => $uri,
                    'Name' => $name ?? 'N/A',
                    'Middleware' => implode(', ', $route->middleware()),
                ];
            }
        }
        
        if (empty($routeData)) {
            $this->warn('No admin routes found.');
            return;
        }
        
        $this->info('Admin Routes:');
        $this->table(
            ['Method', 'URI', 'Name', 'Middleware'],
            $routeData
        );
    }
}