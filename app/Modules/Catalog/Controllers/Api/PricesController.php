<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Core\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Modules\Catalog\Models\CatalogTypePrice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PricesController extends Controller
{    
    /**
     * Получение всех типов цен для API
     * Возвращает список всех типов цен с их основными характеристиками
     *
     * @return JsonResponse
     */
    public function getPrices(): JsonResponse
    {
        try {
            Log::info('API Catalog: начало получения списка типов цен');
            
            // Получаем все типы цен с сортировкой по sort_order
            $priceTypes = CatalogTypePrice::orderBy('sort_order', 'asc')
                ->orderBy('title', 'asc')
                ->get();
            
            // Формируем массив данных для ответа
            $data = [];
            $activeCount = 0;
            $inactiveCount = 0;
            
            foreach ($priceTypes as $priceType) {
                $item = [
                    'id' => $priceType->id,
                    'title' => $priceType->title,
                    'type' => $priceType->type,
                    'currency' => $priceType->currency,
                    'is_active' => (bool) $priceType->is_active,
                    'sort_order' => (int) $priceType->sort_order,
                    'created_at' => $priceType->created_at ? $priceType->created_at->toISOString() : null,
                    'updated_at' => $priceType->updated_at ? $priceType->updated_at->toISOString() : null
                ];
                
                $data[] = $item;
                
                // Подсчитываем активные/неактивные
                if ($priceType->is_active) {
                    $activeCount++;
                } else {
                    $inactiveCount++;
                }
            }
            
            Log::info('API Catalog: список типов цен успешно получен', [
                'total' => count($data),
                'active' => $activeCount,
                'inactive' => $inactiveCount
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'total' => count($data),
                    'active' => $activeCount,
                    'inactive' => $inactiveCount,
                    'currencies' => $this->getUniqueCurrencies($priceTypes),
                    'timestamp' => now()->toISOString()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка получения списка типов цен', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении списка типов цен',
                'error' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }
    
    /**
     * Получение только активных типов цен
     * 
     * @return JsonResponse
     */
    public function getActivePrices(): JsonResponse
    {
        try {
            Log::info('API Catalog: начало получения активных типов цен');
            
            $priceTypes = CatalogTypePrice::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->orderBy('title', 'asc')
                ->get();
            
            $data = $priceTypes->map(function($priceType) {
                return [
                    'id' => $priceType->id,
                    'title' => $priceType->title,
                    'type' => $priceType->type,
                    'currency' => $priceType->currency,
                    'sort_order' => (int) $priceType->sort_order,
                    'currency_symbol' => $this->getCurrencySymbol($priceType->currency)
                ];
            });
            
            Log::info('API Catalog: активные типы цен успешно получены', [
                'total' => $data->count()
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'total' => $data->count(),
                    'timestamp' => now()->toISOString()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка получения активных типов цен', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении активных типов цен'
            ], 500);
        }
    }
    
    /**
     * Получение типа цены по его техническому идентификатору (type)
     * 
     * @param string $type Технический идентификатор типа цены
     * @return JsonResponse
     */
    public function getPriceByType(string $type): JsonResponse
    {
        try {
            Log::info('API Catalog: получение типа цены по техническому идентификатору', [
                'type' => $type
            ]);
            
            $priceType = CatalogTypePrice::where('type', $type)->first();
            
            if (!$priceType) {
                Log::warning('API Catalog: тип цены не найден', ['type' => $type]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Тип цены не найден'
                ], 404);
            }
            
            $data = [
                'id' => $priceType->id,
                'title' => $priceType->title,
                'type' => $priceType->type,
                'currency' => $priceType->currency,
                'is_active' => (bool) $priceType->is_active,
                'sort_order' => (int) $priceType->sort_order,
                'currency_symbol' => $this->getCurrencySymbol($priceType->currency),
                'created_at' => $priceType->created_at ? $priceType->created_at->toISOString() : null,
                'updated_at' => $priceType->updated_at ? $priceType->updated_at->toISOString() : null
            ];
            
            Log::info('API Catalog: тип цены успешно получен', [
                'type' => $type,
                'price_type_id' => $priceType->id
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка получения типа цены', [
                'error' => $e->getMessage(),
                'type' => $type
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении типа цены'
            ], 500);
        }
    }
    
    /**
     * Получение основного типа цены (самый высокий приоритет по sort_order среди активных)
     * 
     * @return JsonResponse
     */
    public function getMainPriceType(): JsonResponse
    {
        try {
            Log::info('API Catalog: получение основного типа цены');
            
            $mainPriceType = CatalogTypePrice::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->first();
            
            if (!$mainPriceType) {
                Log::warning('API Catalog: активные типы цен не найдены');
                
                return response()->json([
                    'success' => false,
                    'message' => 'Активные типы цен не найдены'
                ], 404);
            }
            
            $data = [
                'id' => $mainPriceType->id,
                'title' => $mainPriceType->title,
                'type' => $mainPriceType->type,
                'currency' => $mainPriceType->currency,
                'sort_order' => (int) $mainPriceType->sort_order,
                'currency_symbol' => $this->getCurrencySymbol($mainPriceType->currency),
                'is_main' => true
            ];
            
            Log::info('API Catalog: основной тип цены успешно получен', [
                'type' => $mainPriceType->type,
                'title' => $mainPriceType->title
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка получения основного типа цены', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении основного типа цены'
            ], 500);
        }
    }
    
    /**
     * Получение типов цен сгруппированных по валюте
     * 
     * @return JsonResponse
     */
    public function getPricesGroupedByCurrency(): JsonResponse
    {
        try {
            Log::info('API Catalog: получение типов цен сгруппированных по валюте');
            
            $priceTypes = CatalogTypePrice::where('is_active', true)
                ->orderBy('currency', 'asc')
                ->orderBy('sort_order', 'asc')
                ->get();
            
            $groupedData = [];
            
            foreach ($priceTypes as $priceType) {
                $currency = $priceType->currency;
                
                if (!isset($groupedData[$currency])) {
                    $groupedData[$currency] = [
                        'currency' => $currency,
                        'currency_symbol' => $this->getCurrencySymbol($currency),
                        'price_types' => []
                    ];
                }
                
                $groupedData[$currency]['price_types'][] = [
                    'id' => $priceType->id,
                    'title' => $priceType->title,
                    'type' => $priceType->type,
                    'sort_order' => (int) $priceType->sort_order
                ];
            }
            
            // Преобразуем ассоциативный массив в индексный
            $result = array_values($groupedData);
            
            Log::info('API Catalog: типы цен сгруппированные по валюте успешно получены', [
                'currency_count' => count($result)
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $result,
                'meta' => [
                    'currency_count' => count($result),
                    'total_price_types' => $priceTypes->count(),
                    'timestamp' => now()->toISOString()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка получения типов цен сгруппированных по валюте', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении типов цен сгруппированных по валюте'
            ], 500);
        }
    }
    
    /**
     * Получение уникальных валют из списка типов цен
     * 
     * @param \Illuminate\Database\Eloquent\Collection $priceTypes
     * @return array
     */
    private function getUniqueCurrencies($priceTypes): array
    {
        $currencies = $priceTypes->pluck('currency')->unique()->values()->toArray();
        
        $result = [];
        foreach ($currencies as $currency) {
            $result[] = [
                'code' => $currency,
                'symbol' => $this->getCurrencySymbol($currency),
                'count' => $priceTypes->where('currency', $currency)->count()
            ];
        }
        
        return $result;
    }
    
    /**
     * Получение символа валюты по коду
     * 
     * @param string $currency Код валюты
     * @return string
     */
    private function getCurrencySymbol(string $currency): string
    {
        $symbols = [
            'RUB' => '₽',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'CNY' => '¥',
            'JPY' => '¥',
            'UAH' => '₴',
            'KZT' => '₸',
            'BYN' => 'Br',
        ];
        
        return $symbols[$currency] ?? $currency;
    }
    
    /**
     * Получение статистики по типам цен
     * 
     * @return JsonResponse
     */
    public function getPricesStats(): JsonResponse
    {
        try {
            Log::info('API Catalog: получение статистики по типам цен');
            
            $stats = [
                'total' => CatalogTypePrice::count(),
                'active' => CatalogTypePrice::where('is_active', true)->count(),
                'inactive' => CatalogTypePrice::where('is_active', false)->count(),
                'by_currency' => DB::table('catalog_type_price')
                    ->select('currency', DB::raw('count(*) as count'))
                    ->groupBy('currency')
                    ->orderBy('count', 'desc')
                    ->get()
                    ->map(function($item) {
                        return [
                            'currency' => $item->currency,
                            'count' => $item->count,
                            'symbol' => $this->getCurrencySymbol($item->currency)
                        ];
                    })
                    ->toArray(),
                'sort_order_range' => [
                    'min' => CatalogTypePrice::min('sort_order'),
                    'max' => CatalogTypePrice::max('sort_order'),
                    'avg' => round(CatalogTypePrice::avg('sort_order'), 2)
                ],
                'last_updated' => CatalogTypePrice::max('updated_at')
            ];
            
            Log::info('API Catalog: статистика по типам цен успешно получена', $stats);
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'meta' => [
                    'timestamp' => now()->toISOString()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка получения статистики по типам цен', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении статистики по типам цен'
            ], 500);
        }
    }
}