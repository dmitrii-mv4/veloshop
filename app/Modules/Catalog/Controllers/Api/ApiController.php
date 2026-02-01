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
            $offers = CatalogProductOffer::with(['prices.typePrice', 'attributes'])->get();
            
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
            
            // Получаем все продукты с их офферами и ценами
            $products = Product::with(['offers' => function($query) {
                // Загружаем цены с типами цен и атрибуты
                $query->with(['prices.typePrice', 'attributes'])
                      ->orderBy('created_at', 'desc');
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
            // Формируем структурированные данные цен
            $pricesTree = $this->buildPricesTree($offer->prices);
            
            // Формируем структурированные данные атрибутов
            $attributesTree = $this->buildAttributesTree($offer->attributes);
            
            // Находим основную цену (самая низкая цена из всех типов)
            $mainPrice = $this->getMainPrice($pricesTree);
            
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
                'prices' => $pricesTree,
                'main_price' => $mainPrice,
                'attributes' => $attributesTree,
                'created_by' => $offer->created_by,
                'updated_by' => $offer->updated_by,
                'created_at' => $offer->created_at,
                'updated_at' => $offer->updated_at
            ];
        })->toArray();
    }
    
    /**
     * Построение структуры цен оффера
     *
     * @param \Illuminate\Database\Eloquent\Collection $prices
     * @return array
     */
    private function buildPricesTree($prices): array
    {
        return $prices->map(function($price) {
            return [
                'id' => $price->id,
                'offer_id' => $price->offer_id,
                'type_price_id' => $price->type_price_id,
                'price' => (float) $price->price,
                'type_price' => $price->typePrice ? [
                    'id' => $price->typePrice->id,
                    'title' => $price->typePrice->title,
                    'type' => $price->typePrice->type,
                    'currency' => $price->typePrice->currency,
                    'is_active' => (bool) $price->typePrice->is_active,
                    'sort_order' => $price->typePrice->sort_order
                ] : null,
                'formatted_price' => $price->typePrice ? 
                    number_format($price->price, 2, '.', ' ') . ' ' . 
                    ($price->typePrice->currency === 'RUB' ? '₽' : $price->typePrice->currency) : 
                    number_format($price->price, 2, '.', ' '),
                'created_at' => $price->created_at,
                'updated_at' => $price->updated_at
            ];
        })->toArray();
    }
    
    /**
     * Построение структуры атрибутов оффера
     *
     * @param \Illuminate\Database\Eloquent\Collection $attributes
     * @return array
     */
    private function buildAttributesTree($attributes): array
    {
        $attributeTypes = [
            'color' => 'Цвет',
            'size' => 'Размер',
            'weight' => 'Вес',
            'material' => 'Материал',
            'dimensions' => 'Габариты',
            'storage' => 'Объем памяти',
            'screen' => 'Экран',
            'cpu' => 'Процессор',
            'ram' => 'Оперативная память'
        ];
        
        return $attributes->map(function($attribute) use ($attributeTypes) {
            return [
                'id' => $attribute->id,
                'offer_id' => $attribute->offer_id,
                'type' => $attribute->attributes_type,
                'type_label' => $attributeTypes[$attribute->attributes_type] ?? $attribute->attributes_type,
                'value' => $attribute->attributes_value,
                'created_at' => $attribute->created_at,
                'updated_at' => $attribute->updated_at
            ];
        })->toArray();
    }
    
    /**
     * Получение основной цены оффера
     *
     * @param array $pricesTree
     * @return array|null
     */
    private function getMainPrice(array $pricesTree): ?array
    {
        if (empty($pricesTree)) {
            return null;
        }
        
        // Ищем основную цену (тип 'uprice')
        foreach ($pricesTree as $price) {
            if ($price['type_price'] && $price['type_price']['type'] === 'uprice') {
                return $price;
            }
        }
        
        // Если основной цены нет, возвращаем первую цену
        return $pricesTree[0];
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
            
            $product = Product::with(['offers' => function($query) {
                $query->with(['prices.typePrice', 'attributes']);
            }])->where('product_id', $productId)->first();
            
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
            
            $offers = CatalogProductOffer::with(['prices.typePrice', 'attributes'])
                ->where('product_id', $productId)
                ->get();
            
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
            
            $products = Product::with(['offers' => function($query) {
                $query->with(['prices.typePrice', 'attributes']);
            }])->where('brand', $brand)->get();
            
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
                $offersTree = $this->buildOffersTree($product->offers);
                
                return [
                    'id' => $product->id,
                    'product_id' => $product->product_id,
                    'name' => $product->name,
                    'model' => $product->model,
                    'offers' => $offersTree,
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
    
    /**
     * Получение цен по ID предложения
     *
     * @param string $offerId
     * @return JsonResponse
     */
    public function getPricesByOffer(string $offerId): JsonResponse
    {
        try {
            Log::info('API Catalog: получение цен по ID предложения', ['offer_id' => $offerId]);
            
            $prices = CatalogOfferPrice::with('typePrice')
                ->where('offer_id', $offerId)
                ->get();
            
            if ($prices->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'prices' => []
                    ]
                ]);
            }
            
            $pricesTree = $this->buildPricesTree($prices);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'offer_id' => $offerId,
                    'prices' => $pricesTree,
                    'prices_count' => $prices->count(),
                    'main_price' => $this->getMainPrice($pricesTree)
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка получения цен по ID предложения', [
                'offer_id' => $offerId,
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении цен'
            ], 500);
        }
    }
    
    /**
     * Получение всех типов цен
     *
     * @return JsonResponse
     */
    public function getPriceTypes(): JsonResponse
    {
        try {
            Log::info('API Catalog: получение типов цен');
            
            $priceTypes = CatalogTypePrice::active()->ordered()->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'price_types' => $priceTypes,
                    'count' => $priceTypes->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка получения типов цен', [
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении типов цен'
            ], 500);
        }
    }
    
    /**
     * Получение оффера по его ID с полной информацией
     *
     * @param string $offerId
     * @return JsonResponse
     */
    public function getOfferById(string $offerId): JsonResponse
    {
        try {
            Log::info('API Catalog: получение оффера по ID', ['offer_id' => $offerId]);
            
            $offer = CatalogProductOffer::with(['prices.typePrice', 'attributes'])
                ->where('offer_id', $offerId)
                ->first();
            
            if (!$offer) {
                Log::warning('API Catalog: оффер не найден', ['offer_id' => $offerId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Оффер не найден'
                ], 404);
            }
            
            $pricesTree = $this->buildPricesTree($offer->prices);
            $attributesTree = $this->buildAttributesTree($offer->attributes);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'offer' => [
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
                        'prices' => $pricesTree,
                        'main_price' => $this->getMainPrice($pricesTree),
                        'attributes' => $attributesTree,
                        'created_at' => $offer->created_at,
                        'updated_at' => $offer->updated_at
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка получения оффера по ID', [
                'offer_id' => $offerId,
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении оффера'
            ], 500);
        }
    }
    
    /**
     * Поиск товаров и предложений по названию
     *
     * @param string $query
     * @return JsonResponse
     */
    public function search(string $query): JsonResponse
    {
        try {
            Log::info('API Catalog: поиск товаров и предложений', ['query' => $query]);
            
            // Ищем продукты
            $products = Product::where('name', 'LIKE', "%{$query}%")
                ->orWhere('brand', 'LIKE', "%{$query}%")
                ->orWhere('model', 'LIKE', "%{$query}%")
                ->with(['offers' => function($q) use ($query) {
                    $q->with(['prices.typePrice', 'attributes']);
                }])
                ->get();
            
            // Ищем предложения
            $offers = CatalogProductOffer::where('name', 'LIKE', "%{$query}%")
                ->orWhere('articul_supplier', 'LIKE', "%{$query}%")
                ->with(['prices.typePrice', 'attributes'])
                ->get();
            
            $structuredProducts = $this->buildCatalogTree($products);
            $structuredOffers = $this->buildOffersTree($offers);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'query' => $query,
                    'products' => $structuredProducts['products'] ?? [],
                    'offers' => $structuredOffers,
                    'meta' => [
                        'products_count' => $products->count(),
                        'offers_count' => $offers->count(),
                        'timestamp' => now()->toISOString()
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Catalog: ошибка поиска', [
                'query' => $query,
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при поиске'
            ], 500);
        }
    }
}