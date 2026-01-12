<?php

namespace App\Admin\Services;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;


/**
 * Сервис управления представлениями админ-панели
 * 
 * Отвечает за регистрацию пространства имен представлений админки,
 * подключение общих шаблонов и обеспечение доступа к представлениям
 * через пространство имен 'admin'.
 */
class AdminViewsService
{
    /**
     * Базовый путь к директории представлений админки
     * 
     * @var string
     */
    protected string $adminViewsPath;

    /**
     * Базовый путь к директории шаблонов админки
     * 
     * @var string
     */
    protected string $adminLayoutsPath;

    /**
     * Конструктор сервиса
     * 
     * Инициализирует пути к представлениям админ-панели
     */
    public function __construct()
    {
        $this->adminViewsPath = app_path('Admin/views');
        $this->adminLayoutsPath = app_path('Admin/views/layouts');
        
        Log::info('[AdminViewsService] Сервис представлений админ-панели инициализирован');
    }

    /**
     * Регистрирует все представления админ-панели
     * 
     * Регистрирует пространство имен 'admin' для представлений админки
     * и проверяет наличие базовых шаблонов
     * 
     * @return bool True если представления успешно зарегистрированы
     */
    public function registerAdminViews(): bool
    {
        try {
            Log::info('[AdminViewsService] Начинаем регистрацию представлений админ-панели', [
                'views_path' => $this->adminViewsPath,
                'layouts_path' => $this->adminLayoutsPath
            ]);

            // Проверяем существование директории представлений
            if (!File::exists($this->adminViewsPath)) {
                Log::error('[AdminViewsService] Директория представлений админки не найдена', [
                    'path' => $this->adminViewsPath
                ]);
                return false;
            }

            // Регистрируем пространство имен 'admin' для представлений админки
            View::addNamespace('admin', $this->adminViewsPath);

            // Проверяем наличие шаблона по умолчанию
            $defaultLayoutPath = $this->adminLayoutsPath . '/default.blade.php';
            if (!File::exists($defaultLayoutPath)) {
                Log::warning('[AdminViewsService] Шаблон по умолчанию не найден', [
                    'path' => $defaultLayoutPath
                ]);
            } else {
                Log::info('[AdminViewsService] Шаблон по умолчанию доступен', [
                    'layout' => 'default',
                    'path' => $defaultLayoutPath
                ]);
            }

            // Регистрируем общие переменные для всех представлений админки
            $this->registerSharedViewVariables();

            Log::info('[AdminViewsService] Представления админ-панели успешно зарегистрированы', [
                'namespace' => 'admin',
                'views_count' => $this->countAdminViewFiles()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('[AdminViewsService] Ошибка регистрации представлений админ-панели', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }

    /**
     * Регистрирует общие переменные для всех представлений админки
     * 
     * @return void
     */
    protected function registerSharedViewVariables(): void
    {
        try {
            // Регистрируем базовые переменные для админ-панели
            View::share('adminNamespace', 'admin');
            View::share('adminLayout', 'admin::layouts.default');
            
            Log::debug('[AdminViewsService] Общие переменные представлений зарегистрированы');
        } catch (\Exception $e) {
            Log::error('[AdminViewsService] Ошибка регистрации общих переменных представлений', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Получает путь к шаблону админки
     * 
     * @param string $layoutName Название шаблона (без расширения)
     * @return string|null Путь к файлу шаблона или null если не найден
     */
    public function getAdminLayoutPath(string $layoutName): ?string
    {
        try {
            $layoutPath = $this->adminLayoutsPath . '/' . $layoutName . '.blade.php';
            
            if (!File::exists($layoutPath)) {
                Log::warning('[AdminViewsService] Запрашиваемый шаблон не найден', [
                    'layout' => $layoutName,
                    'path' => $layoutPath
                ]);
                return null;
            }

            return $layoutPath;
        } catch (\Exception $e) {
            Log::error('[AdminViewsService] Ошибка получения пути к шаблону', [
                'layout' => $layoutName,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Проверяет существование представления в админке
     * 
     * @param string $viewName Название представления (например, 'pages.index')
     * @return bool True если представление существует
     */
    public function hasAdminView(string $viewName): bool
    {
        try {
            // Преобразуем название представления в путь
            $viewPath = str_replace('.', '/', $viewName);
            $fullPath = $this->adminViewsPath . '/' . $viewPath . '.blade.php';
            
            return File::exists($fullPath);
        } catch (\Exception $e) {
            Log::error('[AdminViewsService] Ошибка проверки существования представления', [
                'view' => $viewName,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Возвращает список всех представлений админки
     * 
     * @return array Массив названий представлений
     */
    public function listAdminViews(): array
    {
        try {
            if (!File::exists($this->adminViewsPath)) {
                return [];
            }

            $viewFiles = [];
            $files = File::allFiles($this->adminViewsPath);
            
            foreach ($files as $file) {
                if ($file->getExtension() === 'blade.php') {
                    // Получаем относительный путь от директории views
                    $relativePath = str_replace(
                        [$this->adminViewsPath . DIRECTORY_SEPARATOR, '.blade.php'],
                        ['', ''],
                        $file->getPathname()
                    );
                    
                    // Преобразуем путь в формат для view() (заменяем разделители на точки)
                    $viewName = str_replace(DIRECTORY_SEPARATOR, '.', $relativePath);
                    $viewFiles[] = $viewName;
                }
            }

            Log::debug('[AdminViewsService] Получен список представлений админки', [
                'views_count' => count($viewFiles)
            ]);

            return $viewFiles;
        } catch (\Exception $e) {
            Log::error('[AdminViewsService] Ошибка получения списка представлений админки', [
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Подсчитывает количество файлов представлений в директории админки
     * 
     * @return int Количество файлов представлений
     */
    protected function countAdminViewFiles(): int
    {
        try {
            if (!File::exists($this->adminViewsPath)) {
                return 0;
            }

            $files = File::allFiles($this->adminViewsPath);
            $count = 0;
            
            foreach ($files as $file) {
                if ($file->getExtension() === 'blade.php') {
                    $count++;
                }
            }
            
            return $count;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Получает путь к директории представлений админки
     * 
     * @return string Путь к директории представлений
     */
    public function getAdminViewsPath(): string
    {
        return $this->adminViewsPath;
    }

    /**
     * Получает путь к директории шаблонов админки
     * 
     * @return string Путь к директории шаблонов
     */
    public function getAdminLayoutsPath(): string
    {
        return $this->adminLayoutsPath;
    }
}