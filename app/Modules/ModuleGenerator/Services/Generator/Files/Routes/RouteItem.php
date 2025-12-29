<?php

namespace App\Modules\ModuleGenerator\Services\Generator\Files\Routes;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для генерации web-роутов модуля
 * Генерирует web-роуты для административной панели
 * Поддерживает опцию корзины (trash) для восстановления и полного удаления записей
 * 
 * @param array $moduleData Настройки модулей
 * @param string $moduleRouterFullPath абсолютный путь к директории роутов модуля
 */
class RouteItem
{
    protected $moduleData;
    protected $moduleRouterFullPath;

    public function __construct($moduleData, $moduleRouterFullPath)
    {
        $this->moduleData = $moduleData;
        $this->moduleRouterFullPath = $moduleRouterFullPath;
    }

    public function generate()
    {
        try {
            Log::info('Начало генерации web роутов модуля', [
                'module' => $this->moduleData['code_module']
            ]);

            // Генерируем web роуты
            $this->createWebRoutes();

            Log::info('Успешное завершение генерации web роутов модуля', [
                'module' => $this->moduleData['code_module']
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Критическая ошибка при генерации web роутов модуля', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \RuntimeException("Ошибка генерации web роутов: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Создаём web роуты модуля
     */
    public function createWebRoutes()
    {
        try {
            $controllerName = $this->moduleData['item']['controller_name'];
            $codeModule = $this->moduleData['code_module'];
            
            Log::info('Создание web роутов модуля', [
                'module' => $this->moduleData['code_module'],
                'controller_name' => $controllerName,
                'code_module' => $codeModule
            ]);
            
            // Формируем путь к файлу роутов
            $routePathFile = $this->moduleRouterFullPath . '/web.php';
            
            // Проверяем существование файла
            if (File::exists($routePathFile)) {
                Log::warning('Файл web роутов уже существует, будет перезаписан', [
                    'file_path' => $routePathFile
                ]);
            }
            
            // Начинаем формирование контента роутов
            $content = "<?php\n\n";
            $content .= "use Illuminate\Support\Facades\Route;\n";
            $content .= "use {$this->moduleData['namespace']['use']['controller']};\n\n";
            $content .= "Route::prefix('/{$codeModule}')->name('admin.{$codeModule}.')->group(function () {\n    \n";
            $content .= "    // Главная страница (активные записи)\n";
            $content .= "    Route::get('/', [{$controllerName}::class, 'index'])->name('index');\n    \n";
            $content .= "    // Создание записи\n";
            $content .= "    Route::get('/create', [{$controllerName}::class, 'create'])->name('create');\n";
            $content .= "    Route::post('/', [{$controllerName}::class, 'store'])->name('store');\n    \n";
            $content .= "    // Редактирование записи\n";
            $content .= "    Route::get('/{id}/edit', [{$controllerName}::class, 'edit'])->name('edit');\n";
            $content .= "    Route::put('/{id}', [{$controllerName}::class, 'update'])->name('update');\n    \n";
            $content .= "    // Удаление в корзину\n";
            $content .= "    Route::delete('/{id}', [{$controllerName}::class, 'destroy'])->name('destroy');\n    ";

            // Добавляем роуты для корзины, если опция trash включена
            if (isset($this->moduleData['option']['trash']) && $this->moduleData['option']['trash'] === true) {
                $content .= "\n    // Корзина\n";
                $content .= "    Route::prefix('trash')->name('trash.')->group(function () {\n";
                $content .= "        Route::get('/', [{$controllerName}::class, 'trashIndex'])->name('index');\n";
                $content .= "        Route::post('/{id}/restore', [{$controllerName}::class, 'restore'])->name('restore');\n";
                $content .= "        Route::delete('/{id}/force', [{$controllerName}::class, 'forceDestroy'])->name('force');\n";
                $content .= "        Route::post('/empty', [{$controllerName}::class, 'emptyTrash'])->name('empty');\n";
                $content .= "    });\n";
            }

            $content .= "\n});";

            // Записываем изменения в файл
            File::put($routePathFile, $content);
            
            Log::info('Файл web роутов успешно сгенерирован', [
                'file_path' => $routePathFile,
                'controller_name' => $controllerName,
                'has_trash' => isset($this->moduleData['option']['trash']) && $this->moduleData['option']['trash']
            ]);
            
            return true;

        } catch (\Exception $e) {
            Log::error('Ошибка при создании web роутов модуля', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'controller_name' => $controllerName ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception("Ошибка создания web роутов: " . $e->getMessage(), 0, $e);
        }
    }
}