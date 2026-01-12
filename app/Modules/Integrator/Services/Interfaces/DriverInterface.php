<?php

namespace App\Modules\Integrator\Services\Interfaces;

/**
 * Интерфейс для всех драйверов интеграции
 * 
 * Определяет обязательные методы, которые должны быть реализованы
 * в каждом драйвере интеграции
 */
interface DriverInterface
{
    /**
     * Получить название драйвера
     * 
     * @return string
     */
    public function getName(): string;

    /**
     * Получить тип системы
     * 
     * @return string (например: 'crm', 'payment', 'erp', 'cms')
     */
    public function getSystemType(): string;

    /**
     * Получить описание драйвера
     * 
     * @return string
     */
    public function getDescription(): string;

    /**
     * Получить версию драйвера
     * 
     * @return string
     */
    public function getVersion(): string;

    /**
     * Получить HTML-код иконки драйвера
     * 
     * @return string HTML-код иконки (например: <i class="fas fa-database"></i>)
     */
    public function getIcon(): string;

    /**
     * Получить CSS класс иконки
     * 
     * @return string Название класса иконки (например: fas fa-database)
     */
    public function getIconClass(): string;

    /**
     * Получить HTML-форму настроек подключения
     * 
     * @return string HTML-код формы
     */
    public function getSettingsForm(): string;

    /**
     * Инициализация драйвера с настройками
     * 
     * @param array $config Конфигурационные параметры
     * @return void
     */
    public function initialize(array $config = []): void;

    /**
     * Проверить соединение с внешней системой
     * 
     * @return bool
     */
    public function testConnection(): bool;

    /**
     * Получить данные из внешней системы
     * 
     * @param string $endpoint Эндпоинт API
     * @param array $params Параметры запроса
     * @return array
     */
    public function fetchData(string $endpoint, array $params = []): array;

    /**
     * Отправить данные во внешнюю систему
     * 
     * @param string $endpoint Эндпоинт API
     * @param array $data Данные для отправки
     * @return bool
     */
    public function sendData(string $endpoint, array $data): bool;
}