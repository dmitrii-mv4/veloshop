<?php

namespace App\Modules\ModuleGenerator\Services\Generator\Files;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для генерации Middleware для модулей
 * 
 * @param array $moduleData Настройки модулей
 * @param string $moduleMiddlewarePath путь к директории модуля миграций
 */

class Middleware
{
    protected $moduleData;
    protected $moduleMiddlewarePath;

    public function __construct($moduleData)
    {
        $this->moduleData = $moduleData;
    }

    public function generate()
    {
        // Создание структуры директорий
        $this->moduleMiddlewarePath = $this->ensureModulesMiddlewareDir();

        // Генеририуем миграции 
        $this->createIndexMiddleware();
        $this->createCreateMiddleware();
        $this->createUpdateMiddleware();
        $this->createDeleteMiddleware();
    }

     /**
     * Создает или проверяет существование директории для Middleware модуля
     * 
     * Директория создается по пути: app/Http/Middleware/Modules/$moduleData['path']['middleware']
     * 
     */
    private function ensureModulesMiddlewareDir()
    {
        // Формируем путь к модулю
        $moduleMiddlewarePath = base_path($this->moduleData['path']['middleware']);

        if (!File::exists($moduleMiddlewarePath))
        {
            try {
                // Создаем директорию модуля
                File::makeDirectory($moduleMiddlewarePath, 0755, true);
            } catch (\Exception $e)
            {
                $moduleDataCodeName = $this->moduleData['code_name'];

                throw new \RuntimeException("Не удалось создать директорию для модели модуля '{$moduleDataCodeName}' по пути: {$moduleMiddlewarePath}", 0, $e);
            }
        }
        return $moduleMiddlewarePath;
    }

    /**
     * Создаём Middleware для просмотра всех записей
     */
    public function createIndexMiddleware()
    {
        // Полный путь к файлу 
        $middlewareFilePath = base_path($this->moduleData['path']['middleware'] . '/' . $this->moduleData['item']['middleware_name_index'] . '.php');

        $content = <<<PHP
<?php

namespace {$this->moduleData['namespace']['middleware_index']};

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Modules\User\Models\User;
use {$this->moduleData['namespace']['use']['model']};

class {$this->moduleData['item']['middleware_name_index']}
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  \$next
     */
    public function handle(Request \$request, Closure \$next): Response
    {
        // Проверяем разрешение через Gate
        if (Gate::allows('viewAny', {$this->moduleData['item']['model_name']}::class)) {
            return \$next(\$request);
        }
        
        // Если доступ запрещен
        abort(403, 'Доступ запрещен');
    }
}
PHP;

        // Записываем содержимое в файл
        File::put($middlewareFilePath, $content);
        
        if (!File::exists($middlewareFilePath)) {
            throw new \Exception("Файл Middleware не найден: ".$middlewareFilePath);
        }
        
        return true;
    }

    /**
     * Создаём Middleware для создания записей
     */
    public function createCreateMiddleware()
    {
        // Полный путь к файлу
        $middlewareFilePath = base_path($this->moduleData['path']['middleware'] . '/' . $this->moduleData['item']['middleware_name_create'] . '.php');

        $content = <<<PHP
<?php

namespace {$this->moduleData['namespace']['middleware_create']};

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Modules\User\Models\User;
use {$this->moduleData['namespace']['use']['model']};

class {$this->moduleData['item']['middleware_name_create']}
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  \$next
     */
    public function handle(Request \$request, Closure \$next): Response
    {
        // Проверяем разрешение через Gate
        if (Gate::allows('create', {$this->moduleData['item']['model_name']}::class)) {
            return \$next(\$request);
        }
        
        // Если доступ запрещен
        abort(403, 'Доступ запрещен');
    }
}
PHP;
        // Записываем содержимое в файл
        File::put($middlewareFilePath, $content);
        
        if (!File::exists($middlewareFilePath)) {
            throw new \Exception("Файл Middleware не найден: ".$middlewareFilePath);
        }
        
        return true;
    }

    /**
     * Создаём Middleware для редактирования записей
     */
    public function createUpdateMiddleware()
    {
        // Полный путь к файлу
        $middlewareFilePath = base_path($this->moduleData['path']['middleware'] . '/' . $this->moduleData['item']['middleware_name_update'] . '.php');

        $content = <<<PHP
<?php

namespace {$this->moduleData['namespace']['middleware_update']};

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Modules\User\Models\User;
use {$this->moduleData['namespace']['use']['model']};

class {$this->moduleData['item']['middleware_name_update']}
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  \$next
     */
    public function handle(Request \$request, Closure \$next): Response
    {
        // Проверяем разрешение через Gate
        if (Gate::allows('update', {$this->moduleData['item']['model_name']}::class)) {
            return \$next(\$request);
        }
        
        // Если доступ запрещен
        abort(403, 'Доступ запрещен');
    }
}
PHP;

        // Записываем содержимое в файл
        File::put($middlewareFilePath, $content);
        
        if (!File::exists($middlewareFilePath)) {
            throw new \Exception("Файл Middleware не найден: ".$middlewareFilePath);
        }
        
        return true;
    }

    /**
     * Создаём Middleware для удаление записей
     */
    public function createDeleteMiddleware()
    {
        // Полный путь к файлу
        $middlewareFilePath = base_path($this->moduleData['path']['middleware'] . '/' . $this->moduleData['item']['middleware_name_delete'] . '.php');

        $content = <<<PHP
<?php

namespace {$this->moduleData['namespace']['middleware_delete']};

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Modules\User\Models\User;
use {$this->moduleData['namespace']['use']['model']};

class {$this->moduleData['item']['middleware_name_delete']}
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  \$next
     */
    public function handle(Request \$request, Closure \$next): Response
    {
        // Проверяем разрешение через Gate
        if (Gate::allows('delete', {$this->moduleData['item']['model_name']}::class)) {
            return \$next(\$request);
        }
        
        // Если доступ запрещен
        abort(403, 'Доступ запрещен');
    }
}
PHP;

        // Записываем содержимое в файл
        File::put($middlewareFilePath, $content);
        
        if (!File::exists($middlewareFilePath)) {
            throw new \Exception("Файл Middleware не найден: ".$middlewareFilePath);
        }
        
        return true;
    }
}