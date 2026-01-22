<?php

namespace App\Modules\ExchangeOneCVeloshop\Controllers;

use App\Core\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Modules\ExchangeOneCVeloshop\Services\ConnectionCheckService;
use App\Modules\ExchangeOneCVeloshop\Services\DataParserService;
use Illuminate\View\View;

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

    public function index(): Factory|\Illuminate\Contracts\View\View
    {
        return view('exchangeonecveloshop::index', [
            //'connectionHealth' => $this->connectionService->check(config('exchange1c.api_url'), 5),
        ]);
    }

    /**
     * Получить список товаров из 1С
     *
     * @return JsonResponse
     */
    public function getProducts(): JsonResponse
    {
        $getProductsResult = $this->dataParserService->getProducts();

        return response()->json($getProductsResult, $getProductsResult['success'] ? 200 : 500);
    }

    public function importProducts(): JsonResponse
    {
        return response()->json($this->dataParserService->importProducts());
    }

    /**
     * Отобразить интерфейс для работы с товарами
     *
     * @return View
     */
    public function showProductsInterface(): View
    {
        Log::info('ExchangeController: Отображение интерфейса товаров');

        // Получаем данные для отображения
        $result = $this->dataParserService->fetchProducts();

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
