<?php

namespace App\Modules\ExchangeOneCVeloshop\Services;

use App\Modules\Catalog\Models\Goods;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;
use App\Modules\ExchangeOneCVeloshop\Services\Traits\UrlHelperTrait;
use Psr\Log\LoggerInterface;

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
     * Возвращает логгер для обмена с 1С
     *
     * Логи пишутся в отдельный файл:
     * storage/logs/exchangeonecveloshop/exchange.log
     *
     * @return LoggerInterface
     */
    protected function getExchangeLogger(): LoggerInterface
    {
        $logDir = storage_path('logs/exchangeonecveloshop');

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        return Log::build([
            'driver' => 'single',
            'path' => $logDir . '/exchange.log',
            'level' => 'info',
        ]);
    }

    /**
     * Константа по умолчанию для таймаута запроса (секунды)
     *
     * @var int
     */
    const int DEFAULT_TIMEOUT = 120;

    /**
     * URL API 1С по умолчанию
     *
     * @var string
     */
    const string DEFAULT_API_URL = 'http://176.62.189.27:62754/im/4371601201/?type=json';

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
    public function fetchProducts(string $url = self::DEFAULT_API_URL, int $limit = 3, int $timeout = self::DEFAULT_TIMEOUT): array
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

    public function getProducts(): array
    {
        Log::info('ExchangeController: Начало получения товаров из 1С');

        try {
            $url = self::DEFAULT_API_URL;
            $timeout = self::DEFAULT_TIMEOUT;

            Log::debug('ExchangeController: Параметры запроса товаров', [
                'url' => $this->maskUrl($url),
                'timeout' => $timeout
            ]);

            // Получаем данные о товарах
            $result = $this->fetchProducts(url: $url, timeout: $timeout);

            Log::info('ExchangeController: Получение товаров завершено', [
                'success' => $result['success'],
                'total_products' => $result['total_products'] ?? 0
            ]);

            return [
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message'],
                'data' => [
                    'products' => $result['products'],
                    'total' => $result['total_products'] ?? 0,
                    'request_params' => [
                        'url' => $this->maskUrl($url),
                        'timeout' => $timeout
                    ]
                ],
                'debug' => config('app.debug') ? [
                    'raw_sample' => $result['raw_data_sample'] ?? null
                ] : null
            ];

        } catch (Exception $e) {
            Log::error('ExchangeController: Ошибка при получении товаров', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'trace' => config('app.debug') ? $e->getTraceAsString() : 'disabled'
            ]);

            return [
                'status' => 'error',
                'message' => 'Внутренняя ошибка сервера при получении товаров',
                'error' => config('app.debug') ? $e->getMessage() : null
            ];
        }
    }

    public function saveProducts(array $productsData): array
    {
        $logger = $this->getExchangeLogger();

        $products = $productsData['products'] ?? [];
        $total = is_array($products) ? count($products) : 0;

        $logger->info('Начало сохранения товаров из 1С', [
            'total_products' => $total,
            'timestamp' => now()->toDateTimeString(),
        ]);

        if (!is_array($products) || $total === 0) {
            $logger->warning('Список товаров для сохранения пуст или имеет некорректный формат', [
                'products_type' => gettype($products),
            ]);

            return [
                'status' => 'error',
                'message' => 'Нет товаров для сохранения',
                'data' => [
                    'saved' => 0,
                    'failed' => 0,
                    'total' => 0,
                ],
            ];
        }

        $saved = 0;
        $failed = 0;

        foreach ($products as $productData) {
            if (empty($productData['articul']) || empty($productData['name'])) {
                $logger->error('Ошибка при сохранении товара', [
                    'article' => $productData['article'] ?? 'empty',
                    'name' => $productData['name'] ?? 'empty',
                    'message' => 'Name or articul is required',
                ]);

                continue;
            }

            try {
                $goods = Goods::updateOrCreate(
                    ['article' => $productData['article']],
                    [
                        'name' => $productData['name'],
                    ]
                );

                $saved++;

                $logger->info('Товар успешно сохранён', [
                    'article' => $productData['article'],
                    'name' => $productData['name'],
                    'goods_id' => $goods->id,
                ]);
            } catch (Exception $e) {
                $failed++;

                $logger->error('Ошибка при сохранении товара', [
                    'article' => $productData['article'] ?? 'empty',
                    'name' => $productData['name'] ?? 'empty',
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $logger->info('Сохранение товаров завершено', [
            'total_products' => $total,
            'saved' => $saved,
            'failed' => $failed,
            'timestamp' => now()->toDateTimeString(),
        ]);

        $status = $failed === 0 ? 'success' : ($saved > 0 ? 'partial' : 'error');
        $message = match ($status) {
            'success' => 'Все товары успешно сохранены',
            'partial' => 'Часть товаров не удалось сохранить',
            default => 'Не удалось сохранить товары',
        };

        return [
            'status' => $status,
            'message' => $message,
            'data' => [
                'saved' => $saved,
                'failed' => $failed,
                'total' => $total,
            ],
        ];
    }

    public function importProducts(): array
    {
        $getProductsResult = $this->getProducts();
        if ($getProductsResult['status'] == 'error') {
            return [
                'status' => $getProductsResult['status'],
                'message' => $getProductsResult['message']
            ];
        }

        return $this->saveProducts($getProductsResult['data']);
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
