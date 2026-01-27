<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Core\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\CatalogProductOffer;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    /**
     * Получение раздельных данных каталога
     * Возвращает продукты и офферы отдельными массивами
     *
     * @return JsonResponse
     */
    public function getSeparate(): JsonResponse
    {
        try {
            Log::info('API Catalog: получение раздельных данных каталога');
            
            $products = Product::all();
            $offers = CatalogProductOffer::all();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'products' => $products,
                    'offers' => $offers
                ],
                'meta' => [
                    'products_count' => $products->count(),
                    'offers_count' => $offers->count(),
                    'timestamp' => now()->toISOString()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка получения раздельных данных', [
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении данных каталога',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Получение древовидной структуры каталога
     * Возвращает продукты с вложенными офферами в виде дерева
     *
     * @return JsonResponse
     */
    public function getTree(): JsonResponse
    {
        try {
            Log::info('API Catalog: получение древовидной структуры каталога');
            
            // Получаем все продукты с их офферами
            $products = Product::with(['offers' => function($query) {
                // Можно добавить сортировку или фильтрацию офферов
                $query->orderBy('created_at', 'desc');
            }])->get();
            
            // Если нет продуктов
            if ($products->isEmpty()) {
                Log::info('API Catalog: продукты не найдены');
                return response()->json([
                    'success' => true,
                    'data' => [
                        'products' => []
                    ]
                ]);
            }
            
            // Структурируем данные в древовидный формат
            $treeData = $this->buildCatalogTree($products);
            
            Log::info('API Catalog: древовидная структура успешно построена', [
                'products_count' => $products->count(),
                'total_offers' => $products->sum(fn($product) => $product->offers->count())
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $treeData
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка получения древовидной структуры', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при построении древовидной структуры каталога',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Построение древовидной структуры каталога
     *
     * @param \Illuminate\Database\Eloquent\Collection $products
     * @return array
     */
    private function buildCatalogTree($products): array
    {
        $structuredProducts = $products->map(function($product) {
            // Формируем структурированные данные офферов
            $offersTree = $this->buildOffersTree($product->offers);
            
            return [
                'id' => $product->id,
                'product_id' => $product->product_id,
                'name' => $product->name,
                'brand' => $product->brand,
                'model' => $product->model,
                'seazon' => $product->seazon,
                'group_name' => $product->group_name,
                'meta' => [
                    'title' => $product->meta_title,
                    'description' => $product->meta_description,
                    'keywords' => $product->meta_keywords
                ],
                'offers' => $offersTree,
                'offers_count' => $product->offers->count(),
                'created_by' => $product->created_by,
                'updated_by' => $product->updated_by,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at
            ];
        })->toArray();
        
        // Группируем продукты по брендам (если нужно)
        $groupedByBrand = $this->groupProductsByBrand($structuredProducts);
        
        return [
            'products' => $structuredProducts,
            'grouped_by_brand' => $groupedByBrand,
            'meta' => [
                'total_products' => count($structuredProducts),
                'total_offers' => array_sum(array_column($structuredProducts, 'offers_count')),
                'brands_count' => count($groupedByBrand),
                'timestamp' => now()->toISOString()
            ]
        ];
    }
    
    /**
     * Построение структуры офферов продукта
     *
     * @param \Illuminate\Database\Eloquent\Collection $offers
     * @return array
     */
    private function buildOffersTree($offers): array
    {
        return $offers->map(function($offer) {
            return [
                'id' => $offer->id,
                'offer_id' => $offer->offer_id,
                'product_id' => $offer->product_id,
                'articul_supplier' => $offer->articul_supplier,
                'name' => $offer->name,
                'meta' => [
                    'title' => $offer->meta_title,
                    'description' => $offer->meta_description,
                    'keywords' => $offer->meta_keywords
                ],
                'attributes' => $this->extractAttributes($offer),
                'created_by' => $offer->created_by,
                'updated_by' => $offer->updated_by,
                'created_at' => $offer->created_at,
                'updated_at' => $offer->updated_at
            ];
        })->toArray();
    }
    
    /**
     * Извлечение атрибутов оффера (если есть дополнительные поля)
     *
     * @param CatalogProductOffer $offer
     * @return array
     */
    private function extractAttributes(CatalogProductOffer $offer): array
    {
        $attributes = [];
        
        // Добавляем дополнительные поля как атрибуты
        $additionalFields = ['color', 'size', 'material', 'weight', 'price', 'quantity'];
        
        foreach ($additionalFields as $field) {
            if (isset($offer->$field) && !empty($offer->$field)) {
                $attributes[$field] = $offer->$field;
            }
        }
        
        return $attributes;
    }
    
    /**
     * Группировка продуктов по брендам
     *
     * @param array $products
     * @return array
     */
    private function groupProductsByBrand(array $products): array
    {
        $grouped = [];
        
        foreach ($products as $product) {
            $brand = $product['brand'] ?? 'Без бренда';
            
            if (!isset($grouped[$brand])) {
                $grouped[$brand] = [
                    'brand' => $brand,
                    'products' => [],
                    'products_count' => 0,
                    'offers_count' => 0
                ];
            }
            
            $grouped[$brand]['products'][] = [
                'id' => $product['id'],
                'product_id' => $product['product_id'],
                'name' => $product['name'],
                'offers_count' => $product['offers_count']
            ];
            
            $grouped[$brand]['products_count']++;
            $grouped[$brand]['offers_count'] += $product['offers_count'];
        }
        
        // Преобразуем в индексный массив
        return array_values($grouped);
    }
    
    /**
     * Получение продукта по его ID
     *
     * @param string $productId
     * @return JsonResponse
     */
    public function getByProductId(string $productId): JsonResponse
    {
        try {
            Log::info('API Catalog: получение продукта по ID', ['product_id' => $productId]);
            
            $product = Product::with('offers')->where('product_id', $productId)->first();
            
            if (!$product) {
                Log::warning('API Catalog: продукт не найден', ['product_id' => $productId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Продукт не найден'
                ], 404);
            }
            
            $offersTree = $this->buildOffersTree($product->offers);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'product' => [
                        'id' => $product->id,
                        'product_id' => $product->product_id,
                        'name' => $product->name,
                        'brand' => $product->brand,
                        'model' => $product->model,
                        'seazon' => $product->seazon,
                        'group_name' => $product->group_name,
                        'meta' => [
                            'title' => $product->meta_title,
                            'description' => $product->meta_description,
                            'keywords' => $product->meta_keywords
                        ]
                    ],
                    'offers' => $offersTree
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка получения продукта по ID', [
                'product_id' => $productId,
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении продукта'
            ], 500);
        }
    }
    
    /**
     * Получение офферов по ID продукта
     *
     * @param string $productId
     * @return JsonResponse
     */
    public function getOffersByProduct(string $productId): JsonResponse
    {
        try {
            Log::info('API Catalog: получение офферов по ID продукта', ['product_id' => $productId]);
            
            $offers = CatalogProductOffer::where('product_id', $productId)->get();
            
            if ($offers->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'offers' => []
                    ]
                ]);
            }
            
            $offersTree = $this->buildOffersTree($offers);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'product_id' => $productId,
                    'offers' => $offersTree,
                    'offers_count' => $offers->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка получения офферов по ID продукта', [
                'product_id' => $productId,
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении офферов'
            ], 500);
        }
    }
    
    /**
     * Получение продуктов по бренду
     *
     * @param string $brand
     * @return JsonResponse
     */
    public function getByBrand(string $brand): JsonResponse
    {
        try {
            Log::info('API Catalog: получение продуктов по бренду', ['brand' => $brand]);
            
            $products = Product::with('offers')->where('brand', $brand)->get();
            
            if ($products->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'brand' => $brand,
                        'products' => []
                    ]
                ]);
            }
            
            $structuredProducts = $products->map(function($product) {
                return [
                    'id' => $product->id,
                    'product_id' => $product->product_id,
                    'name' => $product->name,
                    'model' => $product->model,
                    'offers_count' => $product->offers->count(),
                    'created_at' => $product->created_at
                ];
            })->toArray();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'brand' => $brand,
                    'products' => $structuredProducts,
                    'products_count' => $products->count(),
                    'total_offers' => $products->sum(fn($product) => $product->offers->count())
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка получения продуктов по бренду', [
                'brand' => $brand,
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении продуктов по бренду'
            ], 500);
        }
    }
}