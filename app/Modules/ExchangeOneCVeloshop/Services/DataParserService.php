<?php

namespace App\Modules\ExchangeOneCVeloshop\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;
use App\Modules\ExchangeOneCVeloshop\Services\Traits\UrlHelperTrait;

/**
 * Сервис парсинга данных из API 1С
 *
 * Основной функционал:
 * - Получение JSON данных с сервера 1С
 * - Извлечение информации о товарах
 * - Обработка и преобразование данных
 * - Логирование процесса парсинга
 *
 *  Для работы необходимо установить пакет: composer require guzzlehttp/guzzle
 */
class DataParserService
{
    use UrlHelperTrait;

    /**
     * Константа по умолчанию для таймаута запроса (секунды)
     *
     * @var int
     */
    const DEFAULT_TIMEOUT = 120;

    /**
     * URL API 1С по умолчанию
     *
     * @var string
     */
    const DEFAULT_API_URL = 'http://176.62.189.27:62754/im/4371601201/?type=json&deep=2';

    /**
     * Получает данные с API 1С
     *
     * @param string $url URL API 1С
     * @param int $timeout Таймаут запроса в секундах
     * @return array|null Массив данных или null при ошибке
     */
    public function fetchData(string $url = self::DEFAULT_API_URL, int $timeout = self::DEFAULT_TIMEOUT): ?array
    {
        Log::info('DataParserService: Начало получения данных с API 1С', [
            'url' => $this->maskUrl($url),
            'timeout' => $timeout
        ]);

        try {
            // Валидация URL
            if (!$this->validateUrl($url, true, 'DataParserService')) {
                Log::error('DataParserService: Некорректный URL', ['url' => $url]);
                return null;
            }

            // Выполнение HTTP запроса
            $response = Http::timeout($timeout)
                ->retry(3, 1000) // 3 попытки с задержкой 1 секунда
                ->withHeaders([
                    'User-Agent' => 'Kotiks CMS/1.0',
                    'Accept' => 'application/json',
                ])
                ->get($url);

            // Проверка успешности запроса
            if (!$response->successful()) {
                Log::error('DataParserService: Ошибка HTTP запроса', [
                    'url' => $this->maskUrl($url),
                    'status_code' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            // Парсинг JSON
            $data = $response->json();

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('DataParserService: Ошибка парсинга JSON', [
                    'url' => $this->maskUrl($url),
                    'json_error' => json_last_error_msg()
                ]);
                return null;
            }

            Log::info('DataParserService: Данные успешно получены', [
                'url' => $this->maskUrl($url),
                'data_structure' => $this->analyzeDataStructure($data)
            ]);

            return $data;

        } catch (Exception $e) {
            Log::error('DataParserService: Исключение при получении данных', [
                'url' => $this->maskUrl($url),
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'trace' => config('app.debug') ? $e->getTraceAsString() : 'disabled'
            ]);
            return null;
        }
    }

    /**
     * Извлекает 3 первых товара из данных
     *
     * @param array $data Массив данных от API 1С
     * @param int $limit Лимит товаров (по умолчанию 3)
     * @return array Массив товаров с артикулом и названием
     */
    public function extractProducts(array $data, int $limit = 3): array
    {
        Log::info('DataParserService: Начало извлечения товаров', [
            'limit' => $limit,
            'data_keys' => array_keys($data)
        ]);

        $products = [];
        $count = 0;

        try {
            // Проверка структуры данных
            if (!isset($data['models']) || !is_array($data['models'])) {
                Log::warning('DataParserService: Некорректная структура данных - отсутствует models', [
                    'available_keys' => array_keys($data)
                ]);
                return $products;
            }

            // Итерация по моделям и предложениям
            foreach ($data['models'] as $modelId => $model) {
                if (!isset($model['offers']) || !is_array($model['offers'])) {
                    continue;
                }

                foreach ($model['offers'] as $offerId => $offer) {
                    // Проверяем наличие требуемых полей
                    if (isset($offer['props']['articul'], $offer['props']['name'])) {
                        $products[] = [
                            'model_id' => $modelId,
                            'offer_id' => $offerId,
                            'articul' => $offer['props']['articul'],
                            'name' => $offer['props']['name'],
                            'full_data' => $offer // Сохраняем полные данные для возможного дальнейшего использования
                        ];

                        $count++;
                        Log::debug('DataParserService: Товар добавлен', [
                            'articul' => $offer['props']['articul'],
                            'name' => $offer['props']['name']
                        ]);

                        // Прерываем цикл при достижении лимита
                        if ($count >= $limit) {
                            break 2;
                        }
                    }
                }
            }

            Log::info('DataParserService: Извлечение товаров завершено', [
                'total_found' => $count,
                'limit' => $limit
            ]);

        } catch (Exception $e) {
            Log::error('DataParserService: Ошибка при извлечении товаров', [
                'message' => $e->getMessage(),
                'exception' => get_class($e)
            ]);
        }

        return $products;
    }

    /**
     * Получает и парсит данные одним вызовом
     *
     * @param string $url URL API 1С
     * @param int $limit Лимит товаров
     * @param int $timeout Таймаут запроса
     * @return array Результат с данными и статусом
     */
    public function getProducts(string $url = self::DEFAULT_API_URL, int $limit = 3, int $timeout = self::DEFAULT_TIMEOUT): array
    {
        $data = $this->fetchData($url, $timeout);

        if ($data === null) {
            return [
                'success' => false,
                'message' => 'Не удалось получить данные с API 1С',
                'products' => []
            ];
        }

        $products = $this->extractProducts($data, $limit);

        return [
            'success' => true,
            'message' => 'Данные успешно получены',
            'total_products' => count($products),
            'products' => $products,
            'raw_data_sample' => $this->getDataSample($data)
        ];
    }

    /**
     * Анализирует структуру данных
     *
     * @param array $data Данные для анализа
     * @return array Информация о структуре
     */
    protected function analyzeDataStructure(array $data): array
    {
        $analysis = [
            'has_models' => isset($data['models']),
            'models_count' => 0,
            'offers_count' => 0
        ];

        if ($analysis['has_models']) {
            $analysis['models_count'] = count($data['models']);

            foreach ($data['models'] as $model) {
                if (isset($model['offers'])) {
                    $analysis['offers_count'] += count($model['offers']);
                }
            }
        }

        return $analysis;
    }

    /**
     * Получает образец данных для отладки
     *
     * @param array $data Полные данные
     * @return array Упрощенный образец
     */
    protected function getDataSample(array $data): array
    {
        $sample = [];
        $count = 0;

        if (isset($data['models'])) {
            foreach ($data['models'] as $modelId => $model) {
                if ($count >= 2) break;

                if (isset($model['offers'])) {
                    foreach ($model['offers'] as $offerId => $offer) {
                        if ($count >= 2) break;

                        $sample[$modelId][$offerId] = [
                            'articul' => $offer['props']['articul'] ?? null,
                            'name' => $offer['props']['name'] ?? null
                        ];
                        $count++;
                    }
                }
            }
        }

        return $sample;
    }
}
