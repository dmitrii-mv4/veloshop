<?php

namespace App\Modules\ModuleGenerator\Services\Generator\Files\Migrations;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для генерации миграций переводов для модулей
 * 
 * Создает таблицу переводов с автоматическим заполнением базовых переводов модуля
 * 
 * @param array $moduleData Настройки модулей
 * @param string $moduleMigrationFullPath абсолютный путь к директории модуля миграций
 * @param string $moduleMigrationPath путь для создания миграций
 */
class MigrationTrans
{
    protected $moduleData;
    protected $moduleMigrationFullPath;
    protected $moduleMigrationPath;

    public function __construct($moduleData, $moduleMigrationFullPath, $moduleMigrationPath)
    {
        $this->moduleData = $moduleData;
        $this->moduleMigrationFullPath = $moduleMigrationFullPath;
        $this->moduleMigrationPath = $moduleMigrationPath;
    }

    public function generate()
    {
        try {
            Log::info('Начало генерации миграции переводов для модуля', [
                'module' => $this->moduleData['code_module']
            ]);

            // Генерируем миграцию для переводов
            return $this->createMigrationTrans();

        } catch (\Exception $e) {
            Log::error('Критическая ошибка при генерации миграции переводов', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \RuntimeException("Ошибка генерации миграции переводов: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Создаём миграцию для переводов
     */
    public function createMigrationTrans()
    {
        try {
            // Используем готовые имена из массива trans
            $tableName = $this->moduleData['trans']['table_name'];
            $migrationName = $this->moduleData['trans']['migration_name'];
            
            Log::info('Создание миграции для таблицы переводов модуля', [
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
            
            Log::debug('Artisan команда make:migration выполнена для таблицы переводов', [
                'output' => Artisan::output()
            ]);
            
            // Ищем созданный файл миграции
            $migrationFiles = File::files($this->moduleMigrationFullPath);
            $latestMigration = collect($migrationFiles)
                ->filter(fn($file) => str_contains($file->getFilename(), $migrationName))
                ->sortByDesc(fn($file) => $file->getCTime())
                ->first();
            
            if (!$latestMigration) {
                Log::error('Файл миграции переводов не найден после выполнения Artisan команды', [
                    'migration_name' => $migrationName,
                    'files_found' => count($migrationFiles)
                ]);
                
                throw new \Exception("Не удалось найти созданный файл миграции: " . $migrationName);
            }
            
            Log::info('Файл миграции переводов найден', [
                'file_path' => $latestMigration->getPathname(),
                'file_name' => $latestMigration->getFilename()
            ]);
            
            // Формируем данные для вставки
            $insertData = [
                [
                    'code' => 'mod_name',
                    'ru' => $this->moduleData['mod_name']['ru'],
                    'en' => $this->moduleData['mod_name']['en']
                ],
                [
                    'code' => 'mod_description',
                    'ru' => $this->moduleData['mod_description']['ru'],
                    'en' => $this->moduleData['mod_description']['en']
                ]
            ];
            
            // Добавляем свойства (поля) модуля
            foreach ($this->moduleData['properties'] as $property) {
                $insertData[] = [
                    'code' => $property['code'],
                    'ru' => $property['name']['ru'],
                    'en' => $property['name']['en']
                ];
            }
            
            Log::debug('Сформированы данные для вставки в таблицу переводов', [
                'records_count' => count($insertData),
                'module' => $this->moduleData['code_module']
            ]);
            
            // Преобразуем массив данных в PHP-код для вставки в миграцию
            $dataCode = "[\n";
            foreach ($insertData as $index => $data) {
                $dataCode .= "            [\n";
                $dataCode .= "                'code' => '" . addslashes($data['code']) . "',\n";
                $dataCode .= "                'ru' => '" . addslashes($data['ru']) . "',\n";
                $dataCode .= "                'en' => '" . addslashes($data['en']) . "',\n";
                $dataCode .= "                'created_at' => now(),\n";
                $dataCode .= "                'updated_at' => now(),\n";
                $dataCode .= "            ]";
                
                if ($index < count($insertData) - 1) {
                    $dataCode .= ",";
                }
                $dataCode .= "\n";
            }
            $dataCode .= "        ]";
            
            // Полностью перезаписываем файл миграции
            $newContent = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Создание таблицы переводов для модуля {$this->moduleData['code_name']}
     * Таблица содержит переводы для названия модуля, описания и всех его свойств
     * Хранится в БД для последующего использования в интерфейсе администратора
     */
    public function up(): void
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            \$table->id();
            \$table->string('code')->unique()->comment('Уникальный код перевода');
            \$table->string('ru', 250)->nullable()->comment('Перевод на русском языке');
            \$table->string('en', 250)->nullable()->comment('Перевод на английском языке');
            \$table->timestamps();
            \$table->softDeletes();
            
            \$table->index('code');
        });

        // Автоматическое заполнение таблицы базовыми переводами модуля
        DB::table('{$tableName}')->insert({$dataCode});
    }

    /**
     * Удаление таблицы переводов при откате миграции
     */
    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
};
PHP;
            
            // Записываем изменения
            File::put($latestMigration->getPathname(), $newContent);
            
            Log::info('Файл миграции переводов успешно сгенерирован', [
                'file_path' => $latestMigration->getPathname(),
                'table_name' => $tableName
            ]);
            
            // Выполняем миграцию
            Log::debug('Запуск миграции переводов в базу данных', [
                'migration_path' => $this->moduleMigrationPath . '/' . $latestMigration->getFilename()
            ]);
            
            Artisan::call('migrate', [
                '--path' => $this->moduleMigrationPath . '/' . $latestMigration->getFilename()
            ]);
            
            $migrationOutput = Artisan::output();
            Log::info('Миграция переводов успешно выполнена в базу данных', [
                'output' => $migrationOutput,
                'table_created' => $tableName
            ]);
            
            return true;

        } catch (\Exception $e) {
            Log::error('Ошибка при создании миграции для таблицы переводов модуля', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'table_name' => $tableName ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception("Ошибка создания миграции переводов: " . $e->getMessage(), 0, $e);
        }
    }
}