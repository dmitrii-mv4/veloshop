<?php

namespace App\Modules\ModuleGenerator\Services\Generator;

use App\Modules\ModuleGenerator\Services\Generator\ModuleConfigFormService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Modules\ModuleGenerator\Services\Generator\Files\Migration;
use App\Modules\ModuleGenerator\Services\Generator\Files\Model;
use App\Modules\ModuleGenerator\Services\Generator\Files\Views;
use App\Modules\ModuleGenerator\Services\Generator\Files\Request;
use App\Modules\ModuleGenerator\Services\Generator\Files\Controller;
use App\Modules\ModuleGenerator\Services\Generator\Files\Middleware;
use App\Modules\ModuleGenerator\Services\Generator\Files\Policy;
use App\Modules\ModuleGenerator\Services\Generator\Files\Routes;

/**
 * Сервис для генерации модулей
 * 
 * @param array $moduleData Настройки создаваемого модуля
 */

class ModuleGeneratorService
{
    protected $moduleData;

    /**
     * Основной метод генерации модуля
     * 
     * @param array $validatedData Валидированные данные для генерации
     * @return array Результат генерации
     */
    public function main($validatedData)
    {
        Log::info('ModuleGeneratorService::main запущен', [
            'module_code' => $validatedData['code_module'] ?? 'unknown'
        ]);

        try {
            // Собираем настройки для модулей
            $configClass = new ModuleConfigFormService();
            $this->moduleData = $configClass->build($validatedData);

            // Создаём директорию для модулей
            $this->createDirModule();

            // Собираем модуль
            $migrationGenerator = new Migration($this->moduleData);
            $modelGenerator = new Model($this->moduleData);
            $viewsGenerator = new Views($this->moduleData);
            $requestGenerator = new Request($this->moduleData);
            $controllerGenerator = new Controller($this->moduleData);
            $middlewareGenerator = new Middleware($this->moduleData);
            $policyGenerator = new Policy($this->moduleData);
            $routesGenerator = new Routes($this->moduleData);

            // Генерируем модели и миграции
            $migrationGenerator->generate();
            $modelGenerator->generate();

            // Генерируем Views и возвращаем название файла view
            $viewNamesData = $viewsGenerator->generate();

            // Генерируем Request
            $requestGenerator->generate();

            // Генерируем контроллеры
            $controllerGenerator->generate($viewNamesData);

            // Генерируем Middleware
            $middlewareGenerator->generate();

            // Генерируем Policy
            $policyGenerator->generate();

            // Генерируем роутер файлы
            $routesGenerator->generate();

            // Автоматически обновляем автозагрузчик
            $this->dumpAutoload();

            return true;

        } catch (\Exception $e) {
            Log::error('ModuleGeneratorService::main ошибка', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $validatedData
            ]);

            // Возвращаем ошибку в структурированном виде
            return [
                'success' => false,
                'message' => 'Ошибка при генерации модуля: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'module' => $validatedData['code_module'] ?? 'unknown'
            ];
        }

