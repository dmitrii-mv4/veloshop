<?php

namespace App\Modules\ExchangeOneCVeloshop\Services;

use App\Modules\Catalog\Models\CatalogWarehouse;
use App\Modules\Catalog\Models\Product;
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
    const int DEFAULT_TIMEOUT = 300;

    /**
     * URL API 1С по умолчанию
     *
     * @var string
     */
    const string DEFAULT_API_URL = 'http://176.62.189.27:62754/im/4371601201/?type=json&deep=7&noprops';

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
     * Получает и парсит данные одним вызовом
     *
     * @param string $url URL API 1С
     * @param int $limit Лимит товаров (0 - нет лимита)
     * @param int $timeout Таймаут запроса
     * @return array Результат с данными и статусом
     */
    public function fetchProducts(string $url = self::DEFAULT_API_URL, int $limit = 0, int $timeout = self::DEFAULT_TIMEOUT): array
    {
        $data = $this->fetchData($url, $timeout);

        if ($data === null) {
            return [
                'success' => false,
                'message' => 'Не удалось получить данные с API 1С',
                'products' => []
            ];
        }

        return [
            'success' => true,
            'message' => 'Данные успешно получены',
            'total_products' => count($data['models']),
            'products' => $data['models'],
            'raw_data_sample' => $this->getDataSample($data)
        ];
    }

    public function saveProducts(array $products): array
    {
        $logger = $this->getExchangeLogger();

        $total = count($products);

        $logger->info('Начало сохранения товаров из 1С', [
            'total_products' => $total,
            'timestamp' => now()->toDateTimeString(),
        ]);

        if ($total === 0) {
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

        foreach ($products as $productID => $productData) {
            if (empty($productData['main']) ||
            empty($productData['main']['brend']) ||
            empty($productData['main']['model']) ||
            empty($productData['main']['sezon'])) {
                $logger->error('Ошибка при сохранении товара', [
                    'product_id' => $productID,
                    'productData' => $productData,
                    'message' => 'Brand, model and season are required',
                ]);

                continue;
            }

            try {
                $productModel = Product::updateOrCreate(
                    ['product_id' => $productID],
                    [
                        'name' => !empty($productData['name']) ? $productData['name'] : "",
                        'group_name' => !empty($productData['main']['group']) ? $productData['main']['group'] : "",
                        'brand' => $productData['main']['brend'],
                        'model' => $productData['main']['model'],
                        'seazon' => $productData['main']['sezon'],
                    ]
                );

                if (!empty($productData['offers'])) {
                    foreach ($productData['offers'] as $offerID => $offerData) {
                        /* TODO: пока все поля не обязательные
                         * if (empty($offerData['props']) ||
                            empty($offerData['props']['articul']) ||
                            empty($offerData['props']['name'])) {
                            $logger->error('Ошибка при сохранении товара', [
                                'offer_id' => $offerID,
                                'offerData' => $offerData,
                                'message' => 'Articul and name are required',
                            ]);

                            continue;
                        }*/

                        $productModel->offers()->updateOrCreate(
                            ['offer_id' => $offerID],
                            [
                                'articul_supplier' => !empty($offerData['props']['articul']) ? $offerData['props']['articul'] : "",
                                'name' => !empty($offerData['props']['name']) ? $offerData['props']['name'] : "",
                            ]
                        );
                    }
                }

                $saved++;

                $logger->info('Товар успешно сохранён', [
                    'productID' => $productID,
                    'name' => $productData['name'],
                    'product_internal_id' => $productModel->id,
                ]);
            } catch (Exception $e) {
                $failed++;

                $logger->error('Ошибка при сохранении товара', [
                    'articul' => $productData['articul'] ?? 'empty',
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

    public function saveStock(array $products): array
    {
        $logger = $this->getExchangeLogger();

        $total = count($products);

        $logger->info('Начало сохранения остатков из 1С', [
            'total_products' => $total,
            'timestamp' => now()->toDateTimeString(),
        ]);

        if ($total === 0) {
            $logger->warning('Список товаров для сохранения остатков пуст или имеет некорректный формат', [
                'products_type' => gettype($products),
            ]);

            return [
                'status' => 'error',
                'message' => 'Нет остатков для сохранения',
                'data' => [
                    'saved' => 0,
                    'failed' => 0,
                    'total' => 0,
                ],
            ];
        }

        $saved = 0;
        $failed = 0;

        foreach ($products as $productID => $productData) {
            try {
                $productModel = Product::findByProductId($productID);
                if (empty($productModel)) {
                    continue;
                }

                if (!empty($productData['offers'])) {
                    foreach ($productData['offers'] as $offerID => $offerData) {
                        if (empty($offerData['sklad'])) {
                            continue;
                        }

                        $offer = null;
                        $offers = $productModel->offers()->find(['offer_id' => $offerID]);

                        if (empty($offers)) {
                            $logger->warning('Оффер для обновления остатков не найден', [
                                'offer_id' => $offerID,
                                'offer_data' => $offerData,
                            ]);
                            continue;
                        }

                        $offer = $offers->first();

                        // обнулить текущие остатки

                        foreach ($offerData['sklad'] as $skladID => $skladQty) {
                            // создать склад, если нету
                            $warehouse = CatalogWarehouse::where(['warehouse_id' => $skladID])->first();
                            if (empty($warehouse)) {
                                $warehouse = CatalogWarehouse::createWithLog([
                                    'warehouse_id' => $skladID,
                                    'title' => $skladID
                                ]);
                            }


                        }
                    }
                }

                $saved++;

                $logger->info('Остатки обновлены', [
                    'productID' => $productID,
                    'name' => $productData['name'],
                    'product_internal_id' => $productModel->id,
                ]);
            } catch (Exception $e) {
                $failed++;

                $logger->error('Ошибка при обновлении остатков', [
                    'articul' => $productData['articul'] ?? 'empty',
                    'name' => $productData['name'] ?? 'empty',
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $logger->info('Обновление остатков завершено', [
            'total_products' => $total,
            'saved' => $saved,
            'failed' => $failed,
            'timestamp' => now()->toDateTimeString(),
        ]);

        $status = $failed === 0 ? 'success' : ($saved > 0 ? 'partial' : 'error');
        $message = match ($status) {
            'success' => 'Остатки успешно обновлены',
            'partial' => 'Часть остатков не удалось обновить',
            default => 'Не удалось обновить остатки',
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
        $getProductsResult = $this->fetchProducts();
        if (!$getProductsResult['success']) {
            return [
                'status' => 'error',
                'message' => $getProductsResult['message']
            ];
        }

        return $this->saveProducts($getProductsResult['products']);
    }

    public function importStock(): array
    {
        $getStockResult = $this->fetchData(self::DEFAULT_API_URL . '&updater');
        if (empty($getStockResult['models'])) {
            return [
                'status' => 'error',
                'message' => 'Ошибка получения остатков товаров'
            ];
        }

        return $this->saveStock($getStockResult['models']);
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
                            'articul_supplier' => $offer['props']['articul_supplier'] ?? null,
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
