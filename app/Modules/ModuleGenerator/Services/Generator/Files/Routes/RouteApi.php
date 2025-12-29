<?php

namespace App\Modules\ModuleGenerator\Services\Generator\Files\Routes;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для генерации API-роутов модуля
 * Генерирует API-роуты для модулей по аналогии с IBlock
 * 
 * @param array $moduleData Настройки модулей
 * @param string $moduleRouterFullPath абсолютный путь к директории роутов модуля
 */
class RouteApi
{
    protected $moduleData;
    protected $moduleRouterFullPath;

    public function __construct($moduleData, $moduleRouterFullPath)
    {
        $this->moduleData = $moduleData;
        $this->moduleRouterFullPath = $moduleRouterFullPath;
    }

    public function generate()
    {
        try {
            Log::info('Начало генерации API роутов модуля', [
                'module' => $this->moduleData['code_module']
            ]);

            // Генерируем API роуты
            $this->createApiRoutes();

            Log::info('Успешное завершение генерации API роутов модуля', [
                'module' => $this->moduleData['code_module']
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Критическая ошибка при генерации API роутов модуля', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \RuntimeException("Ошибка генерации API роутов: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Создаём API роуты модуля
     */
    public function createApiRoutes()
    {
        try {
            // Используем готовые данные из moduleData для API контроллера
            $apiControllerName = $this->moduleData['item']['controller_name_api']; // Например, KontaktyApiController
            $apiNamespace = $this->moduleData['namespace']['use']['controller_api']; // Например, Modules\Kontakty\Controllers\Api\KontaktyApiController
            
            Log::info('Создание API роутов модуля', [
                'module' => $this->moduleData['code_module'],
                'api_controller' => $apiControllerName,
                'api_namespace' => $apiNamespace
            ]);
            
            // Формируем путь к файлу API роутов
            $routePathFile = $this->moduleRouterFullPath . '/api.php';
            
            // Проверяем существование файла
            if (File::exists($routePathFile)) {
                Log::warning('Файл API роутов уже существует, будет перезаписан', [
                    'file_path' => $routePathFile
                ]);
            }
            
            // Формируем контент API роутов
            $content = "<?php\n\n";
            $content .= "use Illuminate\Support\Facades\Route;\n";
            $content .= "use {$apiNamespace};\n\n";
            $content .= "Route::get('/api/" . $this->moduleData['code_module'] . "', [{$apiControllerName}::class, 'index']);\n";
            $content .= "Route::get('/{id}', [{$apiControllerName}::class, 'show']);\n";

            // Записываем изменения в файл
            File::put($routePathFile, $content);
            
            Log::info('Файл API роутов успешно сгенерирован', [
                'file_path' => $routePathFile,
                'api_controller' => $apiControllerName,
                'api_namespace' => $apiNamespace
            ]);
            
            return true;

        } catch (\Exception $e) {
            Log::error('Ошибка при создании API роутов модуля', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'controller_name' => $apiControllerName ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception("Ошибка создания API роутов: " . $e->getMessage(), 0, $e);
        }
    }
}