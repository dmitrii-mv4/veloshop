<?php

namespace App\Modules\ModuleGenerator\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Получаем префикс таблиц из конфигурации
        $tablePrefix = config('database.connections.' . config('database.default') . '.prefix', '');
        
        return [
            // Мультиязычные поля - Основные
            'name.ru' => 'required|string|min:3|max:255',
            'name.en' => 'nullable|string|min:3|max:255',
            
            'description.ru' => 'nullable|string|max:2000',
            'description.en' => 'nullable|string|max:2000',
            
            // Обязательные поля
            'slug' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('modules', 'slug')
            ],
            
            'code_module' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('modules', 'code_module')
            ],
            
            // Статус
            'status' => 'required|boolean',
            
            // Секции модуля (булевы значения)
            'section_seo' => 'sometimes|boolean',
            'section_categories' => 'sometimes|boolean',
            'section_tags' => 'sometimes|boolean',
            'section_comments' => 'sometimes|boolean',
            
            // SEO настройки
            'meta_title' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_img_alt' => 'nullable|string|max:255',
            'meta_img_title' => 'nullable|string|max:255',
            
            // Свойства модуля - обязательный массив с минимум 1 элементом
            'properties' => [
                'required',
                'array',
                'min:1',
                function ($attribute, $value, $fail) {
                    // Проверяем уникальность кодов свойств внутри массива
                    $codes = [];
                    foreach ($value as $index => $property) {
                        if (isset($property['code'])) {
                            $code = strtolower(trim($property['code']));
                            if (in_array($code, $codes)) {
                                $fail("Свойство #" . ($index + 1) . ": Код '{$property['code']}' уже используется другим свойством в этом модуле.");
                            }
                            $codes[] = $code;
                        }
                    }
                }
            ],
            
            // Правила для каждого свойства
            'properties.*.name.ru' => 'required|string|min:2|max:100',
            'properties.*.name.en' => 'nullable|string|min:2|max:100',
            
            'properties.*.type' => [
                'required',
                Rule::in([
                    'string', 'text', 
                    'integer', 'bigInteger', 'float', 'decimal',
                    'date', 'datetime', 'time', 'timestamp',
                    'boolean'
                ])
            ],
            
            'properties.*.code' => [
                'required',
                'string',
                'min:2',
                'max:50',
                'regex:/^[a-z_][a-z0-9_]*$/'
            ],
            
            'properties.*.required' => 'sometimes|boolean',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {        
        // Генерируем slug из русского названия, если не указан
        if (!$this->filled('slug') && $this->filled('name.ru')) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->input('name.ru'))
            ]);
        }
        
        // Обрабатываем свойства
        $properties = $this->input('properties', []);
        foreach ($properties as $index => &$property) {
            // Генерируем код свойства из русского названия, если не указан
            if (empty($property['code']) && !empty($property['name']['ru'])) {
                $property['code'] = $this->generatePropertyCode($property['name']['ru']);
            }
            
            // Приводим код к нижнему регистру
            if (isset($property['code'])) {
                $property['code'] = strtolower($property['code']);
            }
            
            // Приводим required к boolean
            $property['required'] = isset($property['required']) ? (bool)$property['required'] : false;
        }
        
        $this->merge([
            // 'status' => $status,
            'properties' => $properties,
            
            // Приводим все boolean поля к правильному типу
            'section_seo' => (bool)$this->input('section_seo', false),
            'section_categories' => (bool)$this->input('section_categories', false),
            'section_tags' => (bool)$this->input('section_tags', false),
            'section_comments' => (bool)$this->input('section_comments', false),
        ]);
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name.ru' => 'название модуля (русский)',
            'name.en' => 'название модуля (английский)',
            'slug' => 'URL-адрес',
            'code_module' => 'код модуля',
            'description.ru' => 'описание модуля (русский)',
            'description.en' => 'описание модуля (английский)',
            'properties' => 'свойства модуля',
            'properties.*.name.ru' => 'название свойства (русский)',
            'properties.*.name.en' => 'название свойства (английский)',
            'properties.*.type' => 'тип данных',
            'properties.*.code' => 'код свойства',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            // Общие сообщения
            'required' => 'Поле ":attribute" обязательно для заполнения.',
            'min' => 'Поле ":attribute" должно содержать не менее :min символов.',
            'max' => 'Поле ":attribute" должно содержать не более :max символов.',
            'unique' => 'Такое значение поля ":attribute" уже существует.',
            'regex' => 'Поле ":attribute" содержит недопустимые символы.',
            'array' => 'Поле ":attribute" должно быть массивом.',
            'in' => 'Выбранное значение для ":attribute" некорректно.',
            
            // Специфичные сообщения для полей модуля
            'name.ru.required' => 'Название модуля на русском языке обязательно для заполнения.',
            'name.ru.min' => 'Название модуля должно содержать не менее :min символов.',
            
            'slug.required' => 'URL-адрес модуля обязателен для заполнения.',
            'slug.regex' => 'URL-адрес может содержать только латинские буквы в нижнем регистре, цифры и дефисы.',
            'slug.unique' => 'Такой URL-адрес уже используется другим модулем.',
            
            'code_module.required' => 'Код модуля обязателен для заполнения.',
            'code_module.regex' => 'Код модуля может содержать только латинские буквы в нижнем регистре, цифры и подчеркивания.',
            'code_module.unique' => 'Такой код модуля уже используется.',
            
            'status.required' => 'Статус модуля обязателен для заполнения.',
            'status.boolean' => 'Статус должен быть "Активен" или "Неактивен".',
            
            // Сообщения для свойств
            'properties.required' => 'Добавьте хотя бы одно свойство для модуля.',
            'properties.min' => 'Добавьте хотя бы одно свойство для модуля.',
            
            'properties.*.name.ru.required' => 'Название свойства на русском языке обязательно для заполнения.',
            'properties.*.name.ru.min' => 'Название свойства должно содержать не менее :min символов.',
            
            'properties.*.code.required' => 'Код свойства обязателен для заполнения.',
            'properties.*.code.regex' => 'Код свойства может содержать только латинские буквы в нижнем регистре, цифры и подчеркивания.',
        ];
    }

    /**
     * Generate property code from Russian name.
     */
    private function generatePropertyCode(string $russianName): string
    {
        // Транслитерация русских букв
        $translitMap = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'shch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        ];
        
        $code = mb_strtolower($russianName, 'UTF-8');
        $code = strtr($code, $translitMap);
        $code = preg_replace('/[^a-z0-9_\s]/', '', $code);
        $code = preg_replace('/\s+/', '_', $code);
        $code = preg_replace('/_+/', '_', $code);
        
        return trim($code, '_');
    }
}