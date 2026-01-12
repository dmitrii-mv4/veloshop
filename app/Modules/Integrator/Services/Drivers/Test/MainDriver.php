<?php

namespace App\Modules\Integrator\Services\Drivers\Test;

use App\Modules\Integrator\Services\Interfaces\DriverInterface;
use App\Modules\Integrator\Services\Drivers\Test\Helpers\SettingsFormHelper;
use SoapClient;
use SoapFault;
use Exception;

/**
 * Драйвер для интеграции с внешним сервисом 1С для выгрузки товаров
 * 
 * Предоставляет функционал для подключения и обмена данными
 * с веб-сервисом 1С (товары, цены, остатки)
 */
class MainDriver implements DriverInterface
{
    /**
     * Название драйвера
     * 
     * @var string
     */
    protected string $name = 'Тестовый драйвер';

    /**
     * Тип системы
     * 
     * @var string
     */
    protected string $systemType = 'erp';

    /**
     * Описание драйвера
     * 
     * @var string
     */
    protected string $description = 'Драйвер для теста';

    /**
     * Версия драйвера
     * 
     * @var string
     */
    protected string $version = '1.0.0';

    /**
     * Иконка драйвера (HTML-код)
     * 
     * @var string
     */
    protected string $icon = '<i class="fas fa-bicycle fa-2x text-primary"></i>';

    /**
     * CSS класс иконки
     * 
     * @var string
     */
    protected string $iconClass = 'fas fa-bicycle';

    /**
     * Конфигурационные параметры
     * 
     * @var array
     */
    protected array $config = [];

    /**
     * Флаг инициализации
     * 
     * @var bool
     */
    protected bool $initialized = false;

    /**
     * Экземпляр SOAP клиента
     * 
     * @var SoapClient|null
     */
    protected ?SoapClient $soapClient = null;

    /**
     * Получить название драйвера
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Получить тип системы
     * 
     * @return string
     */
    public function getSystemType(): string
    {
        return $this->systemType;
    }

    /**
     * Получить описание драйвера
     * 
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Получить версию драйвера
     * 
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Получить HTML-код иконки драйвера
     * 
     * @return string HTML-код иконки
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * Получить CSS класс иконки
     * 
     * @return string Название класса иконки
     */
    public function getIconClass(): string
    {
        return $this->iconClass;
    }

    /**
     * Получить HTML-форму настроек подключения
     * 
     * @return string HTML-код формы
     */
    public function getSettingsForm(): string
    {
        return SettingsFormHelper::getFormWithValues($this->config);
    }

