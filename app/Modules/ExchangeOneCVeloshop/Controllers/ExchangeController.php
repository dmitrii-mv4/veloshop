<?php

namespace App\Modules\ExchangeOneCVeloshop\Controllers;

use App\Core\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Modules\ExchangeOneCVeloshop\Services\ConnectionCheckService;
use App\Modules\ExchangeOneCVeloshop\Services\DataParserService;

/**
 * Контроллер для управления обменом с 1C Veloshop
 * 
 * Основной функционал:
 * - Проверка соединения с сервером 1С
 * - Получение и парсинг данных товаров
 * - Управление настройками обмена
 * - Отображение статуса обмена
 */
class ExchangeController extends Controller
{
    /**
     * Сервис проверки соединения
     * 
     * @var ConnectionCheckService
     */
    protected ConnectionCheckService $connectionService;
    
    /**
     * Сервис парсинга данных
     * 
     * @var DataParserService
     */
    protected DataParserService $dataParserService;

    /**
     * Конструктор контроллера
     * 
     * @param ConnectionCheckService $connectionService
     * @param DataParserService $dataParserService
     */
    public function __construct(
        ConnectionCheckService $connectionService,
        DataParserService $dataParserService
    ) {
        $this->connectionService = $connectionService;
        $this->dataParserService = $dataParserService;
    }

    public function index()
    {
        //return view('exchangeonecveloshop::index');


        $connected = $this->connectionService->check('http://176.62.189.27:62755/im/4371601201/?type=json&deep=5', 5);

        dd($connected);
    }

    /**
     * Получить список товаров из 1С
     * 
     * @param Request $request Объект HTTP-запроса
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProducts(Request $request)
    {
        Log::info('ExchangeController: Начало получения товаров из 1С');
        
        try {
            // Получаем параметры из запроса
            $url = $request->input('url', DataParserService::DEFAULT_API_URL);
            $limit = $request->input('limit', 3);
            $timeout = $request->input('timeout', DataParserService::DEFAULT_TIMEOUT);

            Log::debug('ExchangeController: Параметры запроса товаров', [
                'url' => $this->dataParserService->maskUrl($url),
                'limit' => $limit,
                'timeout' => $timeout
            ]);

            // Получаем данные о товарах
            $result = $this->dataParserService->getProducts($url, $limit, $timeout);

            Log::info('ExchangeController: Получение товаров завершено', [
                'success' => $result['success'],
                'total_products' => $result['total_products'] ?? 0
            ]);

            return response()->json([
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message'],
                'data' => [
                    'products' => $result['products'],
                    'total' => $result['total_products'] ?? 0,
                    'request_params' => [
                        'url' => $this->dataParserService->maskUrl($url),
                        'limit' => $limit,
                        'timeout' => $timeout
                    ]
                ],
                'debug' => config('app.debug') ? [
                    'raw_sample' => $result['raw_data_sample'] ?? null
                ] : null
            ], $result['success'] ? 200 : 500);

        } catch (\Exception $e) {
            Log::error('ExchangeController: Ошибка при получении товаров', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'trace' => config('app.debug') ? $e->getTraceAsString() : 'disabled'
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Внутренняя ошибка сервера при получении товаров',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Отобразить интерфейс для работы с товарами
     * 
     * @return \Illuminate\View\View
     */
    public function showProductsInterface()
    {
        Log::info('ExchangeController: Отображение интерфейса товаров');
        
        // Получаем данные для отображения
        $result = $this->dataParserService->getProducts();
        
        return view('exchangeonecveloshop::products', [
            'products' => $result['products'] ?? [],
            'total' => $result['total_products'] ?? 0,
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? '',
            'default_url' => DataParserService::DEFAULT_API_URL,
            'default_limit' => 3
        ]);
    }
}