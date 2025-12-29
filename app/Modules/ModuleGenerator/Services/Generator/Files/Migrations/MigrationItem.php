<?php

namespace App\Modules\ModuleGenerator\Services\Generator\Files\Migrations;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Core\Services\FieldTypeTransformer;

/**
 * Сервис для генерации миграций основной таблицы для модулей
 * 
 * @param array $moduleData Настройки модулей
 * @param string $moduleMigrationFullPath абсолютный путь к директории модуля миграций
 * @param string $moduleMigrationPath путь для создания миграций
 */

class MigrationItem
{
    protected $moduleData;
    protected $moduleMigrationFullPath;
    protected $moduleMigrationPath;
    protected $fieldTypeTransformer;

    public function __construct($moduleData, $moduleMigrationFullPath, $moduleMigrationPath)
    {
        $this->moduleData = $moduleData;
        $this->moduleMigrationFullPath = $moduleMigrationFullPath;
        $this->moduleMigrationPath = $moduleMigrationPath;
        $this->fieldTypeTransformer = new FieldTypeTransformer();
    }

    public function generate()
    {
        try {
            Log::info('Начало генерации миграции основной таблицы модуля', [
                'module' => $this->moduleData['code_module']
            ]);

            // Генерируем миграцию для основной таблицы
            $this->createMigrationItem();

            Log::info('Успешное завершение генерации миграции основной таблицы модуля', [
                'module' => $this->moduleData['code_module']
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Критическая ошибка при генерации миграции основной таблицы модуля', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \RuntimeException("Ошибка генерации миграции основной таблицы: " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Преобразует UI-тип в метод Laravel Blueprint
     * 
     * @param string $uiType UI-тип поля
     * @param array $property Массив свойств поля
     * @return string Метод Blueprint и параметры
     */
    private function transformToBlueprintMethod(string $uiType, array $property): string
    {
        $dbType = $this->fieldTypeTransformer->transformForDatabase($uiType);
        
        // Маппинг типов БД на методы Blueprint с параметрами
        $blueprintMap = [
            'string' => "string('{$property['code']}')",
            'text' => "text('{$property['code']}')",
            'integer' => "integer('{$property['code']}')",
            'decimal' => "decimal('{$property['code']}', 10, 2)",
        ];
        
        return $blueprintMap[$dbType] ?? "string('{$property['code']}')";
    }
    
    /**
     * Создаём миграцию для основной таблицы модуля
     */
    public function createMigrationItem()
    {
        try {
            // Используем готовые имена из массива item
            $tableName = $this->moduleData['item']['table_name'];
            $migrationName = $this->moduleData['item']['migration_name'];
            
            Log::info('Создание миграции для основной таблицы модуля', [
                'module' => $this->moduleData['code_module'],
                'table_name' => $tableName,
                'migration_name' => $migrationName
            ]);
            
            // Создаем файл миграции через Artisan
            Artisan::call('make:migration', [
                'name' => $migrationName,
                '--create' => $tableName,
                '--path' => $this->moduleMigrationPath
            ]);
            
            Log::debug('Artisan команда make:migration выполнена', [
                'output' => Artisan::output()
            ]);
            
            // Ищем созданный файл миграции
            $migrationFiles = File::files($this->moduleMigrationFullPath);
            $latestMigration = collect($migrationFiles)
                ->filter(fn($file) => str_contains($file->getFilename(), $migrationName))
                ->sortByDesc(fn($file) => $file->getCTime())
                ->first();
            
            if (!$latestMigration) {
                Log::error('Файл миграции не найден после выполнения Artisan команды', [
                    'migration_name' => $migrationName,
                    'files_found' => count($migrationFiles)
                ]);
                
                throw new \Exception("Не удалось найти созданный файл миграции: " . $migrationName);
            }
            
            Log::info('Файл миграции найден', [
                'file_path' => $latestMigration->getPathname(),
                'file_name' => $latestMigration->getFilename()
            ]);
            
            // Генерируем код для столбцов на основе properties
            $columnsCode = "";
            if (isset($this->moduleData['properties']) && !empty($this->moduleData['properties'])) {
                Log::debug('Обработка свойств модуля для генерации столбцов', [
                    'properties_count' => count($this->moduleData['properties'])
                ]);
                
                foreach ($this->moduleData['properties'] as $index => $property) {
                    if (empty($property['code'] ?? '')) {
                        Log::warning('Пропущено свойство без кода', [
                            'property_index' => $index,
                            'property' => $property
                        ]);
                        continue;
                    }
                    
                    $columnName = $property['code'];
                    $columnType = $property['type'] ?? 'string';
                    $isRequired = $property['required'] ?? false;
                    
                    // Получаем метод Blueprint с помощью FieldTypeTransformer
                    $blueprintMethod = $this->transformToBlueprintMethod($columnType, $property);
                    
                    // Формируем строку столбца
                    $columnLine = "            \$table->{$blueprintMethod}";
                    if (!$isRequired) {
                        $columnLine .= "->nullable()";
                    }
                    $columnLine .= ";\n";
                    
                    $columnsCode .= $columnLine;
                    
                    Log::debug('Сгенерирован столбец', [
                        'column_name' => $columnName,
                        'type' => $columnType,
                        'blueprint_method' => $blueprintMethod,
                        'required' => $isRequired
                    ]);
                }
            } else {
                Log::warning('Модуль не содержит свойств (properties) для генерации столбцов', [
                    'module' => $this->moduleData['code_module']
                ]);
            }
            
            // Добавляем поле author_id
            $authorIdColumn = "            \$table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();\n";
            
            // Добавляем SEO поля если нужно
            $seoColumns = "";
            $sectionSeo = $this->moduleData['option']['seo'] ?? false;
            if ($sectionSeo) {
                Log::info('SEO опция включена, добавляем SEO поля', [
                    'module' => $this->moduleData['code_name']
                ]);
                
                $seoColumns = "            \$table->string('slug')->nullable();\n" .
                            "            \$table->string('meta_title')->nullable();\n" .
                            "            \$table->text('meta_description')->nullable();\n" .
                            "            \$table->string('meta_keywords')->nullable();\n";
            }
            
            // Добавляем softDeletes если включена опция trash
            $softDeletesColumn = "";
            $sectionTrash = $this->moduleData['option']['trash'] ?? false;
            if ($sectionTrash) {
                Log::info('Trash опция включена, добавляем softDeletes', [
                    'module' => $this->moduleData['code_name']
                ]);
                
                $softDeletesColumn = "            \$table->softDeletes();\n";
            }
            
            // Полностью перезаписываем файл миграции
            $newContent = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Миграция для основной таблицы модуля {$this->moduleData['code_name']}
     * Создает таблицу {$tableName} с пользовательскими полями, SEO и мягким удалением
     */
    public function up(): void
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            \$table->id();
{$columnsCode}{$authorIdColumn}{$seoColumns}
            \$table->timestamps();
{$softDeletesColumn}
        });
    }

    /**
     * Откат миграции - удаление таблицы
     */
    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
};
PHP;
            
            // Записываем изменения
            File::put($latestMigration->getPathname(), $newContent);
            
            Log::info('Файл миграции успешно сгенерирован', [
                'file_path' => $latestMigration->getPathname(),
                'table_name' => $tableName
            ]);
            
            // Выполняем миграцию
            Log::debug('Запуск миграции в базу данных', [
                'migration_path' => $this->moduleMigrationPath . '/' . $latestMigration->getFilename()
            ]);
            
            Artisan::call('migrate', [
                '--path' => $this->moduleMigrationPath . '/' . $latestMigration->getFilename()
            ]);
            
            $migrationOutput = Artisan::output();
            Log::info('Миграция успешно выполнена в базу данных', [
                'output' => $migrationOutput,
                'table_created' => $tableName
            ]);
            
            return true;

        } catch (\Exception $e) {
            Log::error('Ошибка при создании миграции для основной таблицы модуля', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'table_name' => $tableName ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception("Ошибка создания миграции: " . $e->getMessage(), 0, $e);
        }
    }
}