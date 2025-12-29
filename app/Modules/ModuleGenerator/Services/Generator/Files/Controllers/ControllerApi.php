<?php

namespace App\Modules\ModuleGenerator\Services\Generator\Files\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для генерации API контроллеров для модулей
 * 
 * Создает API контроллер для модулей с методом index возвращающим JSON
 * 
 * @param array $moduleData Настройки модулей
 */
class ControllerApi
{
    /**
     * @var array Данные модуля для генерации
     */
    protected $moduleData;

    /**
     * Конструктор класса
     * 
     * @param array $moduleData Данные модуля
     */
    public function __construct($moduleData)
    {
        $this->moduleData = $moduleData;
    }

    /**
     * Основной метод генерации API контроллера
     * 
     * @return bool Возвращает true при успешной генерации
     */
    public function generate()
    {
        try {
            Log::info('Начало генерации API контроллера для модуля', [
                'module' => $this->moduleData['code_module'],
                'controller_name' => $this->moduleData['item']['controller_name_api']
            ]);

            // Создание структуры директорий
            $this->ensureModulesApiControllerDir();

            // Генерируем API контроллер
            $this->createApiController();

            Log::info('Успешное завершение генерации API контроллера', [
                'module' => $this->moduleData['code_module'],
                'controller_name' => $this->moduleData['item']['controller_name_api']
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Критическая ошибка при генерации API контроллера для модуля', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'controller_name' => $this->moduleData['item']['controller_name_api'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \RuntimeException("Ошибка генерации API контроллера: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Создает или проверяет существование директории для API контроллеров модуля
     * 
     * Директория создается по пути: modules/nameModule/Controllers/Api
     * 
     * @return string Абсолютный путь к директории API контроллеров
     */
    private function ensureModulesApiControllerDir()
    {
        // Формируем путь к API контроллерам модуля
        $moduleApiControllerPath = $this->moduleData['path']['full_base_module'] . '/Controllers/Api';

        if (!File::exists($moduleApiControllerPath)) {
            try {
                Log::debug('Создание директории для API контроллеров модуля', [
                    'path' => $moduleApiControllerPath,
                    'module' => $this->moduleData['code_module']
                ]);
                
                // Создаём директорию для API контроллеров модуля
                File::makeDirectory($moduleApiControllerPath, 0755, true);
                
                Log::info('Директория для API контроллеров успешно создана', [
                    'path' => $moduleApiControllerPath
                ]);
            } catch (\Exception $e) {
                $moduleDataCodeName = $this->moduleData['code_name'];
                
                Log::error('Ошибка создания директории для API контроллеров модуля', [
                    'module' => $moduleDataCodeName,
                    'path' => $moduleApiControllerPath,
                    'error' => $e->getMessage()
                ]);
                
                throw new \RuntimeException("Не удалось создать директорию для API контроллеров модуля '{$moduleDataCodeName}' по пути: {$moduleApiControllerPath}", 0, $e);
            }
        } else {
            Log::debug('Директория для API контроллеров уже существует', [
                'path' => $moduleApiControllerPath
            ]);
        }
        
        return $moduleApiControllerPath;
    }

    /**
     * Создаёт API контроллер модуля
     * 
     * Генерирует файл API контроллера с методом index возвращающим JSON
     */
    public function createApiController()
    {
        try {
            $controllerName = $this->moduleData['item']['controller_name_api'];
            $modelName = $this->moduleData['item']['model_name'];
            $moduleName = $this->moduleData['code_name'];
            $moduleCode = $this->moduleData['code_module'];
            
            Log::info('Создание API контроллера модуля', [
                'module' => $moduleCode,
                'controller_name' => $controllerName,
                'model_name' => $modelName,
                'module_name' => $moduleName
            ]);
            
            // Формируем путь к файлу API контроллера
            $controllerPathFile = $this->moduleData['path']['full_base_module'] . '/Controllers/Api/' . $controllerName . '.php';
            
            // Проверяем существование файла
            if (File::exists($controllerPathFile)) {
                Log::warning('Файл API контроллера уже существует, будет перезаписан', [
                    'file_path' => $controllerPathFile
                ]);
            }
            
            // Создаем содержимое API контроллера
            $content = $this->generateApiControllerContent($controllerName, $modelName, $moduleName);
            
            // Записываем изменения в файл
            File::put($controllerPathFile, $content);
            
            Log::info('Файл API контроллера успешно сгенерирован', [
                'file_path' => $controllerPathFile,
                'controller_name' => $controllerName,
                'model_name' => $modelName
            ]);
            
            return true;

        } catch (\Exception $e) {
            Log::error('Ошибка при создании API контроллера модуля', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'controller_name' => $controllerName ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception("Ошибка создания API контроллера: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Генерирует содержимое API контроллера
     * 
     * @param string $controllerName Имя контроллера (например, KatalogApiController)
     * @param string $modelName Имя модели (например, Katalog)
     * @param string $moduleName Имя модуля (например, Katalog)
     * @return string Сгенерированный код контроллера
     */
    private function generateApiControllerContent($controllerName, $modelName, $moduleName)
    {
        // Определяем, нужно ли использовать soft deletes
        $hasTrash = isset($this->moduleData['option']['trash']) && $this->moduleData['option']['trash'];
        
        $content = "<?php\n\n";
        $content .= "namespace {$this->moduleData['namespace']['controller_api']};\n\n";
        $content .= "use App\Core\Controllers\Controller;\n";
        $content .= "use Illuminate\Http\JsonResponse;\n";
        $content .= "use Illuminate\Support\Facades\Schema;\n";
        $content .= "use {$this->moduleData['namespace']['use']['model']};\n\n";
        
        $content .= "/**\n";
        $content .= " * API контроллер модуля {$moduleName}\n";
        $content .= " * \n";
        $content .= " * Предоставляет доступ к данным модуля через API\n";
        $content .= " */\n";
        
        $content .= "class {$controllerName} extends Controller\n";
        $content .= "{\n";
        $content .= "    /**\n";
        $content .= "     * Получение всех записей модуля\n";
        $content .= "     * \n";
        $content .= "     * @return JsonResponse JSON ответ с данными\n";
        $content .= "     */\n";
        $content .= "    public function index(): JsonResponse\n";
        $content .= "    {\n";
        
        if ($hasTrash) {
            $content .= "        // Получаем только активные записи (без удаленных в корзину)\n";
            $content .= "        \$items = {$modelName}::all();\n";
        } else {
            $content .= "        // Получаем все записи\n";
            $content .= "        \$items = {$modelName}::all();\n";
        }
        
        $content .= "\n";
        $content .= "        // Если записи не найдены\n";
        $content .= "        if (\$items->isEmpty()) {\n";
        $content .= "            return response()->json([\n";
        $content .= "                '{$moduleName}' => [\n";
        $content .= "                    \n";
        $content .= "                ]\n";
        $content .= "            ]);\n";
        $content .= "        }\n";
        $content .= "\n";
        $content .= "        // Преобразуем в массив и структурируем\n";
        $content .= "        \$apiData = [\n";
        $content .= "            '{$moduleName}' => \$items->toArray(),\n";
        $content .= "        ];\n";
        $content .= "\n";
        $content .= "        return response()->json(\$apiData);\n";
        $content .= "    }\n";
        
        // Добавляем метод для получения одной записи по ID (опционально, но полезно)
        $content .= "\n";
        $content .= "    /**\n";
        $content .= "     * Получение одной записи по ID\n";
        $content .= "     * \n";
        $content .= "     * @param int \$id ID записи\n";
        $content .= "     * @return JsonResponse JSON ответ с данными записи\n";
        $content .= "     */\n";
        $content .= "    public function show(int \$id): JsonResponse\n";
        $content .= "    {\n";
        
        if ($hasTrash) {
            $content .= "        // Ищем активную запись\n";
            $content .= "        \$item = {$modelName}::find(\$id);\n";
        } else {
            $content .= "        // Ищем запись\n";
            $content .= "        \$item = {$modelName}::find(\$id);\n";
        }
        
        $content .= "\n";
        $content .= "        // Если запись не найдена\n";
        $content .= "        if (!\$item) {\n";
        $content .= "            return response()->json([\n";
        $content .= "                'error' => 'Запись не найдена',\n";
        $content .= "                'message' => 'Запись с ID ' . \$id . ' не существует'\n";
        $content .= "            ], 404);\n";
        $content .= "        }\n";
        $content .= "\n";
        $content .= "        return response()->json([\n";
        $content .= "            '{$moduleName}' => \$item->toArray()\n";
        $content .= "        ]);\n";
        $content .= "    }\n";
        
        $content .= "}\n";
        
        return $content;
    }
}