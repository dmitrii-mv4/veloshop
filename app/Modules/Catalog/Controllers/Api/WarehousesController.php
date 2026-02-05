<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Core\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Modules\Catalog\Models\CatalogWarehouse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WarehousesController extends Controller
{    
    /**
     * Получение всех складов для API
     * Возвращает список всех складов с их основной информацией
     *
     * @return JsonResponse
     */
    public function getWarehouses(): JsonResponse
    {
        try {
            Log::info('API Catalog: начало получения списка складов');
            
            // Получаем все склады с сортировкой по sort_order и названию
            $warehouses = CatalogWarehouse::orderBy('sort_order', 'asc')
                ->orderBy('title', 'asc')
                ->get();
            
            // Формируем массив данных для ответа со статистикой
            $data = [];
            $activeCount = 0;
            $inactiveCount = 0;
            $totalOffers = 0;
            $totalQuantity = 0;
            
            foreach ($warehouses as $warehouse) {
                // Получаем статистику для каждого склада
                $stats = $this->getWarehouseStats($warehouse->id);
                
                $item = [
                    'id' => $warehouse->id,
                    'warehouse_id' => $warehouse->warehouse_id,
                    'title' => $warehouse->title,
                    'description' => $warehouse->description,
                    'contacts' => $warehouse->contacts,
                    'is_active' => (bool) $warehouse->is_active,
                    'sort_order' => (int) $warehouse->sort_order,
                    'created_by' => $warehouse->created_by,
                    'updated_by' => $warehouse->updated_by,
                    'created_at' => $warehouse->created_at ? $warehouse->created_at->toISOString() : null,
                    'updated_at' => $warehouse->updated_at ? $warehouse->updated_at->toISOString() : null,
                    'stats' => $stats
                ];
                
                $data[] = $item;
                
                // Обновляем общую статистику
                if ($warehouse->is_active) {
                    $activeCount++;
                } else {
                    $inactiveCount++;
                }
                
                $totalOffers += $stats['unique_offers_count'];
                $totalQuantity += $stats['total_quantity'];
            }
            
            Log::info('API Catalog: список складов успешно получен', [
                'total' => count($data),
                'active' => $activeCount,
                'inactive' => $inactiveCount,
                'total_offers' => $totalOffers,
                'total_quantity' => $totalQuantity
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'total' => count($data),
                    'active' => $activeCount,
                    'inactive' => $inactiveCount,
                    'total_offers' => $totalOffers,
                    'total_quantity' => $totalQuantity,
                    'average_quantity_per_warehouse' => count($data) > 0 ? round($totalQuantity / count($data), 2) : 0,
                    'average_offers_per_warehouse' => count($data) > 0 ? round($totalOffers / count($data), 2) : 0,
                    'timestamp' => now()->toISOString()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка получения списка складов', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении списка складов',
                'error' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }
    
    /**
     * Получение только активных складов
     * 
     * @return JsonResponse
     */
    public function getActiveWarehouses(): JsonResponse
    {
        try {
            Log::info('API Catalog: начало получения активных складов');
            
            $warehouses = CatalogWarehouse::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->orderBy('title', 'asc')
                ->get();
            
            $data = $warehouses->map(function($warehouse) {
                return [
                    'id' => $warehouse->id,
                    'warehouse_id' => $warehouse->warehouse_id,
                    'title' => $warehouse->title,
                    'description' => $warehouse->description,
                    'contacts' => $warehouse->contacts,
                    'sort_order' => (int) $warehouse->sort_order,
                    'stats' => $this->getWarehouseStats($warehouse->id)
                ];
            });
            
            $totalQuantity = $data->sum(function($item) {
                return $item['stats']['total_quantity'];
            });
            
            $totalOffers = $data->sum(function($item) {
                return $item['stats']['unique_offers_count'];
            });
            
            Log::info('API Catalog: активные склады успешно получены', [
                'total' => $data->count(),
                'total_quantity' => $totalQuantity,
                'total_offers' => $totalOffers
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'total' => $data->count(),
                    'total_quantity' => $totalQuantity,
                    'total_offers' => $totalOffers,
                    'timestamp' => now()->toISOString()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка получения активных складов', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении активных складов'
            ], 500);
        }
    }
    
    /**
     * Получение склада по его ID
     * 
     * @param int $id ID склада
     * @return JsonResponse
     */
    public function getWarehouseById(int $id): JsonResponse
    {
        try {
            Log::info('API Catalog: получение склада по ID', [
                'warehouse_id' => $id
            ]);
            
            $warehouse = CatalogWarehouse::find($id);
            
            if (!$warehouse) {
                Log::warning('API Catalog: склад не найден', ['warehouse_id' => $id]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Склад не найден'
                ], 404);
            }
            
            // Получаем детальную статистику
            $detailedStats = $this->getDetailedWarehouseStats($warehouse->id);
            
            $data = [
                'id' => $warehouse->id,
                'warehouse_id' => $warehouse->warehouse_id,
                'title' => $warehouse->title,
                'description' => $warehouse->description,
                'contacts' => $warehouse->contacts,
                'is_active' => (bool) $warehouse->is_active,
                'sort_order' => (int) $warehouse->sort_order,
                'created_by' => $warehouse->created_by,
                'updated_by' => $warehouse->updated_by,
                'created_at' => $warehouse->created_at ? $warehouse->created_at->toISOString() : null,
                'updated_at' => $warehouse->updated_at ? $warehouse->updated_at->toISOString() : null,
                'stats' => $detailedStats['summary'],
                'top_offers' => $detailedStats['top_offers'],
                'recent_activity' => $detailedStats['recent_activity']
            ];
            
            Log::info('API Catalog: склад успешно получен', [
                'warehouse_id' => $warehouse->id,
                'title' => $warehouse->title,
                'total_quantity' => $detailedStats['summary']['total_quantity']
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка получения склада по ID', [
                'error' => $e->getMessage(),
                'warehouse_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении склада'
            ], 500);
        }
    }
    
    /**
     * Получение статистики по складам
     * 
     * @return JsonResponse
     */
    public function getWarehousesStats(): JsonResponse
    {
        try {
            Log::info('API Catalog: получение статистики по складам');
            
            // Общая статистика по всем складам
            $totalWarehouses = CatalogWarehouse::count();
            $activeWarehouses = CatalogWarehouse::where('is_active', true)->count();
            $inactiveWarehouses = CatalogWarehouse::where('is_active', false)->count();
            
            // Статистика по остаткам
            $stockStats = DB::table('catalog_offers_warehouses as cow')
                ->join('catalog_warehouses as cw', 'cow.warehouse_id', '=', 'cw.id')
                ->select(
                    'cw.id',
                    'cw.title',
                    DB::raw('COUNT(DISTINCT cow.offer_id) as unique_offers'),
                    DB::raw('SUM(cow.count) as total_quantity'),
                    DB::raw('AVG(cow.count) as avg_quantity_per_offer')
                )
                ->groupBy('cw.id', 'cw.title')
                ->get();
            
            // Сводная статистика
            $overallStats = [
                'total_warehouses' => $totalWarehouses,
                'active_warehouses' => $activeWarehouses,
                'inactive_warehouses' => $inactiveWarehouses,
                'total_unique_offers' => DB::table('catalog_offers_warehouses')->distinct('offer_id')->count('offer_id'),
                'total_quantity' => DB::table('catalog_offers_warehouses')->sum('count'),
                'warehouses_with_stock' => DB::table('catalog_offers_warehouses')
                    ->distinct('warehouse_id')
                    ->count('warehouse_id'),
                'empty_warehouses' => $totalWarehouses - DB::table('catalog_offers_warehouses')
                    ->distinct('warehouse_id')
                    ->count('warehouse_id')
            ];
            
            // Статистика по активности складов
            $activityStats = DB::table('catalog_warehouses')
                ->select(
                    DB::raw("DATE(created_at) as date"),
                    DB::raw("COUNT(*) as created_count")
                )
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->get();
            
            $stats = [
                'overall' => $overallStats,
                'warehouses' => $stockStats,
                'recent_activity' => $activityStats,
                'top_warehouses_by_quantity' => $stockStats->sortByDesc('total_quantity')->take(5)->values(),
                'top_warehouses_by_offers' => $stockStats->sortByDesc('unique_offers')->take(5)->values()
            ];
            
            Log::info('API Catalog: статистика по складам успешно получена', $overallStats);
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'meta' => [
                    'timestamp' => now()->toISOString()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка получения статистики по складам', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении статистики по складам'
            ], 500);
        }
    }
    
    /**
     * Получение складов сгруппированных по активности
     * 
     * @return JsonResponse
     */
    public function getWarehousesGroupedByActivity(): JsonResponse
    {
        try {
            Log::info('API Catalog: получение складов сгруппированных по активности');
            
            $activeWarehouses = CatalogWarehouse::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->orderBy('title', 'asc')
                ->get()
                ->map(function($warehouse) {
                    return [
                        'id' => $warehouse->id,
                        'warehouse_id' => $warehouse->warehouse_id,
                        'title' => $warehouse->title,
                        'sort_order' => $warehouse->sort_order,
                        'stats' => $this->getWarehouseStats($warehouse->id)
                    ];
                });
            
            $inactiveWarehouses = CatalogWarehouse::where('is_active', false)
                ->orderBy('sort_order', 'asc')
                ->orderBy('title', 'asc')
                ->get()
                ->map(function($warehouse) {
                    return [
                        'id' => $warehouse->id,
                        'warehouse_id' => $warehouse->warehouse_id,
                        'title' => $warehouse->title,
                        'sort_order' => $warehouse->sort_order,
                        'stats' => $this->getWarehouseStats($warehouse->id)
                    ];
                });
            
            $groupedData = [
                'active' => [
                    'count' => $activeWarehouses->count(),
                    'total_quantity' => $activeWarehouses->sum(function($item) {
                        return $item['stats']['total_quantity'];
                    }),
                    'warehouses' => $activeWarehouses
                ],
                'inactive' => [
                    'count' => $inactiveWarehouses->count(),
                    'total_quantity' => $inactiveWarehouses->sum(function($item) {
                        return $item['stats']['total_quantity'];
                    }),
                    'warehouses' => $inactiveWarehouses
                ]
            ];
            
            Log::info('API Catalog: склады сгруппированные по активности успешно получены', [
                'active_count' => $groupedData['active']['count'],
                'inactive_count' => $groupedData['inactive']['count']
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $groupedData,
                'meta' => [
                    'total_active' => $groupedData['active']['count'],
                    'total_inactive' => $groupedData['inactive']['count'],
                    'timestamp' => now()->toISOString()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка получения складов сгруппированных по активности', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении складов сгруппированных по активности'
            ], 500);
        }
    }
    
    /**
     * Получение статистики по конкретному складу
     * 
     * @param int $warehouseId
     * @return array
     */
    private function getWarehouseStats(int $warehouseId): array
    {
        try {
            $stats = DB::table('catalog_offers_warehouses as cow')
                ->where('cow.warehouse_id', $warehouseId)
                ->select(
                    DB::raw('COUNT(DISTINCT cow.offer_id) as unique_offers_count'),
                    DB::raw('SUM(cow.count) as total_quantity'),
                    DB::raw('MAX(cow.updated_at) as last_updated')
                )
                ->first();
            
            return [
                'unique_offers_count' => $stats ? (int) $stats->unique_offers_count : 0,
                'total_quantity' => $stats ? (int) $stats->total_quantity : 0,
                'last_updated' => $stats ? $stats->last_updated : null
            ];
            
        } catch (\Exception $e) {
            Log::warning('API Catalog: ошибка получения статистики склада', [
                'error' => $e->getMessage(),
                'warehouse_id' => $warehouseId
            ]);
            
            return [
                'unique_offers_count' => 0,
                'total_quantity' => 0,
                'last_updated' => null
            ];
        }
    }
    
    /**
     * Получение детальной статистики по складу
     * 
     * @param int $warehouseId
     * @return array
     */
    private function getDetailedWarehouseStats(int $warehouseId): array
    {
        try {
            // Основная статистика
            $summary = $this->getWarehouseStats($warehouseId);
            
            // Топ оферов по количеству
            $topOffers = DB::table('catalog_offers_warehouses as cow')
                ->join('catalog_product_offers as cpo', 'cow.offer_id', '=', 'cpo.id')
                ->where('cow.warehouse_id', $warehouseId)
                ->where('cow.count', '>', 0)
                ->select(
                    'cpo.id',
                    'cpo.offer_id',
                    'cpo.name',
                    'cpo.size',
                    'cpo.color',
                    'cow.count',
                    'cow.updated_at'
                )
                ->orderBy('cow.count', 'desc')
                ->limit(10)
                ->get()
                ->map(function($item) {
                    return [
                        'offer_id' => $item->offer_id,
                        'name' => $item->name,
                        'size' => $item->size,
                        'color' => $item->color,
                        'count' => (int) $item->count,
                        'last_updated' => $item->updated_at
                    ];
                });
            
            // Последняя активность
            $recentActivity = DB::table('catalog_offers_warehouses as cow')
                ->where('cow.warehouse_id', $warehouseId)
                ->join('catalog_product_offers as cpo', 'cow.offer_id', '=', 'cpo.id')
                ->select(
                    'cpo.offer_id',
                    'cpo.name',
                    'cow.count',
                    'cow.updated_at',
                    DB::raw("CASE 
                        WHEN cow.updated_at > DATE_SUB(NOW(), INTERVAL 1 DAY) THEN 'today'
                        WHEN cow.updated_at > DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 'this_week'
                        ELSE 'earlier'
                    END as activity_period")
                )
                ->orderBy('cow.updated_at', 'desc')
                ->limit(20)
                ->get()
                ->groupBy('activity_period');
            
            return [
                'summary' => $summary,
                'top_offers' => $topOffers,
                'recent_activity' => $recentActivity
            ];
            
        } catch (\Exception $e) {
            Log::warning('API Catalog: ошибка получения детальной статистики склада', [
                'error' => $e->getMessage(),
                'warehouse_id' => $warehouseId
            ]);
            
            return [
                'summary' => $this->getWarehouseStats($warehouseId),
                'top_offers' => [],
                'recent_activity' => []
            ];
        }
    }
    
    /**
     * Получение складов с фильтрацией по наличию товаров
     * 
     * @param string $filter Возможные значения: with_stock, without_stock, all
     * @return JsonResponse
     */
    public function getWarehousesByStock(string $filter = 'all'): JsonResponse
    {
        try {
            Log::info('API Catalog: получение складов с фильтрацией по наличию товаров', [
                'filter' => $filter
            ]);
            
            $warehouses = CatalogWarehouse::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->orderBy('title', 'asc')
                ->get();
            
            $filteredData = [];
            
            foreach ($warehouses as $warehouse) {
                $stats = $this->getWarehouseStats($warehouse->id);
                
                // Применяем фильтр
                $shouldInclude = false;
                switch ($filter) {
                    case 'with_stock':
                        $shouldInclude = $stats['total_quantity'] > 0;
                        break;
                    case 'without_stock':
                        $shouldInclude = $stats['total_quantity'] == 0;
                        break;
                    case 'all':
                    default:
                        $shouldInclude = true;
                        break;
                }
                
                if ($shouldInclude) {
                    $filteredData[] = [
                        'id' => $warehouse->id,
                        'warehouse_id' => $warehouse->warehouse_id,
                        'title' => $warehouse->title,
                        'stats' => $stats
                    ];
                }
            }
            
            Log::info('API Catalog: склады с фильтрацией по наличию товаров успешно получены', [
                'filter' => $filter,
                'count' => count($filteredData)
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $filteredData,
                'meta' => [
                    'filter' => $filter,
                    'count' => count($filteredData),
                    'timestamp' => now()->toISOString()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка получения складов с фильтрацией по наличию товаров', [
                'error' => $e->getMessage(),
                'filter' => $filter
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении складов с фильтрацией'
            ], 500);
        }
    }
}