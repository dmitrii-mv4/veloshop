<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

/**
 * Сервис для управления видами (views) модульной системы
 * 
 * Отвечает за автоматическое обнаружение, валидацию и регистрацию
 * шаблонов модулей и административной части системы.
 * Обеспечивает пространства имен для изоляции видов модулей.
 */
class ViewsService
{
    /**
     * Сервис обнаружения модулей
     * 
     * @var ModuleDiscoveryService
     */
    protected ModuleDiscoveryService $moduleDiscovery;

    /**
     * Путь к видам административной части
     * 
     * @var string
     */
    protected string $adminViewsPath;

    /**
     * Конструктор сервиса
     * 
     * @param ModuleDiscoveryService $moduleDiscovery Сервис обнаружения модулей
     */
    public function __construct(ModuleDiscoveryService $moduleDiscovery)
    {
        $this->moduleDiscovery = $moduleDiscovery;
        $this->adminViewsPath = app_path('Admin/views');
        
        Log::info('ViewsService инициализирован', [
            'admin_path' => $this->adminViewsPath
        ]);
    }

    /**
     * Регистрирует все доступные виды модулей и административной части
     * 
     * @return void
     */
    public function registerAllViews(): void
    {
        try {
            // Регистрация видов административной части
            $this->registerAdminViews();
            
            // Регистрация видов активных модулей
            $activeModules = $this->moduleDiscovery->getActiveModules();
            
            foreach ($activeModules as $moduleName => $moduleConfig) {
                $this->registerModuleViews($moduleName);
            }
            
            Log::info('Все виды успешно зарегистрированы', [
                'modules_count' => count($activeModules)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при регистрации видов', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * Регистрирует виды административной части
     * 
     * @return bool
     */
    public function registerAdminViews(): bool
    {
        try {
            if (!File::exists($this->adminViewsPath)) {
                Log::warning('Директория видов админки не найдена', [
                    'path' => $this->adminViewsPath
                ]);
                return false;
            }

            // Регистрируем пространство имен для админки
            View::addNamespace('admin', $this->adminViewsPath);
            
            Log::info('Виды админки зарегистрированы', [
                'path' => $this->adminViewsPath,
                'namespace' => 'admin'
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Ошибка регистрации видов админки', [
                'message' => $e->getMessage(),
                'path' => $this->adminViewsPath
            ]);
            return false;
        }
    }

    /**
     * Регистрирует виды конкретного модуля
     * 
     * @param string $moduleName Название модуля
     * @return bool
     */
    public function registerModuleViews(string $moduleName): bool
    {
        try {
            // Получаем путь к модулю
            $modulePath = $this->moduleDiscovery->getModulePath($moduleName);
            
            if (!$modulePath) {
                Log::warning('Модуль не найден при регистрации видов', [
                    'module' => $moduleName
                ]);
                return false;
            }

            $moduleViewsPath = $modulePath . DIRECTORY_SEPARATOR . 'views';
            
            // Проверяем существование директории видов
            if (!File::exists($moduleViewsPath)) {
                Log::warning('Директория видов модуля не найдена', [
                    'module' => $moduleName,
                    'path' => $moduleViewsPath
                ]);
                return false;
            }

            // Регистрируем пространство имен для модуля
            View::addNamespace(strtolower($moduleName), $moduleViewsPath);
            
            Log::info('Виды модуля зарегистрированы', [
                'module' => $moduleName,
                'path' => $moduleViewsPath,
                'namespace' => strtolower($moduleName)
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Ошибка регистрации видов модуля', [
                'module' => $moduleName,
                'message' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Проверяет существование шаблона в административной части
     * 
     * @param string $viewName Имя шаблона (например: 'layouts.default')
     * @return bool
     */
    public function adminViewExists(string $viewName): bool
    {
        $viewPath = $this->adminViewsPath . DIRECTORY_SEPARATOR . 
                   str_replace('.', DIRECTORY_SEPARATOR, $viewName) . '.blade.php';
        
        return File::exists($viewPath);
    }

    /**
     * Проверяет существование шаблона в модуле
     * 
     * @param string $moduleName Название модуля
     * @param string $viewName Имя шаблона (например: 'goods.index')
     * @return bool
     */
    public function moduleViewExists(string $moduleName, string $viewName): bool
    {
        $modulePath = $this->moduleDiscovery->getModulePath($moduleName);
        
        if (!$modulePath) {
            return false;
        }

        $viewPath = $modulePath . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR .
                   str_replace('.', DIRECTORY_SEPARATOR, $viewName) . '.blade.php';
        
        return File::exists($viewPath);
    }

    /**
     * Получает полный путь к шаблону административной части
     * 
     * @param string $viewName Имя шаблона
     * @return string|null
     */
    public function getAdminViewPath(string $viewName): ?string
    {
        $viewPath = $this->adminViewsPath . DIRECTORY_SEPARATOR . 
                   str_replace('.', DIRECTORY_SEPARATOR, $viewName) . '.blade.php';
        
        return File::exists($viewPath) ? $viewPath : null;
    }

    /**
     * Получает полный путь к шаблону модуля
     * 
     * @param string $moduleName Название модуля
     * @param string $viewName Имя шаблона
     * @return string|null
     */
    public function getModuleViewPath(string $moduleName, string $viewName): ?string
    {
        $modulePath = $this->moduleDiscovery->getModulePath($moduleName);
        
        if (!$modulePath) {
            return null;
        }

        $viewPath = $modulePath . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR .
                   str_replace('.', DIRECTORY_SEPARATOR, $viewName) . '.blade.php';
        
        return File::exists($viewPath) ? $viewPath : null;
    }

    /**
     * Получает список всех доступных шаблонов в административной части
     * 
     * @param bool $recursive Рекурсивный поиск
     * @return array
     */
    public function getAdminViewsList(bool $recursive = true): array
    {
        return $this->getViewsFromDirectory($this->adminViewsPath, $recursive);
    }

    /**
     * Получает список всех доступных шаблонов в модуле
     * 
     * @param string $moduleName Название модуля
     * @param bool $recursive Рекурсивный поиск
     * @return array
     */
    public function getModuleViewsList(string $moduleName, bool $recursive = true): array
    {
        $modulePath = $this->moduleDiscovery->getModulePath($moduleName);
        
        if (!$modulePath) {
            return [];
        }

        $viewsPath = $modulePath . DIRECTORY_SEPARATOR . 'views';
        
        return $this->getViewsFromDirectory($viewsPath, $recursive);
    }

    /**
     * Рекурсивно сканирует директорию и возвращает список шаблонов
     * 
     * @param string $directory Путь к директории
     * @param bool $recursive Рекурсивный поиск
     * @return array
     */
    protected function getViewsFromDirectory(string $directory, bool $recursive = true): array
    {
        if (!File::exists($directory)) {
            return [];
        }

        $views = [];
        $files = $recursive 
            ? File::allFiles($directory)
            : File::files($directory);

        foreach ($files as $file) {
            if ($file->getExtension() === 'blade.php') {
                // Преобразуем путь в dot-нотацию
                $relativePath = str_replace(
                    [$directory . DIRECTORY_SEPARATOR, '.blade.php'],
                    ['', ''],
                    $file->getPathname()
                );
                
                $viewName = str_replace(DIRECTORY_SEPARATOR, '.', $relativePath);
                $views[] = $viewName;
            }
        }

        return $views;
    }

    /**
     * Проверяет и регистрирует виды для нового модуля
     * 
     * @param string $moduleName Название модуля
     * @return bool
     */
    public function checkAndRegisterModule(string $moduleName): bool
    {
        try {
            // Проверяем, существует ли модуль
            $moduleConfig = $this->moduleDiscovery->getModuleConfig($moduleName);
            
            if (!$moduleConfig) {
                Log::warning('Модуль не найден при проверке видов', [
                    'module' => $moduleName
                ]);
                return false;
            }

            // Регистрируем виды модуля
            return $this->registerModuleViews($moduleName);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при проверке и регистрации модуля', [
                'module' => $moduleName,
                'message' => $e->getMessage()
            ]);
            return false;
        }
    }
}