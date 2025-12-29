<?php

namespace App\Modules\ModuleGenerator\Services\Generator\Files\Routes;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Modules\ModuleGenerator\Services\Generator\Files\Routes\RouteItem;
use App\Modules\ModuleGenerator\Services\Generator\Files\Routes\RouteApi;

/**
 * Основной класс для управления генерацией роутов модулей
 * 
 * Создает директорию для роутов и вызывает генерацию web и API роутов
 * 
 * @param array $moduleData Настройки модулей
 * @param string $moduleRouterFullPath абсолютный путь к директории роутов модуля
 */
class Routes
{
    protected $moduleData;
    protected $moduleRouterFullPath;

    public function __construct($moduleData)
    {
        $this->moduleData = $moduleData;
    }

    /**
     * Основной метод генерации всех роутов модуля
     * 
     * Создает директорию и вызывает генерацию web и API роутов
     * 
     * @return bool Возвращает true при успешной генерации всех роутов
     */
    public function generate()
    {
        try {
            Log::info('Начало генерации роутов для модуля', [
                'module' => $this->moduleData['code_module']
            ]);

            // Создание структуры директорий
            $this->moduleRouterFullPath = $this->ensureModulesRouterDir();

            // Генерируем web роуты
            $this->generateRouteItem();

            // Генерируем API роуты
            $this->generateRouteApi();

            Log::info('Успешное завершение генерации роутов для модуля', [
                'module' => $this->moduleData['code_module'],
                'has_api' => isset($this->moduleData['option']['api']) && $this->moduleData['option']['api']
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Критическая ошибка при генерации роутов для модуля', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \RuntimeException("Ошибка генерации роутов: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Создает или проверяет существование директории для роутов модуля
     * 
     * Директория создается по пути: modules/nameModule/routes
     * 
     * @return string Абсолютный путь к директории роутов
     */
    public function ensureModulesRouterDir()
    {
        // Формируем путь к роутам модуля
        $moduleRouterPath = $this->moduleData['path']['full_base_module'] . '/routes';

        if (!File::exists($moduleRouterPath)) {
            try {
                Log::debug('Создание директории для роутов модуля', [
                    'path' => $moduleRouterPath,
                    'module' => $this->moduleData['code_module']
                ]);
                
                // Создаём директорию для модуля
                File::makeDirectory($moduleRouterPath, 0755, true);
                
                Log::info('Директория для роутов успешно создана', [
                    'path' => $moduleRouterPath
                ]);
            } catch (\Exception $e) {
                $moduleDataCodeName = $this->moduleData['code_name'];
                
                Log::error('Ошибка создания директории для роутов модуля', [
                    'module' => $moduleDataCodeName,
                    'path' => $moduleRouterPath,
                    'error' => $e->getMessage()
                ]);
                
                throw new \RuntimeException("Не удалось создать директорию для роутов модуля '{$moduleDataCodeName}' по пути: {$moduleRouterPath}", 0, $e);
            }
        } else {
            Log::debug('Директория для роутов уже существует', [
                'path' => $moduleRouterPath
            ]);
        }
        
        return $moduleRouterPath;
    }

    /**
     * Генерация web роутов модуля
     * 
     * Создает экземпляр RouteItem и передает ему путь к директории роутов
     * 
     * @return bool Возвращает true при успешной генерации
     */
    protected function generateRouteItem()
    {
        try {
            Log::info('Вызов генерации web роутов модуля', [
                'module' => $this->moduleData['code_module'],
                'router_full_path' => $this->moduleRouterFullPath
            ]);

            // Создаем экземпляр RouteItem с передачей путей
            $routeItem = new RouteItem(
                $this->moduleData,
                $this->moduleRouterFullPath
            );
            
            return $routeItem->generate();

        } catch (\Exception $e) {
            Log::error('Ошибка при вызове генерации web роутов', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \RuntimeException("Ошибка генерации web роутов: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Генерация API роутов модуля
     * 
     * Создает экземпляр RouteApi и передает ему путь к директории роутов
     * 
     * @return bool Возвращает true при успешной генерации
     */
    protected function generateRouteApi()
    {
        try {
            Log::info('Вызов генерации API роутов модуля', [
                'module' => $this->moduleData['code_module'],
                'router_full_path' => $this->moduleRouterFullPath
            ]);

            // Создаем экземпляр RouteApi с передачей путей
            $routeApi = new RouteApi(
                $this->moduleData,
                $this->moduleRouterFullPath
            );
            
            return $routeApi->generate();

        } catch (\Exception $e) {
            Log::error('Ошибка при вызове генерации API роутов', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \RuntimeException("Ошибка генерации API роутов: " . $e->getMessage(), 0, $e);
        }
    }
}