<?php

namespace App\Modules\Integrator\Services;

use Illuminate\Support\Facades\File;
use ReflectionClass;
use App\Modules\Integrator\Services\Interfaces\DriverInterface;

/**
 * Сервис для обнаружения и управления драйверами интеграции
 * 
 * Основные функции:
 * - Сканирование директории Drivers на наличие доступных драйверов
 * - Проверка корректности структуры драйверов
 * - Получение информации о всех доступных драйверах
 * - Проверка доступности конкретного драйвера по имени
 */
class DriverDiscoveryService
{
    /**
     * Базовый путь к директории с драйверами
     */
    protected string $driversPath;
    
    /**
     * Базовое пространство имен для драйверов
     */
    protected string $driversNamespace = 'App\\Modules\\Integrator\\Services\\Drivers\\';

    /**
     * Конструктор сервиса
     */
    public function __construct()
    {
        $this->driversPath = base_path('app/Modules/Integrator/Services/Drivers/');
    }

    /**
     * Получить список всех доступных драйверов
     * 
     * @return array Массив с информацией о драйверах
     */
    public function getAvailableDrivers(): array
    {
        $drivers = [];
        
        if (!File::exists($this->driversPath)) {
            return $drivers;
        }

        $directories = File::directories($this->driversPath);
        
        foreach ($directories as $directory) {
            $driverName = basename($directory);
            
            if ($this->isValidDriver($driverName)) {
                $driverInfo = $this->getDriverInfo($driverName);
                if ($driverInfo) {
                    $drivers[$driverName] = $driverInfo;
                }
            }
        }

        return $drivers;
    }

    /**
     * Проверить валидность драйвера
     * 
     * @param string $driverName Имя драйвера (название директории)
     * @return bool
     */
    public function isValidDriver(string $driverName): bool
    {
        $driverPath = $this->driversPath . $driverName;
        
        // Проверяем существование директории
        if (!File::exists($driverPath) || !File::isDirectory($driverPath)) {
            return false;
        }

        // Проверяем наличие файла MainDriver.php
        $mainDriverFile = $driverPath . '/MainDriver.php';
        if (!File::exists($mainDriverFile)) {
            return false;
        }

        // Проверяем наличие класса MainDriver
        $className = $this->driversNamespace . $driverName . '\\MainDriver';
        if (!class_exists($className)) {
            return false;
        }

        return true;
    }

    /**
     * Получить информацию о драйвере
     * 
     * @param string $driverName Имя драйвера
     * @return array|null Массив с информацией о драйвере или null если драйвер невалиден
     */
    public function getDriverInfo(string $driverName): ?array
    {
        if (!$this->isValidDriver($driverName)) {
            return null;
        }

        $className = $this->driversNamespace . $driverName . '\\MainDriver';
        
        try {
            $reflection = new ReflectionClass($className);
            $instance = $reflection->newInstanceWithoutConstructor();
            
            // Проверяем, что драйвер реализует интерфейс
            if (!$instance instanceof DriverInterface) {
                return null;
            }
            
            $driverInfo = [
                'name' => $instance->getName(),
                'system_type' => $instance->getSystemType(),
                'description' => $instance->getDescription(),
                'version' => $instance->getVersion(),
                'icon' => $instance->getIcon(),
                'icon_class' => $instance->getIconClass(),
                'class' => $className,
                'driver_dir' => $driverName,
                'status' => 'available'
            ];

            // Добавляем форму настроек, если метод существует
            if (method_exists($instance, 'getSettingsForm')) {
                $driverInfo['settings_form'] = $instance->getSettingsForm();
            }

            return $driverInfo;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Проверить наличие драйвера
     * 
     * @param string $driverName Имя драйвера
     * @return bool
     */
    public function hasDriver(string $driverName): bool
    {
        return $this->isValidDriver($driverName);
    }

    /**
     * Получить экземпляр драйвера
     * 
     * @param string $driverName Имя драйвера
     * @return mixed Экземпляр класса драйвера
     * @throws \RuntimeException Если драйвер не найден или невалиден
     */
    public function getDriverInstance(string $driverName)
    {
        if (!$this->isValidDriver($driverName)) {
            throw new \RuntimeException("Драйвер '{$driverName}' не найден или имеет невалидную структуру");
        }

        $className = $this->driversNamespace . $driverName . '\\MainDriver';
        
        try {
            $instance = app($className);
            
            // Проверяем, что драйвер реализует интерфейс
            if (!$instance instanceof DriverInterface) {
                throw new \RuntimeException("Драйвер '{$driverName}' не реализует обязательный интерфейс DriverInterface");
            }
            
            return $instance;
        } catch (\Exception $e) {
            throw new \RuntimeException("Не удалось создать экземпляр драйвера '{$driverName}': " . $e->getMessage());
        }
    }

    /**
     * Получить список имен доступных драйверов
     * 
     * @return array
     */
    public function getDriverNames(): array
    {
        $drivers = $this->getAvailableDrivers();
        return array_keys($drivers);
    }

    /**
     * Получить драйверы по типу системы
     * 
     * @param string $systemType Тип системы (например, 'crm', 'payment', 'erp')
     * @return array
     */
    public function getDriversBySystemType(string $systemType): array
    {
        $allDrivers = $this->getAvailableDrivers();
        $filteredDrivers = [];

        foreach ($allDrivers as $driverName => $driverInfo) {
            if ($driverInfo['system_type'] === $systemType) {
                $filteredDrivers[$driverName] = $driverInfo;
            }
        }

        return $filteredDrivers;
    }

    /**
     * Получить драйверы с иконками определенного класса
     * 
     * @param string $iconClass CSS класс иконки
     * @return array
     */
    public function getDriversByIconClass(string $iconClass): array
    {
        $allDrivers = $this->getAvailableDrivers();
        $filteredDrivers = [];

        foreach ($allDrivers as $driverName => $driverInfo) {
            if ($driverInfo['icon_class'] === $iconClass) {
                $filteredDrivers[$driverName] = $driverInfo;
            }
        }

        return $filteredDrivers;
    }

    /**
     * Получить уникальные типы систем из всех драйверов
     * 
     * @return array
     */
    public function getAvailableSystemTypes(): array
    {
        $drivers = $this->getAvailableDrivers();
        $systemTypes = [];

        foreach ($drivers as $driverInfo) {
            $systemType = $driverInfo['system_type'];
            if (!in_array($systemType, $systemTypes)) {
                $systemTypes[] = $systemType;
            }
        }

        return $systemTypes;
    }

    /**
     * Получить уникальные классы иконок из всех драйверов
     * 
     * @return array
     */
    public function getAvailableIconClasses(): array
    {
        $drivers = $this->getAvailableDrivers();
        $iconClasses = [];

        foreach ($drivers as $driverInfo) {
            $iconClass = $driverInfo['icon_class'];
            if (!in_array($iconClass, $iconClasses)) {
                $iconClasses[] = $iconClass;
            }
        }

        return $iconClasses;
    }
}