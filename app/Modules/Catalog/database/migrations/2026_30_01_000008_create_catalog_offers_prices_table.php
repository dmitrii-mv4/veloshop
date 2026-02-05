<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Core\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\CatalogProductOffer;
use App\Modules\Catalog\Models\CatalogOfferPrice;
use App\Modules\Catalog\Models\CatalogTypePrice;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{    
    /**
     * Получение древовидной структуры каталога
     * Возвращает продукты с вложенными офферами в виде дерева
     * 
     * Структура ответа:
     * [
     *     {
     *         "id": 1,
     *         "product_id": "prod_001",
     *         "name": "Название товара",
     *         // ... другие поля продукта
     *         "offers": [
     *             {
     *                 "id": 1,
     *                 "offer_id": "offer_001",
     *                 "product_id": 1,
     *                 "name": "Название офера",
     *                 // ... другие поля офера
     *                 "prices": [
     *                     {
     *                         "type_price_id": 1,
     *                         "type": "price",
     *                         "title": "Основная цена",
     *                         "price": 1000.00,
     *                         "currency": "RUB",
     *                         "formatted": "1 000.00 ₽"
     *                     },
     *                     // ... другие цены
     *                 ],
     *                 "warehouses": [
     *                     {
     *                         "warehouse_id": 1,
     *                         "title": "Основной склад",
     *                         "count": 50
     *                     },
     *                     // ... другие склады
     *                 ]
     *             }
     *         ]
     *     }
     * ]
     *
     * @return JsonResponse
     */
    public function getTree(): JsonResponse
    {
        try {
            Log::info('Начало формирования древовидной структуры каталога');
            
            // Загружаем все продукты с активными оферами
            $products = Product::with([
                'offers' => function($query) {
                    // Загружаем только активные оферы
                    $query->where('is_active', true)
                          ->orderBy('sort_order', 'asc');
                },
                'offers.prices.typePrice',
                'offers.warehouseOffers.warehouse'
            ])->get();
            
            Log::info('Загружено продуктов для формирования дерева', [
                'count' => $products->count()
            ]);
            
            $tree = [];
            
            foreach ($products as $product) {
                $productData = [
                    'id' => $product->id,
                    'product_id' => $product->product_id,
                    'group_name' => $product->group_name,
                    'brand' => $product->brand,
                    'model' => $product->model,
                    'seazon' => $product->seazon,
                    'name' => $product->name,
                    'meta_title' => $product->meta_title,
                    'meta_description' => $product->meta_description,
                    'meta_keywords' => $product->meta_keywords,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                    'offers' => []
                ];
                
                // Формируем данные оферов для текущего продукта
                foreach ($product->offers as $offer) {
                    $offerData = [
                        'id' => $offer->id,
                        'offer_id' => $offer->offer_id,
                        'product_id' => $offer->product_id,
                        'size' => $offer->size,
                        'color' => $offer->color,
                        'main_color' => $offer->main_color,
                        'articul_supplier' => $offer->articul_supplier,
                        'name' => $offer->name,
                        'vcode' => $offer->vcode,
                        'meta_title' => $offer->meta_title,
                        'meta_description' => $offer->meta_description,
                        'meta_keywords' => $offer->meta_keywords,
                        'is_active' => $offer->is_active,
                        'sort_order' => $offer->sort_order,
                        'created_at' => $offer->created_at,
                        'updated_at' => $offer->updated_at,
                        'prices' => $this->getOfferPricesArray($offer),
                        'warehouses' => $this->getOfferWarehousesArray($offer)
                    ];
                    
                    $productData['offers'][] = $offerData;
                }
                
                $tree[] = $productData;
                
                Log::debug('Обработан продукт', [
                    'product_id' => $product->product_id,
                    'offers_count' => count($productData['offers'])
                ]);
            }
            
            Log::info('Древовидная структура каталога успешно сформирована', [
                'total_products' => count($tree),
                'total_offers' => array_sum(array_map(function($product) {
                    return count($product['offers']);
                }, $tree))
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $tree,
                'meta' => [
                    'total_products' => count($tree),
                    'total_offers' => array_sum(array_map(function($product) {
                        return count($product['offers']);
                    }, $tree)),
                    'timestamp' => now()->toDateTimeString()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при формировании древовидной структуры каталога', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при формировании структуры каталога',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Получение массива цен для офера
     * Использует готовый метод из модели CatalogProductOffer
     *
     * @param CatalogProductOffer $offer
     * @return array
     */
    private function getOfferPricesArray(CatalogProductOffer $offer): array
    {
        try {
            return $offer->getPricesArray();
        } catch (\Exception $e) {
            Log::warning('Ошибка при получении цен офера', [
                'offer_id' => $offer->offer_id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Получение массива складов и остатков для офера
     * Использует готовый метод из модели CatalogProductOffer
     *
     * @param CatalogProductOffer $offer
     * @return array
     */
    private function getOfferWarehousesArray(CatalogProductOffer $offer): array
    {
        try {
            return $offer->getWarehouseStocksArray();
        } catch (\Exception $e) {
            Log::warning('Ошибка при получении складов офера', [
                'offer_id' => $offer->offer_id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Оптимизированная версия получения древовидной структуры с пагинацией
     * Для больших каталогов
     *
     * @param int $page
     * @param int $perPage
     * @return JsonResponse
     */
    public function getTreePaginated(int $page = 1, int $perPage = 50): JsonResponse
    {
        try {
            Log::info('Начало формирования древовидной структуры каталога с пагинацией', [
                'page' => $page,
                'per_page' => $perPage
            ]);
            
            $query = Product::with([
                'offers' => function($query) {
                    $query->where('is_active', true)
                          ->orderBy('sort_order', 'asc');
                },
                'offers.prices.typePrice',
                'offers.warehouseOffers.warehouse'
            ]);
            
            $totalProducts = $query->count();
            $products = $query->skip(($page - 1) * $perPage)
                             ->take($perPage)
                             ->get();
            
            $tree = [];
            $totalOffers = 0;
            
            foreach ($products as $product) {
                $productData = [
                    'id' => $product->id,
                    'product_id' => $product->product_id,
                    'group_name' => $product->group_name,
                    'brand' => $product->brand,
                    'model' => $product->model,
                    'seazon' => $product->seazon,
                    'name' => $product->name,
                    'meta_title' => $product->meta_title,
                    'meta_description' => $product->meta_description,
                    'meta_keywords' => $product->meta_keywords,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                    'offers' => []
                ];
                
                foreach ($product->offers as $offer) {
                    $offerData = [
                        'id' => $offer->id,
                        'offer_id' => $offer->offer_id,
                        'product_id' => $offer->product_id,
                        'size' => $offer->size,
                        'color' => $offer->color,
                        'main_color' => $offer->main_color,
                        'articul_supplier' => $offer->articul_supplier,
                        'name' => $offer->name,
                        'vcode' => $offer->vcode,
                        'meta_title' => $offer->meta_title,
                        'meta_description' => $offer->meta_description,
                        'meta_keywords' => $offer->meta_keywords,
                        'is_active' => $offer->is_active,
                        'sort_order' => $offer->sort_order,
                        'created_at' => $offer->created_at,
                        'updated_at' => $offer->updated_at,
                        'prices' => $this->getOfferPricesArray($offer),
                        'warehouses' => $this->getOfferWarehousesArray($offer)
                    ];
                    
                    $productData['offers'][] = $offerData;
                    $totalOffers++;
                }
                
                $tree[] = $productData;
            }
            
            $totalPages = ceil($totalProducts / $perPage);
            
            Log::info('Древовидная структура каталога с пагинацией успешно сформирована', [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_products' => $totalProducts,
                'total_pages' => $totalPages,
                'current_page_products' => count($tree),
                'current_page_offers' => $totalOffers
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $tree,
                'meta' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_products' => $totalProducts,
                    'total_pages' => $totalPages,
                    'has_more' => $page < $totalPages,
                    'timestamp' => now()->toDateTimeString()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при формировании древовидной структуры каталога с пагинацией', [
                'error' => $e->getMessage(),
                'page' => $page,
                'per_page' => $perPage,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при формировании структуры каталога',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}