    /**
     * Инициализация драйвера с настройками
     * 
     * @param array $config Конфигурационные параметры
     * @return void
     */
    public function initialize(array $config = []): void
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->initialized = true;
    }

    /**
     * Проверить соединение с внешней системой
     * 
     * @return bool
     */
    public function testConnection(): bool
    {
        if (!$this->initialized) {
            throw new \RuntimeException('Драйвер не инициализирован. Вызовите метод initialize() перед использованием.');
        }

        try {
            // Создаем SOAP клиент для проверки соединения
            $options = [
                'login' => $this->config['1c_login'],
                'password' => $this->config['1c_password'],
                'connection_timeout' => (int)$this->config['1c_timeout'],
                'trace' => (bool)$this->config['1c_debug_mode'],
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
            ];

            $client = new SoapClient($this->config['1c_url'], $options);
            
            // Пробуем вызвать простой метод для проверки соединения
            if (method_exists($client, 'TestConnection')) {
                $result = $client->TestConnection();
                return isset($result->return) && $result->return === true;
            }
            
            // Если метода TestConnection нет, пытаемся получить версию
            if (method_exists($client, 'GetVersion')) {
                $result = $client->GetVersion();
                return !empty($result->return);
            }
            
            // Просто проверяем, что можем создать клиент
            return true;
            
        } catch (SoapFault $e) {
            if ($this->config['1c_debug_mode']) {
                error_log('Ошибка подключения к 1С: ' . $e->getMessage());
            }
            return false;
        } catch (Exception $e) {
            if ($this->config['1c_debug_mode']) {
                error_log('Общая ошибка при подключении к 1С: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Получить товары из 1С
     * 
     * @param array $params Параметры запроса
     * @return array
     */
    public function fetchData(string $endpoint = 'products', array $params = []): array
    {
        if (!$this->initialized) {
            throw new \RuntimeException('Драйвер не инициализирован. Вызовите метод initialize() перед использованием.');
        }

        try {
            $this->initializeSoapClient();
            
            $methodName = $this->getMethodName($endpoint);
            
            if (!method_exists($this->soapClient, $methodName)) {
                throw new \RuntimeException("Метод {$methodName} не найден в веб-сервисе 1С");
            }
            
            // Подготавливаем параметры запроса
            $requestParams = $this->prepareRequestParams($endpoint, $params);
            
            // Выполняем запрос к 1С
            $response = $this->soapClient->$methodName($requestParams);
            
            // Обрабатываем ответ
            return $this->processResponse($endpoint, $response);
            
        } catch (SoapFault $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка SOAP при получении данных: ' . $e->getMessage(),
                'error_code' => $e->getCode(),
                'timestamp' => now()->toDateTimeString()
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка при получении данных: ' . $e->getMessage(),
                'timestamp' => now()->toDateTimeString()
            ];
        }
    }

    /**
     * Отправить данные в 1С (например, заказы)
     * 
     * @param string $endpoint Эндпоинт API
     * @param array $data Данные для отправки
     * @return bool
     */
    public function sendData(string $endpoint, array $data): bool
    {
        if (!$this->initialized) {
            throw new \RuntimeException('Драйвер не инициализирован. Вызовите метод initialize() перед использованием.');
        }

        try {
            $this->initializeSoapClient();
            
            $methodName = $this->getMethodName($endpoint, true);
            
            if (!method_exists($this->soapClient, $methodName)) {
                throw new \RuntimeException("Метод {$methodName} не найден в веб-сервисе 1С");
            }
            
            // Подготавливаем данные для отправки
            $requestData = $this->prepareSendData($endpoint, $data);
            
            // Отправляем данные в 1С
            $response = $this->soapClient->$methodName($requestData);
            
            // Проверяем успешность операции
            return $this->checkSendSuccess($endpoint, $response);
            
        } catch (SoapFault $e) {
            if ($this->config['1c_debug_mode']) {
                error_log('Ошибка отправки данных в 1С: ' . $e->getMessage());
            }
            return false;
        } catch (Exception $e) {
            if ($this->config['1c_debug_mode']) {
                error_log('Общая ошибка отправки в 1С: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Получить конфигурацию по умолчанию
     * 
     * @return array
     */
    protected function getDefaultConfig(): array
    {
        return [
            '1c_url' => '',
            '1c_sync_interval' => '15',
            '1c_login' => '',
            '1c_password' => '',
            '1c_timeout' => 30,
            '1c_debug_mode' => false,
            '1c_entity_type' => 'products',
            '1c_sync_method' => 'full',
        ];
    }

    /**
     * Получить текущую конфигурацию
     * 
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Проверить, инициализирован ли драйвер
     * 
     * @return bool
     */
    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    /**
     * Инициализировать SOAP клиент
     * 
     * @return void
     * @throws SoapFault
     */
    protected function initializeSoapClient(): void
    {
        if (!$this->soapClient) {
            $options = [
                'login' => $this->config['1c_login'],
                'password' => $this->config['1c_password'],
                'connection_timeout' => (int)$this->config['1c_timeout'],
                'trace' => (bool)$this->config['1c_debug_mode'],
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'encoding' => 'UTF-8',
                'soap_version' => SOAP_1_2,
            ];

            $this->soapClient = new SoapClient($this->config['1c_url'], $options);
        }
    }

    /**
     * Получить имя метода для вызова
     * 
     * @param string $endpoint Эндпоинт
     * @param bool $isSend Флаг отправки данных
     * @return string
     */
    protected function getMethodName(string $endpoint, bool $isSend = false): string
    {
        $methods = [
            'products' => $isSend ? 'SetProducts' : 'GetProducts',
            'categories' => $isSend ? 'SetCategories' : 'GetCategories',
            'prices' => $isSend ? 'SetPrices' : 'GetPrices',
            'stock' => $isSend ? 'SetStock' : 'GetStock',
            'orders' => $isSend ? 'SetOrder' : 'GetOrders',
        ];

        return $methods[$endpoint] ?? ($isSend ? 'SetData' : 'GetData');
    }

    /**
     * Подготовить параметры запроса
     * 
     * @param string $endpoint Эндпоинт
     * @param array $params Параметры
     * @return array
     */
    protected function prepareRequestParams(string $endpoint, array $params): array
    {
        $defaultParams = [
            'date_from' => date('Y-m-d', strtotime('-1 month')),
            'date_to' => date('Y-m-d'),
            'limit' => 100,
            'offset' => 0,
            'only_active' => true,
        ];

        $params = array_merge($defaultParams, $params);

        // Преобразуем параметры для SOAP
        $soapParams = [];
        foreach ($params as $key => $value) {
            $soapParams[$key] = $value;
        }

        return $soapParams;
    }

    /**
     * Обработать ответ от 1С
     * 
     * @param string $endpoint Эндпоинт
     * @param mixed $response Ответ от SOAP
     * @return array
     */
    protected function processResponse(string $endpoint, $response): array
    {
        if (!isset($response->return)) {
            return [
                'status' => 'error',
                'message' => 'Некорректный ответ от сервера 1С',
                'timestamp' => now()->toDateTimeString()
            ];
        }

        $result = $response->return;
        
        // Преобразуем объект в массив
        if (is_object($result)) {
            $result = json_decode(json_encode($result), true);
        }

        return [
            'status' => 'success',
            'endpoint' => $endpoint,
            'data' => $result,
            'timestamp' => now()->toDateTimeString(),
            'items_count' => is_array($result) ? count($result) : 1
        ];
    }

    /**
     * Подготовить данные для отправки
     * 
     * @param string $endpoint Эндпоинт
     * @param array $data Данные
     * @return array
     */
    protected function prepareSendData(string $endpoint, array $data): array
    {
        // Базовая структура данных для отправки
        $baseData = [
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'timestamp' => date('Y-m-d H:i:s'),
            'source' => 'Kotiks CMS',
        ];

        // Добавляем специфичные поля для разных эндпоинтов
        switch ($endpoint) {
            case 'orders':
                $baseData['order_type'] = $data['type'] ?? 'web_order';
                $baseData['order_number'] = $data['number'] ?? '';
                break;
            case 'products':
                $baseData['action'] = $data['action'] ?? 'update';
                break;
        }

        return $baseData;
    }

    /**
     * Проверить успешность отправки
     * 
     * @param string $endpoint Эндпоинт
     * @param mixed $response Ответ
     * @return bool
     */
    protected function checkSendSuccess(string $endpoint, $response): bool
    {
        if (!isset($response->return)) {
            return false;
        }

        $result = $response->return;
        
        if (is_object($result)) {
            $result = json_decode(json_encode($result), true);
        }

        if (is_array($result)) {
            return isset($result['success']) && $result['success'] === true;
        }

        return (bool)$result;
    }
}