        //dd($this->moduleData);
    }

    /**
     * Создаёт директорию для конкретного модуля (например, Modules/News)
     * Проверяет существование папки модуля, создаёт если не существует, иначе логирует ошибку и выбрасывает исключение
     */
    private function createDirModule(): bool
    {
        $moduleCode = $this->moduleData['code_module']; // "news"
        $moduleName = $this->moduleData['code_name'];   // "News"
        
        // Полный путь к директории модуля (например: /var/www/kotiks/Modules/News)
        $fullModulePath = $this->moduleData['path']['full_base_module'];
        
        // 1. Проверяем существование папки модуля
        if (File::exists($fullModulePath)) {
            // Директория модуля уже существует - логируем и выбрасываем исключение
            \Log::error("Не удалось создать директорию для модуля '{$moduleName}' (код: {$moduleCode}). Директория уже существует.", [
                'module_code' => $moduleCode,
                'module_name' => $moduleName,
                'path' => $fullModulePath,
                'module_data' => $this->moduleData
            ]);
            
            throw new \RuntimeException(
                "Модуль '{$moduleName}' (код: {$moduleCode}) уже существует. " .
                "Директория: {$fullModulePath}. " .
                "Если вы хотите пересоздать модуль, удалите существующую директорию вручную."
            );
        }
        
        // 2. Создаём папку модуля, если она не существует
        try {
            File::makeDirectory($fullModulePath, 0755, true);
            
            \Log::info("Директория модуля успешно создана", [
                'module_code' => $moduleCode,
                'module_name' => $moduleName,
                'path' => $fullModulePath,
                'permissions' => '0755'
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            // Логируем ошибку создания директории
            \Log::error("Не удалось создать директорию для модуля '{$moduleName}' (код: {$moduleCode})", [
                'module_code' => $moduleCode,
                'module_name' => $moduleName,
                'path' => $fullModulePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Бросаем пользовательское исключение с понятным сообщением
            throw new \RuntimeException(
                "Не удалось создать директорию для модуля '{$moduleName}' (код: {$moduleCode}). " .
                "Путь: {$fullModulePath}. " .
                "Ошибка: " . $e->getMessage() . " " .
                "Убедитесь, что у веб-сервера есть права на запись в директорию: " . dirname($fullModulePath)
            );
        }
    }

    /**
     * Автоматический dump-autoload 
     * 
     */
    private function dumpAutoload()
    {
        try {
            // Устанавливаем переменные окружения
            putenv('HOME=' . base_path());
            putenv('COMPOSER_HOME=' . base_path() . '/.composer');
            
            // Ищем composer в системе
            $composerPath = null;
            
            // Вариант 1: composer в PATH
            $whichComposer = shell_exec('which composer 2>/dev/null');
            if ($whichComposer && trim($whichComposer)) {
                $composerPath = trim($whichComposer);
            }
            
            // Вариант 2: глобальный composer
            if (!$composerPath && file_exists('/usr/local/bin/composer')) {
                $composerPath = '/usr/local/bin/composer';
            }
            
            // Вариант 3: composer.phar в проекте
            if (!$composerPath && file_exists(base_path('composer.phar'))) {
                $composerPath = base_path('composer.phar');
            }
            
            if (!$composerPath) {
                Log::warning('Composer не найден в системе, используем альтернативный метод');
                $this->alternativeAutoload();
                return false;
            }
            
            $currentDir = getcwd();
            chdir(base_path());
            
            // Используем найденный composer
            $command = PHP_BINARY . ' ' . $composerPath . ' dump-autoload 2>&1';
            $output = [];
            $returnCode = 0;
            
            exec($command, $output, $returnCode);
            
            chdir($currentDir);
            
            Log::info('Composer dump-autoload выполнен', [
                'return_code' => $returnCode,
                'output_first_line' => $output[0] ?? 'Пустой вывод',
                'composer_path' => $composerPath
            ]);
            
            if ($returnCode !== 0) {
                Log::warning('Composer dump-autoload вернул ошибку, используем альтернативный метод');
                $this->alternativeAutoload();
            }
            
            return $returnCode === 0;
        } catch (\Exception $e) {
            Log::warning('Не удалось выполнить composer dump-autoload', [
                'error' => $e->getMessage()
            ]);
            
            $this->alternativeAutoload();
            return false;
        }
    }

    /**
     * Альтернативная dump-autoload 
     * 
     */
    private function alternativeAutoload()
    {
        try {
            // Метод 1: Очистка кеша Laravel
            \Artisan::call('optimize:clear');
            
            // Метод 2: Ручная перезагрузка автозагрузчика
            $autoloadPath = base_path('vendor/autoload.php');
            if (file_exists($autoloadPath)) {
                require_once $autoloadPath;
                Log::info('Автозагрузчик перезагружен вручную');
            }
            
            // Метод 3: Уведомление для разработчика
            Log::info('Выполните вручную: composer dump-autoload && php artisan optimize:clear');
            
            return true;
        } catch (\Exception $e) {
            Log::error('Альтернативный метод автозагрузки не сработал', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}