<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\File;

/**
 * Сервис обнаружения и загрузки модулей системы
 * 
 * Отвечает за сканирование директории модулей, загрузку конфигурационных файлов,
 * валидацию структуры модулей и предоставление метаинформации о доступных модулях.
 */
class ModuleDiscoveryService
{
    /**
     * Базовый путь к директории модулей
     * 
     * @var string
     */
    protected string $modulesPath;

    /**
     * Конструктор сервиса
     * 
     * Инициализирует базовый путь к директории модулей
     */
    public function __construct()
    {
        $this->modulesPath = app_path('Modules');
    }

    /**
     * Обнаруживает все доступные модули в системе
     * 
     * Сканирует директорию модулей, загружает конфигурации,
     * валидирует структуру и возвращает массив модулей
     * с полной метаинформацией
     * 
     * @return array Массив конфигураций всех обнаруженных модулей
     */
    public function discoverAllModules(): array
    {
        $modules = [];
        
        if (!File::exists($this->modulesPath)) {
            return $modules;
        }

        $directories = File::directories($this->modulesPath);

        foreach ($directories as $directory) {
            $moduleName = basename($directory);
            $config = $this->loadModuleConfig($moduleName);
            
            if ($config !== null && $this->validateModuleConfig($config)) {
                $modules[$moduleName] = array_merge(
                    $config,
                    ['path' => $directory]
                );
            }
        }

        // Сортировка модулей по приоритету загрузки
        uasort($modules, function ($a, $b) {
            $priorityA = $a['module']['priority'] ?? 999;
            $priorityB = $b['module']['priority'] ?? 999;
            
            return $priorityA <=> $priorityB;
        });

        return $modules;
    }

    /**
     * Загружает конфигурацию конкретного модуля
     * 
     * @param string $moduleName Название модуля
     * @return array|null Конфигурация модуля или null если модуль не найден
     */
    public function getModuleConfig(string $moduleName): ?array
    {
        $allModules = $this->discoverAllModules();
        return $allModules[$moduleName] ?? null;
    }

    /**
     * Проверяет существование и доступность модуля
     * 
     * @param string $moduleName Название модуля
     * @return bool True если модуль существует и включен
     */
    public function isModuleEnabled(string $moduleName): bool
    {
        $config = $this->getModuleConfig($moduleName);
        
        if (!$config) {
            return false;
        }

        return $config['module']['enabled'] ?? false;
    }

    /**
     * Получает список всех активных модулей
     * 
     * @return array Массив конфигураций только активных модулей
     */
    public function getActiveModules(): array
    {
        $allModules = $this->discoverAllModules();
        
        return array_filter($allModules, function ($module) {
            return $this->isModuleEnabled($module['module']['name'] ?? '');
        });
    }

    /**
     * Получает список зависимостей модуля
     * 
     * @param string $moduleName Название модуля
     * @return array Массив названий зависимых модулей
     */
    public function getModuleDependencies(string $moduleName): array
    {
        $config = $this->getModuleConfig($moduleName);
        
        if (!$config) {
            return [];
        }

        return $config['module']['dependencies'] ?? [];
    }

    /**
     * Загружает конфигурационный файл модуля
     * 
     * @param string $moduleName Название модуля
     * @return array|null Конфигурация модуля или null при ошибке
     */
    protected function loadModuleConfig(string $moduleName): ?array
    {
        $configPath = $this->modulesPath . DIRECTORY_SEPARATOR . 
                     $moduleName . DIRECTORY_SEPARATOR . 'config.php';

        if (!File::exists($configPath)) {
            return null;
        }

        try {
            $config = require $configPath;
            
            if (!is_array($config)) {
                return null;
            }

            // Добавляем название модуля в конфигурацию
            $config['module']['name'] = $config['module']['name'] ?? $moduleName;
            
            return $config;
        } catch (\Exception $e) {
            // Логирование ошибки загрузки конфигурации
            return null;
        }
    }

    /**
     * Валидирует структуру конфигурации модуля
     * 
     * Проверяет наличие обязательных полей и их корректность
     * 
     * @param array $config Конфигурация модуля
     * @return bool True если конфигурация валидна
     */
    protected function validateModuleConfig(array $config): bool
    {
        // Проверка наличия обязательной секции module
        if (!isset($config['module']) || !is_array($config['module'])) {
            return false;
        }

        $moduleConfig = $config['module'];

        // Обязательные поля
        $requiredFields = ['name', 'title', 'description', 'version', 'enabled'];
        
        foreach ($requiredFields as $field) {
            if (!isset($moduleConfig[$field])) {
                return false;
            }
        }

        // Проверка типа данных обязательных полей
        if (!is_string($moduleConfig['name']) || 
            !is_string($moduleConfig['title']) || 
            !is_string($moduleConfig['description']) ||
            !is_string($moduleConfig['version']) ||
            !is_bool($moduleConfig['enabled'])) {
            return false;
        }

        // Проверка версии PHP и Laravel если указаны
        if (isset($config['system']['requirements'])) {
            $requirements = $config['system']['requirements'];
            
            if (isset($requirements['php'])) {
                if (!version_compare(PHP_VERSION, $requirements['php'], '>=')) {
                    return false;
                }
            }
            
            if (isset($requirements['laravel'])) {
                $laravelVersion = app()->version();
                if (!version_compare($laravelVersion, $requirements['laravel'], '>=')) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Получает путь к директории модуля
     * 
     * @param string $moduleName Название модуля
     * @return string|null Путь к директории или null если модуль не существует
     */
    public function getModulePath(string $moduleName): ?string
    {
        $config = $this->getModuleConfig($moduleName);
        return $config['path'] ?? null;
    }

    /**
     * Устанавливает пользовательский путь к директории модулей
     * 
     * @param string $path Новый путь к директории модулей
     * @return self
     */
    public function setModulesPath(string $path): self
    {
        $this->modulesPath = $path;
        return $this;
    }
}