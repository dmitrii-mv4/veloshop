<?php

namespace App\Modules\ModuleGenerator\Services\Generator\Files;

use App\Core\Services\ValidationTypeMapper;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class Request
{
    protected $moduleData;
    protected $moduleRequestsPath;

    public function __construct($moduleData)
    {
        $this->moduleData = $moduleData;
        
        // Инициализируем путь к директории Requests
        $this->moduleRequestsPath = $this->moduleData['path']['full_base_module'] . '/Requests';
    }

    /**
     * Создает или проверяет существование директории для моделей Request
     */
    public function generate()
    {        
        try {
            // Создаём директорию Requests
            if (!File::exists($this->moduleRequestsPath)) {
                File::makeDirectory($this->moduleRequestsPath, 0755, true);
            }
            
            // Генерируем оба типа Request файлов
            $this->generateRequestFile('create');
            $this->generateRequestFile('update');
            
            // Возвращаем результат вместо null
            return ['success' => true];
            
        } catch (\Exception $e) {
            Log::error('Ошибка при генерации Request файлов', [
                'module' => $this->moduleData['code_name'],
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Генерирует Request файл
     */
    private function generateRequestFile(string $type): void
    {
        $fileName = $type === 'create' 
            ? $this->moduleData['item']['request_name_create'] 
            : $this->moduleData['item']['request_name_update'];
        
        $filePath = $this->moduleRequestsPath . '/' . $fileName . '.php';
        
        // Формируем namespace
        $namespace = 'Modules\\' . $this->moduleData['code_name'] . '\\Requests';
        
        // Генерируем правила валидации
        $rules = $this->generateValidationRules($type);
        $rulesString = implode("\n", $rules);
        
        // Генерируем человекочитаемые названия
        $attributes = $this->generateAttributeNames();
        $attributesString = implode("\n", $attributes);
        
        // Генерируем кастомные сообщения об ошибках
        $messages = $this->generateValidationMessages($type, $attributes);
        $messagesString = implode("\n", $messages);
        
        // Создаём содержимое файла
        $content = <<<PHP
<?php

namespace {$namespace};

use Illuminate\Foundation\Http\FormRequest;

class {$fileName} extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
{$rulesString}
        ];
    }

    public function attributes(): array
    {
        return [
{$attributesString}
        ];
    }

    public function messages(): array
    {
        return [
{$messagesString}
        ];
    }
}
PHP;
        
        // Записываем файл
        File::put($filePath, $content);
        
        Log::info('Request файл создан', [
            'file' => $fileName,
            'path' => $filePath
        ]);
    }

    /**
     * Генерирует правила валидации на основе properties
     */
    private function generateValidationRules(string $type): array
    {
        $rules = [];
        
        // Поля из properties
        if (isset($this->moduleData['properties'])) {
            foreach ($this->moduleData['properties'] as $property) {
                if (empty($property['code'])) continue;
                
                $field = $property['code'];
                $fieldType = $property['type'] ?? 'string';
                $isRequired = $property['required'] ?? false;
                
                // Определяем правила
                $fieldRules = [];
                
                // Правило required/sometimes
                if ($type === 'create' && $isRequired) {
                    $fieldRules[] = 'required';
                } else {
                    $fieldRules[] = 'sometimes';
                    if (!$isRequired) {
                        $fieldRules[] = 'nullable';
                    }
                }
                
                // Правило типа через сервис
                $fieldRules[] = ValidationTypeMapper::toValidationRule($fieldType);
                
                // Дополнительные правила для строковых типов
                if (ValidationTypeMapper::isStringType($fieldType)) {
                    $maxLength = ValidationTypeMapper::getStringMaxLength($fieldType);
                    $fieldRules[] = "max:{$maxLength}";
                }
                
                $rules[] = "            '{$field}' => '" . implode('|', $fieldRules) . "',";
            }
        }
        
        // SEO поля
        $sectionSeo = $this->moduleData['connection_section']['seo'] ?? false;
        if ($sectionSeo) {
            $seoFields = [
                'slug' => 'sometimes|string|max:255|nullable',
                'meta_title' => 'sometimes|string|max:255|nullable',
                'meta_description' => 'sometimes|string|max:500|nullable',
                'meta_keywords' => 'sometimes|string|max:255|nullable',
            ];
            
            foreach ($seoFields as $field => $fieldRule) {
                $rules[] = "            '{$field}' => '{$fieldRule}',";
            }
        }
        
        return $rules;
    }

    /**
     * Генерирует человекочитаемые названия для атрибутов
     */
    private function generateAttributeNames(): array
    {
        $attributes = [];
        
        // Поля из properties
        if (isset($this->moduleData['properties'])) {
            foreach ($this->moduleData['properties'] as $property) {
                if (empty($property['code'])) continue;
                
                $field = $property['code'];
                $nameRu = $property['name']['ru'] ?? $field;
                $attributes[] = "            '{$field}' => '{$nameRu}',";
            }
        }
        
        // SEO поля
        $sectionSeo = $this->moduleData['connection_section']['seo'] ?? false;
        if ($sectionSeo) {
            $seoAttributes = [
                'slug' => 'URL-адрес',
                'meta_title' => 'Meta заголовок',
                'meta_description' => 'Meta описание',
                'meta_keywords' => 'Ключевые слова',
            ];
            
            foreach ($seoAttributes as $field => $name) {
                $attributes[] = "            '{$field}' => '{$name}',";
            }
        }
        
        return $attributes;
    }

    /**
     * Генерирует кастомные сообщения об ошибках валидации
     */
    private function generateValidationMessages(string $type, array $attributes): array
    {
        $messages = [];
        
        // Преобразуем атрибуты в ассоциативный массив для удобства
        $attributeMap = [];
        foreach ($attributes as $attributeLine) {
            if (preg_match("/'([^']+)' => '([^']+)'/", $attributeLine, $matches)) {
                $attributeMap[$matches[1]] = $matches[2];
            }
        }
        
        // Поля из properties
        if (isset($this->moduleData['properties'])) {
            foreach ($this->moduleData['properties'] as $property) {
                if (empty($property['code'])) continue;
                
                $field = $property['code'];
                $fieldType = $property['type'] ?? 'string';
                $isRequired = $property['required'] ?? false;
                $fieldName = $attributeMap[$field] ?? $field;
                
                // Сообщения для required
                if ($type === 'create' && $isRequired) {
                    $messages[] = "            '{$field}.required' => 'Поле \"{$fieldName}\" обязательно для заполнения.',";
                }
                
                // Сообщения для типа поля через сервис
                $validationType = ValidationTypeMapper::toValidationRule($fieldType);
                $typeMessages = [
                    'string' => 'Поле ":attribute" должно быть строкой.',
                    'integer' => 'Поле ":attribute" должно быть целым числом.',
                    'numeric' => 'Поле ":attribute" должно быть числом.',
                    'boolean' => 'Поле ":attribute" должно быть логическим значением (true/false).',
                    'date' => 'Поле ":attribute" должно быть корректной датой.',
                    'array' => 'Поле ":attribute" должно быть массивом.',
                    'file' => 'Поле ":attribute" должно быть файлом.',
                    'image' => 'Поле ":attribute" должно быть изображением.',
                    'email' => 'Поле ":attribute" должно быть корректным email адресом.',
                    'url' => 'Поле ":attribute" должно быть корректным URL.',
                ];
                
                if (isset($typeMessages[$validationType])) {
                    $messages[] = "            '{$field}.{$validationType}' => '{$typeMessages[$validationType]}',";
                }
                
                // Сообщения для максимальной длины
                if (ValidationTypeMapper::isStringType($fieldType)) {
                    $messages[] = "            '{$field}.max' => 'Поле \"{$fieldName}\" не должно превышать :max символов.',";
                }
                
                // Общие сообщения для nullable
                $messages[] = "            '{$field}.nullable' => 'Поле \"{$fieldName}\" может быть пустым.',";
                
                // Общие сообщения для sometimes
                $messages[] = "            '{$field}.sometimes' => 'Поле \"{$fieldName}\" может быть не указано при обновлении.',";
            }
        }
        
        // SEO поля
        $sectionSeo = $this->moduleData['connection_section']['seo'] ?? false;
        if ($sectionSeo) {
            $seoFields = [
                'slug' => 'URL-адрес',
                'meta_title' => 'Meta заголовок',
                'meta_description' => 'Meta описание',
                'meta_keywords' => 'Ключевые слова',
            ];
            
            foreach ($seoFields as $field => $fieldName) {
                // Общие сообщения для SEO полей
                $messages[] = "            '{$field}.string' => 'Поле \"{$fieldName}\" должно быть строкой.',";
                
                // Сообщения для максимальной длины
                $max = ($field === 'meta_description') ? 500 : 255;
                $messages[] = "            '{$field}.max' => 'Поле \"{$fieldName}\" не должно превышать {$max} символов.',";
                
                // Общие сообщения
                $messages[] = "            '{$field}.nullable' => 'Поле \"{$fieldName}\" может быть пустым.',";
                $messages[] = "            '{$field}.sometimes' => 'Поле \"{$fieldName}\" может быть не указано при обновлении.',";
            }
        }
        
        // Убираем дубликаты
        $messages = array_unique($messages);
        
        return $messages;
    }
}