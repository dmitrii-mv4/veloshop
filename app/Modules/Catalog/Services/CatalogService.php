<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\CatalogWarehouse;
use App\Modules\Catalog\Models\Product;
use Illuminate\Support\Facades\Log;

/**
 * Сервис каталога
 *
 * Содержит бизнес-логику для работы с каталогом товаров.
 * Предоставляет методы для сложных операций с товарами и складами.
 */
class CatalogService
{
    /**
     * Создает новый товар с полной информацией
     */
    public function createProductWithOffers(array $productData, array $offersData = []): Product
    {
        try {
            // Создаем товар
            $product = Product::createWithLog($productData);

            // Создаем предложения, если они переданы
            foreach ($offersData as $offerData) {
                $offerData['product_id'] = $product->product_id;
                $offerData['created_by'] = auth()->id();
                $offerData['updated_by'] = auth()->id();

                Offer::createWithLog($offerData);
            }

            Log::info('Product with offers created successfully', [
                'product_id' => $product->id,
                'offers_count' => count($offersData),
            ]);

            return $product;
        } catch (\Exception $e) {
            Log::error('Error creating product with offers', [
                'error' => $e->getMessage(),
                'product_data' => $productData,
            ]);
            throw $e;
        }
    }

    /**
     * Получает товар с полной информацией (предложения, цены, атрибуты, склады)
     */
    public function getProductFullInfo(string $productId): Product
    {
        try {
            $product = Product::where('product_id', $productId)
                ->with([
                    'offers' => function ($query) {
                        $query->with([
                            'prices',
                            'attributes',
                            'warehouseOffers.warehouse',
                        ]);
                    },
                ])
                ->firstOrFail();

            Log::info('Product full info loaded', ['product_id' => $productId]);

            return $product;
        } catch (\Exception $e) {
            Log::error('Error loading product full info', [
                'error' => $e->getMessage(),
                'product_id' => $productId,
            ]);
            throw $e;
        }
    }

    /**
     * Обновляет наличие товара на складе
     */
    public function updateWarehouseQuantity(string $offerId, int $warehouseId, int $quantity): bool
    {
        try {
            $warehouseOffer = \App\Modules\Catalog\Models\CatalogWarehouseOffer::updateOrCreate(
                [
                    'offer_id' => $offerId,
                    'warehouses_id' => $warehouseId,
                ],
                ['quantity' => $quantity]
            );

            Log::info('Warehouse quantity updated', [
                'offer_id' => $offerId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error updating warehouse quantity', [
                'error' => $e->getMessage(),
                'offer_id' => $offerId,
                'warehouse_id' => $warehouseId,
            ]);
            throw $e;
        }
    }

    /**
     * Получает общее количество товара на всех складах
     */
    public function getOfferTotalQuantity(string $offerId): int
    {
        try {
            $quantity = \App\Modules\Catalog\Models\CatalogWarehouseOffer::where('offer_id', $offerId)
                ->sum('quantity');

            Log::info('Offer total quantity calculated', [
                'offer_id' => $offerId,
                'quantity' => $quantity,
            ]);

            return (int) $quantity;
        } catch (\Exception $e) {
            Log::error('Error calculating offer total quantity', [
                'error' => $e->getMessage(),
                'offer_id' => $offerId,
            ]);
            throw $e;
        }
    }

    /**
     * Получает статистику каталога
     */
    public function getCatalogStatistics(): array
    {
        try {
            $totalProducts = Product::count();
            $totalOffers = Offer::count();
            $totalWarehouses = CatalogWarehouse::count();

            // Товары с наибольшим количеством предложений
            $productsWithMostOffers = Product::withCount('offers')
                ->orderBy('offers_count', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($product) {
                    return [
                        'name' => $product->name,
                        'offers_count' => $product->offers_count,
                        'product_id' => $product->product_id,
                    ];
                });

            // Склады с наибольшим количеством товаров
            $warehousesWithMostProducts = CatalogWarehouse::withCount('warehouseOffers')
                ->orderBy('warehouse_offers_count', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($warehouse) {
                    return [
                        'address' => $warehouse->address,
                        'products_count' => $warehouse->warehouse_offers_count,
                    ];
                });

            $statistics = [
                'total_products' => $totalProducts,
                'total_offers' => $totalOffers,
                'total_warehouses' => $totalWarehouses,
                'products_with_most_offers' => $productsWithMostOffers,
                'warehouses_with_most_products' => $warehousesWithMostProducts,
                'average_offers_per_product' => $totalProducts > 0 ? round($totalOffers / $totalProducts, 2) : 0,
            ];

            Log::info('Catalog statistics calculated', $statistics);

            return $statistics;
        } catch (\Exception $e) {
            Log::error('Error calculating catalog statistics', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Поиск товаров с фильтрацией
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function searchProducts(array $filters = [])
    {
        try {
            $query = Product::query();

            // Фильтр по названию
            if (isset($filters['search']) && $filters['search']) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'LIKE', "%{$filters['search']}%")
                        ->orWhere('brand', 'LIKE', "%{$filters['search']}%")
                        ->orWhere('model', 'LIKE', "%{$filters['search']}%");
                });
            }

            // Фильтр по бренду
            if (isset($filters['brand']) && $filters['brand']) {
                $query->where('brand', $filters['brand']);
            }

            // Фильтр по сезону
            if (isset($filters['seazon']) && $filters['seazon']) {
                $query->where('seazon', $filters['seazon']);
            }

            // Сортировка
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            // Пагинация
            $perPage = $filters['per_page'] ?? 25;
            $products = $query->paginate($perPage);

            Log::info('Products search performed', [
                'filters' => $filters,
                'total_results' => $products->total(),
            ]);

            return $products;
        } catch (\Exception $e) {
            Log::error('Error searching products', [
                'error' => $e->getMessage(),
                'filters' => $filters,
            ]);
            throw $e;
        }
    }

    /**
     * Генерирует уникальный ID товара
     */
    public function generateProductId(string $prefix = 'U'): string
    {
        try {
            do {
                $productId = $prefix.str_pad(mt_rand(1, 99999999999), 11, '0', STR_PAD_LEFT);
            } while (Product::where('product_id', $productId)->exists());

            Log::info('Product ID generated', ['product_id' => $productId]);

            return $productId;
        } catch (\Exception $e) {
            Log::error('Error generating product ID', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Генерирует уникальный ID предложения
     */
    public function generateOfferId(string $prefix = 'HQ-'): string
    {
        try {
            do {
                $offerId = $prefix.str_pad(mt_rand(1, 9999999), 7, '0', STR_PAD_LEFT);
            } while (Offer::where('offer_id', $offerId)->exists());

            Log::info('Offer ID generated', ['offer_id' => $offerId]);

            return $offerId;
        } catch (\Exception $e) {
            Log::error('Error generating offer ID', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
