<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Core\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\CatalogProductOffer;
use App\Modules\Catalog\Models\CatalogOfferPrice;
use App\Modules\Catalog\Models\CatalogTypePrice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TreeController extends Controller
{
    /**
     * Получение древовидной структуры каталога
     * Возвращает продукты с вложенными офферами в виде дерева
     *
     * @return JsonResponse
     */
    public function getTree(): JsonResponse
    {
        try {
            Log::info('API Catalog: начало получения древовидной структуры каталога');

            // Загружаем все продукты с активными оферами и связанными данными
            $products = Product::with([
                'offers' => function($query) {
                    $query->where('is_active', true)
                          ->orderBy('sort_order', 'asc');
                },
                'offers.prices.typePrice',
                'offers.warehouseOffers.warehouse'
            ])->get();

            Log::info('API Catalog: продукты загружены для формирования дерева', [
                'total_products' => $products->count()
            ]);

            $tree = [];

            foreach ($products as $product) {
                // Формируем данные продукта
                $productData = [
                    'id' => $product->id,
                    'product_id' => $product->product_id,
                    'category_id' => $product->category_id,
                    'brand' => $product->brand,
                    'model' => $product->model,
                    'seazon' => $product->seazon,
                    'name' => $product->name,
                    'meta_title' => $product->meta_title,
                    'meta_description' => $product->meta_description,
                    'meta_keywords' => $product->meta_keywords,
                    'created_at' => $product->created_at ? $product->created_at->toISOString() : null,
                    'updated_at' => $product->updated_at ? $product->updated_at->toISOString() : null,
                    'offers' => []
                ];

                // Формируем оферы для текущего продукта
                foreach ($product->offers as $offer) {
                    // Получаем цены офера
                    $prices = [];
                    foreach ($offer->prices as $price) {
                        if ($price->typePrice) {
                            $prices[] = [
                                'type_price_id' => $price->type_price_id,
                                'type' => $price->typePrice->type,
                                'title' => $price->typePrice->title,
                                'price' => (float) $price->price,
                                'currency' => $price->typePrice->currency,
                                'formatted' => $price->getPriceWithCurrency()
                            ];
                        }
                    }

                    // Получаем склады и остатки офера
                    $warehouses = [];
                    foreach ($offer->warehouseOffers as $warehouseOffer) {
                        if ($warehouseOffer->warehouse) {
                            $warehouses[] = [
                                'warehouse_id' => $warehouseOffer->warehouse_id,
                                'title' => $warehouseOffer->warehouse->title,
                                'count' => (int) $warehouseOffer->count
                            ];
                        }
                    }

                    // Формируем данные офера
                    $offerData = [
                        'id' => $offer->id,
                        'offer_id' => $offer->offer_id,
                        'product_id' => $offer->product_id,
                        'name' => $offer->name,
                        'articul_supplier' => $offer->articul_supplier,
                        'vcode' => $offer->vcode,
                        'meta_title' => $offer->meta_title,
                        'meta_description' => $offer->meta_description,
                        'meta_keywords' => $offer->meta_keywords,
                        'is_active' => (bool) $offer->is_active,
                        'sort_order' => (int) $offer->sort_order,
                        'created_at' => $offer->created_at ? $offer->created_at->toISOString() : null,
                        'updated_at' => $offer->updated_at ? $offer->updated_at->toISOString() : null,
                        'prices' => $prices,
                        'warehouses' => $warehouses
                    ];

                    $productData['offers'][] = $offerData;
                }

                $tree[] = $productData;

                Log::debug('API Catalog: обработан продукт', [
                    'product_id' => $product->product_id,
                    'offers_count' => count($productData['offers'])
                ]);
            }

            // Подсчитываем общую статистику
            $totalOffers = 0;
            $totalPrices = 0;
            $totalWarehouses = 0;

            foreach ($tree as $product) {
                $totalOffers += count($product['offers']);
                foreach ($product['offers'] as $offer) {
                    $totalPrices += count($offer['prices']);
                    $totalWarehouses += count($offer['warehouses']);
                }
            }

            Log::info('API Catalog: древовидная структура каталога успешно сформирована', [
                'total_products' => count($tree),
                'total_offers' => $totalOffers,
                'total_prices' => $totalPrices,
                'total_warehouses' => $totalWarehouses
            ]);

            return response()->json([
                'success' => true,
                'data' => $tree,
                'meta' => [
                    'total_products' => count($tree),
                    'total_offers' => $totalOffers,
                    'total_prices' => $totalPrices,
                    'total_warehouses' => $totalWarehouses,
                    'timestamp' => now()->toISOString(),
                    'generated_in' => round(microtime(true) - LARAVEL_START, 3) . 's'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка получения древовидной структуры каталога', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при построении древовидной структуры каталога',
                'error' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }

    /**
     * Упрощенная версия получения дерева каталога без сложных зависимостей
     * Используется как fallback если основной метод не работает
     *
     * @return JsonResponse
     */
    public function getTreeSimple(): JsonResponse
    {
        try {
            Log::info('API Catalog: начало получения упрощенной древовидной структуры каталога');

            // Используем простой запрос без сложных связей
            $products = Product::with([
                'offers' => function($query) {
                    $query->where('is_active', true)
                          ->orderBy('sort_order', 'asc');
                }
            ])->get();

            $tree = [];

            foreach ($products as $product) {
                $productData = [
                    'id' => $product->id,
                    'product_id' => $product->product_id,
                    'name' => $product->name,
                    'brand' => $product->brand,
                    'model' => $product->model,
                    'offers' => []
                ];

                foreach ($product->offers as $offer) {
                    // Получаем цены напрямую через запрос
                    $prices = CatalogOfferPrice::where('offer_id', $offer->id)
                        ->with('typePrice')
                        ->get()
                        ->map(function($price) {
                            if ($price->typePrice) {
                                return [
                                    'type_price_id' => $price->type_price_id,
                                    'type' => $price->typePrice->type,
                                    'title' => $price->typePrice->title,
                                    'price' => (float) $price->price,
                                    'currency' => $price->typePrice->currency
                                ];
                            }
                            return null;
                        })
                        ->filter()
                        ->values()
                        ->toArray();

                    // Получаем склады напрямую через запрос
                    $warehouses = DB::table('catalog_offers_warehouses as cow')
                        ->join('catalog_warehouses as cw', 'cow.warehouse_id', '=', 'cw.id')
                        ->where('cow.offer_id', $offer->id)
                        ->select('cw.id as warehouse_id', 'cw.title', 'cow.count')
                        ->get()
                        ->map(function($item) {
                            return [
                                'warehouse_id' => $item->warehouse_id,
                                'title' => $item->title,
                                'count' => (int) $item->count
                            ];
                        })
                        ->toArray();

                    $offerData = [
                        'id' => $offer->id,
                        'offer_id' => $offer->offer_id,
                        'name' => $offer->name,
                        'prices' => $prices,
                        'warehouses' => $warehouses
                    ];

                    $productData['offers'][] = $offerData;
                }

                $tree[] = $productData;
            }

            Log::info('API Catalog: упрощенная древовидная структура каталога успешно сформирована', [
                'total_products' => count($tree)
            ]);

            return response()->json([
                'success' => true,
                'data' => $tree,
                'meta' => [
                    'total_products' => count($tree),
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка получения упрощенной древовидной структуры каталога', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при построении структуры каталога',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Получение дерева каталога с пагинацией
     *
     * @param int $page Номер страницы
     * @param int $perPage Количество элементов на странице
     * @return JsonResponse
     */
    public function getTreePaginated($page = 1, $perPage = 50): JsonResponse
    {
        try {
            Log::info('API Catalog: начало получения древовидной структуры каталога с пагинацией', [
                'page' => $page,
                'per_page' => $perPage
            ]);

            $page = max(1, (int) $page);
            $perPage = min(100, max(1, (int) $perPage));

            $query = Product::with([
                'offers' => function($query) {
                    $query->where('is_active', true)
                          ->orderBy('sort_order', 'asc');
                },
                'offers.prices.typePrice',
                'offers.warehouseOffers.warehouse'
            ]);

            $total = $query->count();
            $products = $query->skip(($page - 1) * $perPage)
                             ->take($perPage)
                             ->get();

            $tree = [];

            foreach ($products as $product) {
                $productData = [
                    'id' => $product->id,
                    'product_id' => $product->product_id,
                    'name' => $product->name,
                    'brand' => $product->brand,
                    'model' => $product->model,
                    'offers' => []
                ];

                foreach ($product->offers as $offer) {
                    $prices = [];
                    foreach ($offer->prices as $price) {
                        if ($price->typePrice) {
                            $prices[] = [
                                'type_price_id' => $price->type_price_id,
                                'type' => $price->typePrice->type,
                                'title' => $price->typePrice->title,
                                'price' => (float) $price->price,
                                'currency' => $price->typePrice->currency
                            ];
                        }
                    }

                    $warehouses = [];
                    foreach ($offer->warehouseOffers as $warehouseOffer) {
                        if ($warehouseOffer->warehouse) {
                            $warehouses[] = [
                                'warehouse_id' => $warehouseOffer->warehouse_id,
                                'title' => $warehouseOffer->warehouse->title,
                                'count' => (int) $warehouseOffer->count
                            ];
                        }
                    }

                    $offerData = [
                        'id' => $offer->id,
                        'offer_id' => $offer->offer_id,
                        'name' => $offer->name,
                        'prices' => $prices,
                        'warehouses' => $warehouses
                    ];

                    $productData['offers'][] = $offerData;
                }

                $tree[] = $productData;
            }

            $totalPages = ceil($total / $perPage);

            Log::info('API Catalog: древовидная структура каталога с пагинацией успешно сформирована', [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'current_page_items' => count($tree)
            ]);

            return response()->json([
                'success' => true,
                'data' => $tree,
                'meta' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => $totalPages,
                    'has_next_page' => $page < $totalPages,
                    'has_previous_page' => $page > 1,
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка получения древовидной структуры каталога с пагинацией', [
                'error' => $e->getMessage(),
                'page' => $page,
                'per_page' => $perPage
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при построении структуры каталога с пагинацией',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
