<?php

namespace App\Modules\ModuleGenerator\Services\Generator\Files;

use App\Modules\ModuleGenerator\Services\ModuleConfigService;
use Illuminate\Support\Facades\File;


/**
 * Сервис для генерации моделей для модулей
 * 
 * @param array $moduleData Настройки модулей
 * @param string $moduleViewsPath путь к директории модуля модели
 */

class Model
{
    protected $moduleData;
    protected $moduleModelPath;

    public function __construct($moduleData)
    {
        $this->moduleData = $moduleData;
    }

    public function generate()
    {
        // Создание структуры директорий
        $this->moduleModelPath = $this->ensureModulesModelDir();

        // Генеририуем модели 
        $this->createModel();
    }

    /**
     * Создает или проверяет существование директории для моделей модуля
     * 
     * Директория создается по пути: modules/nameModule/Model
     * 
     */
    private function ensureModulesModelDir()
    {
        // Формируем путь к модулю
        $moduleModelPath = base_path($this->moduleData['path']['model']);

        if (!File::exists($moduleModelPath))
        {
            try {
                // Создаем директорию модуля
                File::makeDirectory($moduleModelPath, 0755, true);
            } catch (\Exception $e)
            {
                $moduleDataCodeName = $this->moduleData['code_name'];

                throw new \RuntimeException("Не удалось создать директорию для модели модуля '{$moduleDataCodeName}' по пути: {$moduleModelPath}", 0, $e);
            }
        }
        return $moduleModelPath;
    }

    /**
     * Создаём модель для модуля с записями
     */
    public function createModel()
    {
        // Определяем пути
        $modelDirPath = $this->moduleData['path']['full_base_module'] . '/Models';
        $modelFilePath = $modelDirPath . '/' . $this->moduleData['item']['model_name'] . '.php';
        
        // Создаём директорию
        if (!File::exists($modelDirPath)) {
            File::makeDirectory($modelDirPath, 0755, true);
        }
        
        // Формируем fillable поля
        $fillableFields = [];
        
        // Добавляем поля из properties
        if (isset($this->moduleData['properties']) && is_array($this->moduleData['properties'])) {
            foreach ($this->moduleData['properties'] as $property) {
                if (!empty($property['code'] ?? '')) {
                    $fillableFields[] = $property['code'];
                }
            }
        }
        
        // Добавляем SEO поля
        $sectionSeo = $this->moduleData['connection_section']['seo'] ?? false;
        if ($sectionSeo) {
            $fillableFields = array_merge($fillableFields, [
                'slug',
                'meta_title', 
                'meta_description',
                'meta_keywords'
            ]);
        }
        
        // Формируем строку fillable
        $fillableString = "";
        if (!empty($fillableFields)) {
            $fillableLines = array_map(fn($field) => "        '{$field}',", $fillableFields);
            $fillableString = implode("\n", $fillableLines);
        }
        
        // Генерируем содержимое модели
        $namespace = 'Modules\\' . $this->moduleData['code_name'] . '\\Models';
        $modelName = $this->moduleData['item']['model_name'];
        $tableName = $this->moduleData['item']['table_name'];
        
        $content = <<<PHP
    <?php

    namespace {$namespace};

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\SoftDeletes;

    class {$modelName} extends Model
    {
        use SoftDeletes;

        protected \$table = '{$tableName}';
        protected \$guarded = false;
        
        protected \$fillable = [
    {$fillableString}
        ];
    }
    PHP;
        
        // Создаём файл
        File::put($modelFilePath, $content);
        
        if (!File::exists($modelFilePath)) {
            throw new \Exception("Файл модели не создан: " . $modelFilePath);
        }
        
        return true;
    }
}