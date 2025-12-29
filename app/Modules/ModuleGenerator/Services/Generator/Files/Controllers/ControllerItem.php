<?php

namespace App\Modules\ModuleGenerator\Services\Generator\Files\Controllers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для генерации контроллеров для модулей
 * 
 * Создает CRUD систему в контроллере для административной части модулей.
 * 
 * @param array $moduleData Настройки модулей
 * @param string $moduleControllerPath путь к директории модуля модели
 * @param array $viewNamesData Название views файлов
 * @param string $controllerPath относительный путь к директории модуля контроллера
 */
class ControllerItem
{
    protected $moduleData;
    protected $moduleControllerPath;
    protected $controllerPath;
    protected $searchableFields = [];

    public function __construct($moduleData)
    {
        $this->moduleData = $moduleData;
        $this->prepareSearchableFields();
    }

    /**
     * Подготавливает массив полей для поиска
     * 
     * Определяет, какие поля из properties подходят для поиска
     */
    private function prepareSearchableFields()
    {
        $this->searchableFields = [];
        
        if (!isset($this->moduleData['properties']) || !is_array($this->moduleData['properties'])) {
            return;
        }

        // Определяем типы полей, которые подходят для поиска
        $searchableTypes = ['string', 'text', 'varchar', 'char', 'tinytext', 'mediumtext', 'longtext'];
        
        foreach ($this->moduleData['properties'] as $property) {
            if (in_array($property['type'], $searchableTypes)) {
                $this->searchableFields[] = $property['code'];
            }
        }
        
        // Всегда добавляем системные поля для поиска
        $this->searchableFields[] = 'slug';
    }

    /**
     * Генерирует строку с условиями поиска для контроллера
     * 
     * @return string Сгенерированный код условий поиска
     */
    private function generateSearchConditions()
    {
        if (empty($this->searchableFields)) {
            return "            // Нет полей для поиска\n";
        }
        
        $conditions = "            \$query->where(function(\$q) use (\$search) {\n";
        
        foreach ($this->searchableFields as $index => $field) {
            if ($index === 0) {
                $conditions .= "                \$q->where('$field', 'LIKE', \"%{\$search}%\")\n";
            } else {
                $conditions .= "                  ->orWhere('$field', 'LIKE', \"%{\$search}%\")\n";
            }
        }
        
        $conditions .= "                ;\n            });\n";
        
        return $conditions;
    }

    /**
     * Генерирует метод index для контроллера
     * 
     * @param string $indexViewName Название view для index
     * @return string Сгенерированный код метода index
     */
    private function generateIndexMethod($indexViewName)
    {
        $searchConditions = $this->generateSearchConditions();
        
        return <<<PHP
    /**
     * Метод показа модуля главной страницы
     * 
     * Отображает список записей с поддержкой поиска, сортировки и пагинации
     */
    public function index(Request \$request): View
    {
        try {
            \$query = {$this->moduleData['item']['model_name']}::query();

            // Поиск
            if (\$request->has('search') && \$request->search) {
                \$search = \$request->search;
{$searchConditions}
            }

            // Сортировка
            \$sortBy = \$request->get('sort_by', 'created_at');
            \$sortOrder = \$request->get('sort_order', 'desc');
            \$query->orderBy(\$sortBy, \$sortOrder);

            // Пагинация
            \$perPage = \$request->get('per_page', 10);
            \$items = \$query->paginate(\$perPage);

            // Статистика
            \$totalItems = {$this->moduleData['item']['model_name']}::count();
            \$trashedCount = {$this->moduleData['item']['model_name']}::onlyTrashed()->count();

            return view('{$indexViewName}', [
                'items' => \$items,
                'totalItems' => \$totalItems,
                'trashedCount' => \$trashedCount,
                'search' => \$request->search ?? '',
                'sortBy' => \$sortBy,
                'sortOrder' => \$sortOrder,
                'perPage' => \$perPage,
                'moduleName' => \$this->codeModule,
                'properties' => \$this->moduleData['properties'] ?? []
            ]);

        } catch (\\Exception \$e) {
            Log::error('Ошибка при получении списка записей модуля {\$this->codeModule}', [
                'error' => \$e->getMessage(),
                'trace' => \$e->getTraceAsString()
            ]);
            
            return view('{$indexViewName}', [
                'items' => collect(),
                'totalItems' => 0,
                'trashedCount' => 0,
                'search' => '',
                'sortBy' => 'created_at',
                'sortOrder' => 'desc',
                'perPage' => 10,
                'moduleName' => \$this->codeModule,
                'properties' => \$this->moduleData['properties'] ?? []
            ]);
        }
    }
PHP;
    }

    public function generate($viewNamesData)
    {
        // Преобразовываем массив названий файлов views в переменные 
        $indexViewName = $viewNamesData['indexViewName'];

        // Создание структуры директорий
        $this->moduleControllerPath = $this->ensureModulesControllerDir();

        // Генерируем контроллер
        $this->createController($indexViewName);
    }

    /**
     * Создает или проверяет существование директории для контроллера модуля
     * 
     * Директория создается по пути: modules/nameModule/Controllers
     */
    private function ensureModulesControllerDir()
    {
        // Формируем путь к модулю
        $moduleControllerPath = base_path($this->moduleData['path']['controller']);

        if (!File::exists($moduleControllerPath))
        {
            try {
                // Создаем директорию модуля
                File::makeDirectory($moduleControllerPath, 0755, true);
            } catch (\Exception $e)
            {
                $moduleDataCodeName = $this->moduleData['code_name'];

                throw new \RuntimeException("Не удалось создать директорию для модели модуля '{$moduleDataCodeName}' по пути: {$moduleControllerPath}", 0, $e);
            }
        }
        return $moduleControllerPath;
    }

    public function createController($indexViewName)
    {
        // Формируем имя файла контроллера с полным именем файла
        $controllerPathFile = base_path($this->moduleData['path']['controller'] . '/' . $this->moduleData['item']['controller_name'] . '.php');

        // Генерируем метод index
        $indexMethod = $this->generateIndexMethod($indexViewName);

        // Создаем содержимое контроллера
        $content = "<?php

namespace {$this->moduleData['namespace']['controller']};

use App\Core\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\View\View;
use {$this->moduleData['namespace']['use']['model']};

/**
 * Контроллер модуля {$this->moduleData['code_name']}
 * 
 * Создает CRUD систему в контроллере для административной части модулей.
 * 
 * @param string \$currentTableName Название таблицы текущего модуля
 * @param string \$codeModule Код модуля, например news
 * @param array \$searchableFields Массив полей, доступных для поиска
 */

class {$this->moduleData['item']['controller_name']} extends Controller
{
    protected \$currentTableName;
    protected \$codeModule;
    protected \$searchableFields;

    public function __construct()
    {
        // Получаем название таблицы модели
        \$this->currentTableName = {$this->moduleData['item']['model_name']}::getModel()->getTable();

        // Получаем код модуля из названия таблицы
        \$this->codeModule = str_replace('_', '', \$this->currentTableName);
        
        // Инициализируем поля для поиска
        \$this->searchableFields = [\n";
        
        // Добавляем searchableFields в конструктор
        foreach ($this->searchableFields as $field) {
            $content .= "            '$field',\n";
        }
        
        $content .= "        ];
    }

{$indexMethod}
}";

        // Записываем изменения в файл
        File::put($controllerPathFile, $content);
    }
}