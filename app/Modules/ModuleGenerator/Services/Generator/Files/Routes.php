<?php

namespace App\Modules\ModuleGenerator\Services\Generator\Files;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для генерации Router API для модулей
 * 
 * @param array $moduleData Настройки модулей
 */

class Routes
{
    protected $moduleData;

    public function __construct($moduleData)
    {
        $this->moduleData = $moduleData;
    }

    public function generate()
    {
        try {
            // Создание структуры директорий
            $this->ensureModulesRouterDir();

            $routeFilePath = base_path($this->moduleData['path']['router'] . '/web.php');

            // Формируем переменные для вставки
            $codeModule = $this->moduleData['code_module'];
            $controllerName = $this->moduleData['item']['controller_name'];

            $content = "<?php

use Illuminate\Support\Facades\Route;
use {$this->moduleData['namespace']['use']['controller']};
use {$this->moduleData['namespace']['use']['controller_category']};

Route::prefix('/{$codeModule}')->controller({$controllerName}::class)->group(function () 
{
    Route::get('/', 'index')
        ->middleware(['{$codeModule}_index'])
        ->name('admin.{$codeModule}.index');

    Route::get('/create', 'create')
        ->middleware(['{$codeModule}_create'])
        ->name('admin.{$codeModule}.create');

    Route::post('/', 'store')
        ->middleware(['{$codeModule}_create'])
        ->name('admin.{$codeModule}.store');

    Route::get('/{item}/edit', 'edit')
        ->middleware(['{$codeModule}_update'])
        ->name('admin.{$codeModule}.edit');

    Route::patch('/{item}', 'update')
        ->middleware(['{$codeModule}_update'])
        ->name('admin.{$codeModule}.update');

    Route::delete('/{item}', 'delete')
        ->middleware(['{$codeModule}_delete'])
        ->name('admin.{$codeModule}.delete');
});

";

            // Create the file and write the content
            if (File::put($routeFilePath, $content) === false) {
                throw new \Exception("Failed to create route file:" . $routeFilePath);
            }

        } catch (\Exception $e) {
            Log::error('Ошибка при генерации Route файла', [
                'module' => $this->moduleData['code_name'],
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function ensureModulesRouterDir()
    {
        // Формируем путь к модулю
        $moduleRouterPath = base_path($this->moduleData['path']['router']);

        if (!File::exists($moduleRouterPath))
        {
            try {
                // Создаем директорию модуля
                File::makeDirectory($moduleRouterPath, 0755, true);
            } catch (\Exception $e)
            {
                throw new \RuntimeException("Не удалось создать директорию для router модуля по пути: {$moduleRouterPath}", 0, $e);
            }
        }
        return $moduleRouterPath;
    }
}