<?php

namespace App\Modules\ModuleGenerator\Services\Generator\Files\Models;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Modules\ModuleGenerator\Services\Generator\Files\Models\ModelItem;

/**
 * Основной класс для управления генерацией моделей модулей
 * 
 * Создает директорию для моделей и вызывает генерацию основной модели
 * 
 * @param array $moduleData Настройки модулей
 * @param string $moduleModelFullPath абсолютный путь к директории модуля моделей
 */
class Model
{
    protected $moduleData;
    protected $moduleModelFullPath;

    public function __construct($moduleData)
    {
        $this->moduleData = $moduleData;
    }

    /**
     * Основной метод генерации всех моделей модуля
     * 
     * Создает директорию и вызывает генерацию основной модели
     * 
     * @return bool Возвращает true при успешной генерации всех моделей
     */
    public function generate()
    {
        try {
            Log::info('Начало генерации моделей для модуля', [
                'module' => $this->moduleData['code_module']
            ]);

            // Создание структуры директорий
            $this->moduleModelFullPath = $this->ensureModulesModelDir();

            // Генерируем основную модель
            $this->generateModelItem();

            Log::info('Успешное завершение генерации моделей для модуля', [
                'module' => $this->moduleData['code_module']
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Критическая ошибка при генерации моделей для модуля', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \RuntimeException("Ошибка генерации моделей: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Создает или проверяет существование директории для моделей модуля
     * 
     * Директория создается по пути: modules/nameModule/Models
     * 
     * @return string Абсолютный путь к директории моделей
     */
    public function ensureModulesModelDir()
    {
        // Формируем путь к моделям модуля
        $moduleModelPath = $this->moduleData['path']['full_base_module'] . '/Models';

        if (!File::exists($moduleModelPath)) {
            try {
                Log::debug('Создание директории для моделей модуля', [
                    'path' => $moduleModelPath,
                    'module' => $this->moduleData['code_module']
                ]);
                
                // Создаём директорию для модуля
                File::makeDirectory($moduleModelPath, 0755, true);
                
                Log::info('Директория для моделей успешно создана', [
                    'path' => $moduleModelPath
                ]);
            } catch (\Exception $e) {
                $moduleDataCodeName = $this->moduleData['code_name'];
                
                Log::error('Ошибка создания директории для моделей модуля', [
                    'module' => $moduleDataCodeName,
                    'path' => $moduleModelPath,
                    'error' => $e->getMessage()
                ]);
                
                throw new \RuntimeException("Не удалось создать директорию для модели модуля '{$moduleDataCodeName}' по пути: {$moduleModelPath}", 0, $e);
            }
        } else {
            Log::debug('Директория для моделей уже существует', [
                'path' => $moduleModelPath
            ]);
        }
        
        return $moduleModelPath;
    }

    /**
     * Генерация основной модели модуля
     * 
     * Создает экземпляр ModelItem и передает ему путь к директории моделей
     * 
     * @return bool Возвращает true при успешной генерации
     */
    protected function generateModelItem()
    {
        try {
            Log::info('Вызов генерации основной модели модуля', [
                'module' => $this->moduleData['code_module'],
                'model_full_path' => $this->moduleModelFullPath
            ]);

            // Создаем экземпляр ModelItem с передачей путей
            $modelItem = new ModelItem(
                $this->moduleData,
                $this->moduleModelFullPath
            );
            
            return $modelItem->generate();

        } catch (\Exception $e) {
            Log::error('Ошибка при вызове генерации основной модели', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \RuntimeException("Ошибка генерации основной модели: " . $e->getMessage(), 0, $e);
        }
    }
}