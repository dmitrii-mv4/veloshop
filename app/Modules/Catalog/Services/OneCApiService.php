<?php

namespace App\Modules\Catalog\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Сервис для работы с API 1С
 * 
 * Реализует логику подключения к 1С с несколькими вариантами URL
 * и стратегиями обработки ошибок
 */
class OneCApiService
{
    /**
     * Основные URL для подключения к 1С
     */
    private array $apiUrls = [];
    
    /**
     * Параметры запроса
     */
    private array $params = [];
    
    /**
     * Настройки HTTP-клиента
     */
    private array $httpConfig = [];
    
    /**
     * Конструктор сервиса
     */
    public function __construct()
    {
        $config = config('catalog.api', []);
        
        $this->apiUrls = array_merge(
            [$config['url'] ?? ''],
            $config['alternatives'] ?? []
        );
        
        $this->params = $config['params'] ?? ['type' => 'json'];
        $this->httpConfig = [
            'timeout' => $config['timeout'] ?? 30,
            'connect_timeout' => $config['connect_timeout'] ?? 10,
            'retries' => $config['retries'] ?? 2,
            'retry_delay' => $config['retry_delay'] ?? 3,
        ];
    }
    
    /**
     * Получение данных из 1С
     * 
     * @param array $params Дополнительные параметры
     * @return array|null
     */
    public function fetchData(array $params = []): ?array
    {
        $finalParams = array_merge($this->params, $params);
        
        // Пробуем все доступные URL
        foreach ($this->apiUrls as $url) {
            if (empty($url)) {
                continue;
            }
            
            Log::info('Попытка подключения к 1С', [
                'url' => $url,
                'params' => $finalParams
            ]);
            
            $data = $this->tryFetch($url, $finalParams);
            
            if ($data !== null) {
                Log::info('Успешное подключение к 1С', [
                    'url' => $url,
                    'items_count' => count($data)
                ]);
                return $data;
            }
        }
        
        Log::error('Все попытки подключения к 1С завершились неудачей');
        return null;
    }
    
    /**
     * Попытка получения данных с одного URL
     * 
     * @param string $url
     * @param array $params
     * @return array|null
     */
    private function tryFetch(string $url, array $params): ?array
    {
        $retryCount = 0;
        
        while ($retryCount < $this->httpConfig['retries']) {
            try {
                $response = Http::timeout($this->httpConfig['timeout'])
                    ->connectTimeout($this->httpConfig['connect_timeout'])
                    ->get($url, $params);
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Проверяем корректность структуры ответа
                    if ($this->validateResponse($data)) {
                        return $data;
                    } else {
                        Log::warning('Некорректная структура ответа от 1С', ['data' => $data]);
                        return null;
                    }
                } else {
                    Log::warning('Ошибка HTTP при подключении к 1С', [
                        'status' => $response->status(),
                        'url' => $url
                    ]);
                }
                
            } catch (\Exception $e) {
                Log::warning('Исключение при подключении к 1С', [
                    'attempt' => $retryCount + 1,
                    'url' => $url,
                    'error' => $e->getMessage()
                ]);
            }
            
            $retryCount++;
            if ($retryCount < $this->httpConfig['retries']) {
                sleep($this->httpConfig['retry_delay']);
            }
        }
        
        return null;
    }
    
    /**
     * Валидация ответа от 1С
     * 
     * @param mixed $data
     * @return bool
     */
    private function validateResponse($data): bool
    {
        if (!is_array($data)) {
            return false;
        }
        
        // Проверяем разные форматы ответа
        if (isset($data['items']) && is_array($data['items'])) {
            return true;
        }
        
        // Проверяем, является ли сам ответ массивом товаров
        if (!empty($data) && is_array($data)) {
            $firstItem = reset($data);
            return isset($firstItem['id']) && isset($firstItem['name']);
        }
        
        return false;
    }
    
    /**
     * Тестирование подключения к 1С
     * 
     * @return array
     */
    public function testConnection(): array
    {
        $results = [];
        
        foreach ($this->apiUrls as $index => $url) {
            if (empty($url)) {
                continue;
            }
            
            $startTime = microtime(true);
            
            try {
                $response = Http::timeout(5)
                    ->connectTimeout(3)
                    ->get($url, ['type' => 'json']);
                
                $results[] = [
                    'url' => $url,
                    'status' => $response->status(),
                    'success' => $response->successful(),
                    'time' => round((microtime(true) - $startTime) * 1000) . 'ms',
                    'error' => null
                ];
                
            } catch (\Exception $e) {
                $results[] = [
                    'url' => $url,
                    'status' => 0,
                    'success' => false,
                    'time' => round((microtime(true) - $startTime) * 1000) . 'ms',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Получение информации о товарах из 1С (упрощенная версия)
     * 
     * @return array
     */
    public function getSampleProducts(): array
    {
        // Для тестирования возвращаем тестовые данные
        return [
            [
                'id' => '1',
                'name' => 'Тестовый товар 1',
                'code' => 'TEST001',
                'ncode' => 'ТЕСТ001',
                'cena' => 1000.00,
                'cena2' => 1200.00,
                'sklad' => [
                    ['name' => 'Основной склад', 'qty' => 10]
                ],
                'props' => [
                    ['propname' => 'Цвет', 'propval' => 'Красный'],
                    ['propname' => 'Размер', 'propval' => 'M']
                ]
            ],
            [
                'id' => '2',
                'name' => 'Тестовый товар 2',
                'code' => 'TEST002',
                'ncode' => 'ТЕСТ002',
                'cena' => 2000.00,
                'cena2' => null,
                'sklad' => [
                    ['name' => 'Основной склад', 'qty' => 5],
                    ['name' => 'Дополнительный склад', 'qty' => 3]
                ],
                'props' => []
            ]
        ];
    }
}