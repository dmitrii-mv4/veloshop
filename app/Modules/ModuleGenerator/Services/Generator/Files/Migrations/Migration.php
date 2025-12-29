<?php

namespace App\Modules\ModuleGenerator\Services\Generator\Files\Migrations;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Modules\ModuleGenerator\Services\Generator\Files\Migrations\MigrationItem;
use App\Modules\ModuleGenerator\Services\Generator\Files\Migrations\MigrationTrans;

/**
 * Основной класс для управления генерацией миграций модулей
 * 
 * Создает директорию для миграций и вызывает генерацию основной таблицы и таблицы переводов
 * 
 * @param array $moduleData Настройки модулей
 * @param string $moduleMigrationFullPath абсолютный путь к директории модуля миграций
 * @param string $moduleMigrationPath путь для создания миграций
 */
class Migration
{
    protected $moduleData;
    protected $moduleMigrationFullPath;
    protected $moduleMigrationPath;

    public function __construct($moduleData)
    {
        $this->moduleData = $moduleData;
    }

    /**
     * Основной метод генерации всех миграций модуля
     * 
     * Создает директорию и вызывает генерацию основной таблицы и таблицы переводов
     * 
     * @return bool Возвращает true при успешной генерации всех миграций
     */
    public function generate()
    {
        try {
            Log::info('Начало генерации всех миграций для модуля', [
                'module' => $this->moduleData['code_module']
            ]);

            // Создание структуры директорий
            $this->moduleMigrationFullPath = $this->ensureModulesMigrationDir();

            // Создаём путь для создания миграций
            $this->moduleMigrationPath = $this->moduleData['path']['modules'] . $this->moduleData['path']['migration'];

            // Генерируем миграцию для основной таблицы
            $this->generateMigrationItem();

            // Генерируем миграцию для переводов
            $this->generateMigrationTrans();

            Log::info('Успешное завершение генерации всех миграций для модуля', [
                'module' => $this->moduleData['code_module']
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Критическая ошибка при генерации всех миграций для модуля', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \RuntimeException("Ошибка генерации всех миграций: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Создает или проверяет существование директории для миграций модуля
     * 
     * Директория создается по пути: modules/nameModule/database/migrations
     * 
     * @return string Абсолютный путь к директории миграций
     */
    public function ensureModulesMigrationDir()
    {
        // Формируем путь к миграциям модуля
        $moduleMigrationFullPath = $this->moduleData['path']['full_base_module'] . $this->moduleData['path']['migration'];

        if (!File::exists($moduleMigrationFullPath)) {
            try {
                Log::debug('Создание директории для миграций модуля', [
                    'path' => $moduleMigrationFullPath,
                    'module' => $this->moduleData['code_module']
                ]);
                
                // Создаём директорию для модуля
                File::makeDirectory($moduleMigrationFullPath, 0755, true);
                
                Log::info('Директория для миграций успешно создана', [
                    'path' => $moduleMigrationFullPath
                ]);
            } catch (\Exception $e) {
                $moduleDataCode = $this->moduleData['code_module'];
                
                Log::error('Ошибка создания директории для миграций модуля', [
                    'module' => $moduleDataCode,
                    'path' => $moduleMigrationFullPath,
                    'error' => $e->getMessage()
                ]);
                
                throw new \RuntimeException("Не удалось создать директорию для миграций модуля '{$moduleDataCode}' по пути: {$moduleMigrationFullPath}", 0, $e);
            }
        } else {
            Log::debug('Директория для миграций уже существует', [
                'path' => $moduleMigrationFullPath
            ]);
        }
        
        return $moduleMigrationFullPath;
    }

    /**
     * Генерация миграции для основной таблицы модуля
     * 
     * Создает экземпляр MigrationItem и передает ему путь к директории миграций
     * 
     * @return bool Возвращает true при успешной генерации
     */
    protected function generateMigrationItem()
    {
        try {
            Log::info('Вызов генерации миграции основной таблицы модуля', [
                'module' => $this->moduleData['code_module'],
                'migration_full_path' => $this->moduleMigrationFullPath,
                'migration_path' => $this->moduleMigrationPath
            ]);

            // Создаем экземпляр MigrationItem с передачей путей
            $migrationItem = new MigrationItem(
                $this->moduleData,
                $this->moduleMigrationFullPath,
                $this->moduleMigrationPath
            );
            
            return $migrationItem->generate();

        } catch (\Exception $e) {
            Log::error('Ошибка при вызове генерации миграции основной таблицы', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \RuntimeException("Ошибка генерации миграции основной таблицы: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Генерация миграции для таблицы переводов модуля
     * 
     * Создает экземпляр MigrationTrans и передает ему путь к директории миграций
     * 
     * @return bool Возвращает true при успешной генерации
     */
    protected function generateMigrationTrans()
    {
        try {
            Log::info('Вызов генерации миграции таблицы переводов модуля', [
                'module' => $this->moduleData['code_module'],
                'migration_full_path' => $this->moduleMigrationFullPath,
                'migration_path' => $this->moduleMigrationPath
            ]);

            // Создаем экземпляр MigrationTrans с передачей путей
            $migrationTrans = new MigrationTrans(
                $this->moduleData,
                $this->moduleMigrationFullPath,
                $this->moduleMigrationPath
            );
            
            return $migrationTrans->generate();

        } catch (\Exception $e) {
            Log::error('Ошибка при вызове генерации миграции таблицы переводов', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \RuntimeException("Ошибка генерации миграции таблицы переводов: " . $e->getMessage(), 0, $e);
        }
    }
}