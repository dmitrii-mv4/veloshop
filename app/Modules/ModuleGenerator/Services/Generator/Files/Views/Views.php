<?php

namespace App\Modules\ModuleGenerator\Services\Generator\Files\Views;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Modules\ModuleGenerator\Services\Generator\Files\Views\Index;

/**
 * Основной класс для управления генерацией views модулей
 * 
 * Создает директорию для views и вызывает генерацию всех view файлов
 * 
 * @param array $moduleData Настройки модулей
 * @param string $moduleViewsFullPath абсолютный путь к директории views модуля
 */
class Views
{
    protected $moduleData;
    protected $moduleViewsFullPath;

    public function __construct($moduleData)
    {
        $this->moduleData = $moduleData;
    }

    /**
     * Основной метод генерации всех views модуля
     * 
     * Создает директорию и вызывает генерацию view файлов
     * Возвращает массив с именами сгенерированных view файлов
     * 
     * @return array Массив с именами view файлов
     */
    public function generate()
    {
        try {
            Log::info('Начало генерации views для модуля', [
                'module' => $this->moduleData['code_module']
            ]);

            // Создание структуры директорий
            $this->moduleViewsFullPath = $this->ensureModulesViewsDir();

            // Генерируем index view и получаем его имя
            $indexViewName = $this->generateIndex();

            Log::info('Успешное завершение генерации views для модуля', [
                'module' => $this->moduleData['code_module']
            ]);

            // Упаковываем в массив названия views файлы
            $viewNamesData = [
                'indexViewName' => $indexViewName,
            ];

            return $viewNamesData;

        } catch (\Exception $e) {
            Log::error('Критическая ошибка при генерации views для модуля', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \RuntimeException("Ошибка генерации views: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Создает или проверяет существование директории для views файлов модуля
     * 
     * Директория создается по пути: modules/nameModule/views
     * 
     * @return string Абсолютный путь к директории views
     */
    public function ensureModulesViewsDir()
    {
        // Формируем путь к views модуля
        $moduleViewsPath = $this->moduleData['path']['full_base_module'] . '/views';

        if (!File::exists($moduleViewsPath)) {
            try {
                Log::debug('Создание директории для views модуля', [
                    'path' => $moduleViewsPath,
                    'module' => $this->moduleData['code_module']
                ]);
                
                // Создаём директорию для модуля
                File::makeDirectory($moduleViewsPath, 0755, true);
                
                Log::info('Директория для views успешно создана', [
                    'path' => $moduleViewsPath
                ]);
            } catch (\Exception $e) {
                $moduleDataCode = $this->moduleData['code_module'];
                
                Log::error('Ошибка создания директории для views модуля', [
                    'module' => $moduleDataCode,
                    'path' => $moduleViewsPath,
                    'error' => $e->getMessage()
                ]);
                
                throw new \RuntimeException("Не удалось создать директорию для views модуля '{$moduleDataCode}' по пути: {$moduleViewsPath}", 0, $e);
            }
        } else {
            Log::debug('Директория для views уже существует', [
                'path' => $moduleViewsPath
            ]);
        }
        
        return $moduleViewsPath;
    }

    /**
     * Генерация index view модуля
     * 
     * Создает экземпляр Index и передает ему путь к директории views
     * 
     * @return string Имя сгенерированного view (например: "katalog::index")
     */
    protected function generateIndex()
    {
        try {
            Log::info('Вызов генерации index view модуля', [
                'module' => $this->moduleData['code_module'],
                'views_full_path' => $this->moduleViewsFullPath
            ]);

            // Создаем экземпляр Index с передачей путей
            $indexView = new Index(
                $this->moduleData,
                $this->moduleViewsFullPath
            );
            
            // Получаем имя view из класса Index
            $indexViewName = $indexView->generate();
            
            Log::info('Успешно получено имя index view', [
                'module' => $this->moduleData['code_module'],
                'view_name' => $indexViewName
            ]);
            
            return $indexViewName;

        } catch (\Exception $e) {
            Log::error('Ошибка при вызове генерации index view', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \RuntimeException("Ошибка генерации index view: " . $e->getMessage(), 0, $e);
        }
    }
}