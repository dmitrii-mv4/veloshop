<?php

namespace App\Modules\ModuleGenerator\Services\Generator\Files\Models;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для генерации основной модели для модулей
 * 
 * @param array $moduleData Настройки модулей
 * @param string $moduleModelFullPath абсолютный путь к директории модуля моделей
 */
class ModelItem
{
    protected $moduleData;
    protected $moduleModelFullPath;

    public function __construct($moduleData, $moduleModelFullPath)
    {
        $this->moduleData = $moduleData;
        $this->moduleModelFullPath = $moduleModelFullPath;
    }

    public function generate()
    {
        try {
            Log::info('Начало генерации основной модели модуля', [
                'module' => $this->moduleData['code_module']
            ]);

            // Генерируем основную модель
            $this->createModelItem();

            Log::info('Успешное завершение генерации основной модели модуля', [
                'module' => $this->moduleData['code_module']
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Критическая ошибка при генерации основной модели модуля', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \RuntimeException("Ошибка генерации основной модели: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Создаём основную модель модуля
     */
    public function createModelItem()
    {
        try {
            // Используем готовые имена из массива item
            $modelName = $this->moduleData['item']['model_name'];
            $tableName = $this->moduleData['item']['table_name'];
            
            Log::info('Создание основной модели модуля', [
                'module' => $this->moduleData['code_module'],
                'model_name' => $modelName,
                'table_name' => $tableName
            ]);
            
            // Формируем путь к файлу модели
            $modelPathFile = $this->moduleModelFullPath . '/' . $modelName . '.php';
            
            // Проверяем существование файла
            if (File::exists($modelPathFile)) {
                Log::warning('Файл модели уже существует, будет перезаписан', [
                    'file_path' => $modelPathFile
                ]);
            }
            
            // Определяем свойства для fillable
            $fillableFields = [];
            if (isset($this->moduleData['properties']) && !empty($this->moduleData['properties'])) {
                Log::debug('Обработка свойств модуля для fillable', [
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
                    
                    $fillableFields[] = $property['code'];
                    Log::debug('Добавлено поле в fillable', [
                        'field' => $property['code']
                    ]);
                }
            } else {
                Log::warning('Модуль не содержит свойств (properties) для fillable', [
                    'module' => $this->moduleData['code_module']
                ]);
            }
            
            // Добавляем SEO поля если нужно
            $seoFields = [];
            $sectionSeo = $this->moduleData['option']['seo'] ?? false;
            if ($sectionSeo) {
                Log::info('SEO опция включена, добавляем SEO поля в fillable', [
                    'module' => $this->moduleData['code_name']
                ]);
                
                $seoFields = ['slug', 'meta_title', 'meta_description', 'meta_keywords'];
                $fillableFields = array_merge($fillableFields, $seoFields);
                
                Log::debug('Добавлены SEO поля', [
                    'seo_fields' => $seoFields
                ]);
            }
            
            // Преобразуем массив fillable в строку
            $fillableString = '';
            foreach ($fillableFields as $index => $field) {
                if ($index === 0) {
                    $fillableString .= "            '{$field}',\n";
                } elseif ($index === count($fillableFields) - 1) {
                    $fillableString .= "            '{$field}',\n";
                } else {
                    $fillableString .= "            '{$field}',\n";
                }
            }
            
            // Добавляем author_id в fillable
            $fillableString .= "            'author_id',\n";
            Log::debug('Добавлено поле author_id в fillable');
            
            // Определяем использование softDeletes
            $useSoftDeletes = '';
            $useSoftDeletesTrait = '';
            $sectionTrash = $this->moduleData['option']['trash'] ?? false;
            if ($sectionTrash) {
                Log::info('Trash опция включена, добавляем softDeletes', [
                    'module' => $this->moduleData['code_name']
                ]);
                
                $useSoftDeletes = "use Illuminate\Database\Eloquent\SoftDeletes;\n";
                $useSoftDeletesTrait = "use SoftDeletes;\n";
            }
            
            // Определяем использование HasTranslations если есть свойства с переводами
            $useHasTranslations = '';
            $useHasTranslationsTrait = '';
            
            // Генерируем связи для author
            $authorRelationship = "
    /**
     * Связь с пользователем, создавшим запись
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return \$this->belongsTo(User::class, 'author_id');
    }";
            
            // Создаем содержимое модели
            $content = "<?php

namespace {$this->moduleData['namespace']['model']};

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Modules\User\Models\User;

{$useSoftDeletes}
{$useHasTranslations}

/**
 * Модель модуля {$this->moduleData['code_name']}
 * 
 * @table {$tableName}
 * 
 * @property int \$id Идентификатор записи
 * @property int|null \$author_id Идентификатор пользователя, создавшего запись
 * @property \Illuminate\Support\Carbon \$created_at Дата создания
 * @property \Illuminate\Support\Carbon \$updated_at Дата обновления
 */
class {$modelName} extends Model
{
    use HasFactory;
    {$useSoftDeletesTrait}{$useHasTranslationsTrait}
    
    /**
     * Название таблицы в базе данных
     *
     * @var string
     */
    protected \$table = '{$tableName}';

    /**
     * Поля, доступные для массового заполнения
     *
     * @var array
     */
    protected \$fillable = [
{$fillableString}    ];

    /**
     * Поля, которые должны быть скрыты при сериализации
     *
     * @var array
     */
    protected \$hidden = [];

    /**
     * Типы атрибутов для автоматического приведения типов
     *
     * @var array
     */
    protected \$casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
{$authorRelationship}
}";

            // Записываем изменения в файл
            File::put($modelPathFile, $content);
            
            Log::info('Файл модели успешно сгенерирован', [
                'file_path' => $modelPathFile,
                'model_name' => $modelName,
                'table_name' => $tableName,
                'has_soft_deletes' => $sectionTrash,
                'has_seo_fields' => $sectionSeo
            ]);
            
            return true;

        } catch (\Exception $e) {
            Log::error('Ошибка при создании основной модели модуля', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'model_name' => $modelName ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception("Ошибка создания модели: " . $e->getMessage(), 0, $e);
        }
    }
}