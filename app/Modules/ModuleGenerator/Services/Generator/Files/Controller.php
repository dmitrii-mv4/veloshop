<?php

namespace App\Modules\ModuleGenerator\Services\Generator\Files;

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

class Controller
{
    protected $moduleData;
    protected $moduleControllerPath;
    protected $controllerPath;

    public function __construct($moduleData)
    {
        $this->moduleData = $moduleData;
    }

    public function generate($viewNamesData)
    {
        // Преобразовываем массив названий файлов views в переменные 
        $indexViewName = $viewNamesData['indexViewName'];
        //$createViewName = $viewNamesData['createViewName'];
        //$updateViewName = $viewNamesData['updateViewName'];

        // Создание структуры директорий
        $this->moduleControllerPath = $this->ensureModulesControllerDir();

        // Генеририуем контроллер
        $this->createController($indexViewName);

        // Если включены категории
        // if ($this->moduleData['section_categories'] == true)
        // {
        //     // Преобразовываем массив названий файлов views в переменные 
        //     $indexCategoryViewName = $viewNamesData['indexCategoryViewName'];
        //     $createCategoryViewName = $viewNamesData['createCategoryViewName'];
        //     $editCategoryViewName = $viewNamesData['editCategoryViewName'];

        //     $this->createControllerCategory($indexCategoryViewName, $createCategoryViewName, $editCategoryViewName);
        // }
    }

    /**
     * Создает или проверяет существование директории для контроллера модуля
     * 
     * Директория создается по пути: modules/nameModule/Controllers
     * 
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

        // Определяем, нужно ли подключать категории
        //$hasCategories = '';

        // if ($this->moduleData['section_categories'] == true)
        // {
        //     $hasCategories = 'use ' . $this->moduleData['namespace']['use']['model_category'] . ';';
        // }

        // Создаем содержимое контроллера
        $content = "<?php

namespace {$this->moduleData['namespace']['controller']};

use App\Core\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Modules\ModuleGenerator\Models\ModulesModel;
use {$this->moduleData['namespace']['use']['request_create']};
use {$this->moduleData['namespace']['use']['request_update']};
use {$this->moduleData['namespace']['use']['model']};

/**
 * Контроллер модуля {$this->moduleData['code_name']}
 * 
 * Создает CRUD систему в контроллере для административной части модулей.
 * 
 * @param string \$currentTableName Название таблицы текущего модуля
 * @param string \$codeModule Код модуля, например news
 */

class {$this->moduleData['item']['controller_name']} extends Controller
{

    protected \$currentTableName;
    protected \$codeModule;

    public function __construct()
    {
        // Получаем название таблицы модели
        \$this->currentTableName = {$this->moduleData['item']['model_name']}::getModel()->getTable();

        // Получаем код модуля
        
    }

    /**
     * Метод показа модуля гланой страницы
     */
    public function index(): View
    {
        // Выводим все записи
        \$items = {$this->moduleData['item']['model_name']}::orderBy('id')->paginate(10);

        return view('{$indexViewName}', compact('items'));
    }
}";

        // Записываем изменения в файл
        File::put($controllerPathFile, $content);
    }
}