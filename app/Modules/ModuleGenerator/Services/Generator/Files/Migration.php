<?php

namespace App\Modules\ModuleGenerator\Services\Generator\Files;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Modules\ModuleGenerator\Services\Generator\ModuleConfigService;

/**
 * Сервис для генерации миграций для модулей
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

    public function generate()
    {
        // Создание структуры директорий
        $this->moduleMigrationFullPath = $this->ensureModulesMigrationDir();

        // Создаём путь для создания миграций
        $this->moduleMigrationPath = $this->moduleData['path']['modules'] . $this->moduleData['path']['migration'];

        // Генеририуем миграции 
        $this->createMigrationItem();
    }

    /**
     * Создает или проверяет существование директории для миграций модуля
     * 
     * Директория создается по пути: modules/nameModule/database/migrations/modules
     * 
     */
    private function ensureModulesMigrationDir()
    {
        // Формируем путь к миграция модуля
        $moduleMigrationFullPath = $this->moduleData['path']['full_base_module'] . $this->moduleData['path']['migration'];

        if (!File::exists($moduleMigrationFullPath))
        {
            try {
                // Создаём директорию для модуля
                File::makeDirectory($moduleMigrationFullPath, 0755, true);
            } catch (\Exception $e)
            {
                $moduleDataCode = $this->moduleData['code_module'];

                throw new \RuntimeException("Не удалось создать директорию для миграций модуля '{$moduleDataCode}' по пути: {$moduleMigrationFullPath}", 0, $e);
            }
        }
        return $moduleMigrationFullPath;
    }
    
    /**
     * Создаём миграцию для записей
     */
    public function createMigrationItem()
    {
        // Используем готовые имена из массива item
        $tableName = $this->moduleData['item']['table_name'];
        $migrationName = $this->moduleData['item']['migration_name'];
        
        // Создаем файл миграции через Artisan
        Artisan::call('make:migration', [
            'name' => $migrationName,
            '--create' => $tableName,
            '--path' => $this->moduleMigrationPath
        ]);
        
        // Ищем созданный файл миграции
        $migrationFiles = File::files($this->moduleMigrationFullPath);
        $latestMigration = collect($migrationFiles)
            ->filter(fn($file) => str_contains($file->getFilename(), $migrationName))
            ->sortByDesc(fn($file) => $file->getCTime())
            ->first();
        
        if (!$latestMigration) {
            throw new \Exception("Не удалось найти созданный файл миграции: " . $migrationName);
        }
        
        // Генерируем код для столбцов на основе properties
        $columnsCode = "";
        if (isset($this->moduleData['properties']) && !empty($this->moduleData['properties'])) {
            foreach ($this->moduleData['properties'] as $property) {
                if (empty($property['code'] ?? '')) continue;
                
                $columnName = $property['code'];
                $columnType = $property['type'] ?? 'string';
                $isRequired = $property['required'] ?? false;
                
                // Преобразуем тип для Laravel
                $typeMap = [
                    'string' => 'string', 'text' => 'text', 'integer' => 'integer',
                    'int' => 'integer', 'float' => 'float', 'boolean' => 'boolean',
                    'date' => 'date', 'datetime' => 'dateTime', 'json' => 'json'
                ];
                
                $laravelType = $typeMap[$columnType] ?? 'string';
                
                // Формируем строку столбца
                $columnLine = "            \$table->{$laravelType}('{$columnName}')";
                if (!$isRequired) $columnLine .= "->nullable()";
                $columnLine .= ";\n";
                
                $columnsCode .= $columnLine;
            }
        }
        
        // Добавляем SEO поля если нужно
        $seoColumns = "";
        $sectionSeo = $this->moduleData['connection_section']['seo'] ?? false;
        if ($sectionSeo) {
            $seoColumns = "            \$table->string('slug')->nullable();\n" .
                        "            \$table->string('meta_title')->nullable();\n" .
                        "            \$table->text('meta_description')->nullable();\n" .
                        "            \$table->string('meta_keywords')->nullable();\n";
            \Log::info('section_seo включен для модуля: ' . $this->moduleData['code_name']);
        }
        
        // Полностью перезаписываем файл миграции
        $newContent = <<<PHP
    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('{$tableName}', function (Blueprint \$table) {
                \$table->id();
    {$columnsCode}{$seoColumns}
                \$table->timestamps();
                \$table->softDeletes();
            });
        }

        public function down(): void
        {
            Schema::dropIfExists('{$tableName}');
        }
    };
    PHP;
        
        // Записываем изменения
        File::put($latestMigration->getPathname(), $newContent);
        
        // Выполняем миграцию
        Artisan::call('migrate', [
            '--path' => $this->moduleMigrationPath . '/' . $latestMigration->getFilename()
        ]);
        
        return true;
    }
}