<?php

namespace App\Modules\Catalog\Controllers;

use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\CatalogProductOffer;
use App\Modules\Catalog\Models\PriceType;
use App\Modules\Catalog\Models\CatalogOfferPrice;
use App\Modules\Catalog\Models\CatalogWarehouse;
use App\Modules\Catalog\Models\CatalogOfferWarehouse;
use App\Modules\Catalog\Models\CatalogAttribute;
use App\Modules\Catalog\Models\Tag;
use App\Modules\Catalog\Requests\Offers\CreateOfferRequest;
use App\Modules\Catalog\Requests\Offers\UpdateOfferRequest;
use App\Modules\Catalog\Services\ProductIdGenerator;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

/**
 * Контроллер для работы с предложениями товаров (вариациями)
 *
 * Предоставляет методы для управления вариациями товаров
 */
class OfferController
{
    /**
     * Показывает список предложений товара
     *
     * @param Request $request
     * @param int $productId
     * @return View
     */
    public function index(Request $request, $productId)
    {
        try {
            $product = Product::findOrFail($productId);

            $search = $request->input('search', '');
            $perPage = $request->input('per_page', 25);
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');

            $query = $product->offers()->with(['prices']);

            // Поиск
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('articul_supplier', 'LIKE', "%{$search}%")
                      ->orWhere('offer_id', 'LIKE', "%{$search}%");
                });
            }

            // Сортировка
            $query->orderBy($sortBy, $sortOrder);

            // Пагинация
            $offers = $query->paginate($perPage);

            Log::info('Offers list loaded', [
                'product_id' => $productId,
                'total_offers' => $offers->total()
            ]);

            return view('catalog::offers.index', [
                'product' => $product,
                'offers' => $offers,
                'search' => $search,
                'perPage' => $perPage,
                'sortBy' => $sortBy,
                'sortOrder' => $sortOrder,
                'totalOffers' => $product->offers()->count(),
            ]);
        } catch (Exception $e) {
            Log::error('Error loading offers list', [
                'error' => $e->getMessage(),
                'product_id' => $productId
            ]);
            return back()->with('error', 'Произошла ошибка при загрузке списка предложений');
        }
    }

    /**
     * Показывает форму создания предложения
     *
     * @param int $productId
     * @return View
     */
    public function create($productId)
    {
        try {
            $product = Product::findOrFail($productId);

            // Генерируем уникальный ID предложения
            $idGenerator = new ProductIdGenerator();
            $offerId = $idGenerator->generateOfferId();

            // Получаем активные типы цен
            $priceTypes = PriceType::active()->ordered()->get();

            // Получаем все активные склады
            $warehouses = CatalogWarehouse::getAllActive();

            // Получаем все теги для мультиселекта
            $tags = Tag::orderBy('name')->get();

            // Получаем все атрибуты для виджета
            $attributes = CatalogAttribute::orderBy('name')->get();

            // Создаем пустую коллекцию для совместимости с шаблоном
            $currentPrices = collect();

            Log::info('Offer create form loaded', [
                'product_id' => $productId,
                'generated_offer_id' => $offerId,
                'price_types_count' => $priceTypes->count(),
                'warehouses_count' => $warehouses->count(),
                'tags_count' => $tags->count(),
                'attributes_count' => $attributes->count()
            ]);

            return view('catalog::offers.create', [
                'product' => $product,
                'offerId' => $offerId,
                'priceTypes' => $priceTypes,
                'currentPrices' => $currentPrices,
                'warehouses' => $warehouses,
                'tags' => $tags,
                'attributes' => $attributes,
            ]);
        } catch (Exception $e) {
            Log::error('Error loading offer create form', [
                'error' => $e->getMessage(),
                'product_id' => $productId
            ]);
            return back()->with('error', 'Произошла ошибка при загрузке формы создания');
        }
    }

    /**
     * Сохраняет новое предложение
     *
     * @param CreateOfferRequest $request
     * @param int $productId
     * @return RedirectResponse
     */
    public function store(CreateOfferRequest $request, $productId)
    {
        DB::beginTransaction();

        try {
            $product = Product::findOrFail($productId);
            $validated = $request->validated();

            Log::info('Product offer created', [
                'offer_id' => $validated['offer_id'],
                'product_id' => $product->product_id,
                'name' => $validated['name']
            ]);

            // Добавляем информацию о создателе
            $validated['created_by'] = auth()->id();
            $validated['updated_by'] = auth()->id();
            // Use integer product id, not string product_id
            $validated['product_id'] = $product->id;

            // Создаем предложение
            $offer = CatalogProductOffer::createWithLog($validated);

            // Добавляем цены через новую структуру
            $prices = $request->input('prices', []);
            foreach ($prices as $price) {
                if (!empty($price['price_type_id']) && !empty($price['value'])) {
                    try {
                        CatalogOfferPrice::create([
                            'offer_id' => $offer->id,
                            'price_type_id' => $price['price_type_id'],
                            'price' => (float) str_replace(',', '.', $price['value'])
                        ]);
                    } catch (Exception $e) {
                        Log::error('Error creating offer price', [
                            'error' => $e->getMessage(),
                            'price_data' => $price,
                            'offer_id' => $offer->offer_id
                        ]);
                        // Продолжаем создание, даже если цена не сохранилась
                    }
                }
            }

            // Добавляем остатки на складах
            $warehouseStocks = $request->input('warehouses', []);
            foreach ($warehouseStocks as $warehouseId => $count) {
                if (!empty($count) && $count > 0) {
                    try {
                        CatalogOfferWarehouse::create([
                            'offer_id' => $offer->id,
                            'warehouse_id' => $warehouseId,
                            'count' => (int) $count
                        ]);
                    } catch (Exception $e) {
                        Log::error('Error creating warehouse stock', [
                            'error' => $e->getMessage(),
                            'warehouse_id' => $warehouseId,
                            'count' => $count,
                            'offer_id' => $offer->offer_id
                        ]);
                        // Продолжаем создание, даже если остаток не сохранился
                    }
                }
            }

            // Синхронизируем теги
            if ($request->has('tags')) {
                $offer->tags()->sync($request->input('tags', []));
            }

            // Сохраняем атрибуты
            if ($request->has('attributes')) {
                $this->syncAttributes($offer, $request->input('attributes', []));
            }

            DB::commit();

            Log::info('Offer created successfully', [
                'offer_id' => $offer->offer_id,
                'product_id' => $productId,
                'prices_count' => count($prices),
                'warehouse_stocks_count' => count($warehouseStocks),
                'tags_count' => $offer->tags()->count(),
                'attributes_count' => $offer->catalogAttributes()->count()
            ]);

            return redirect()->route('catalog.products.offers.index', $productId)
                ->with('success', 'Предложение успешно создано');

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error creating offer', [
                'error' => $e->getMessage(),
                'product_id' => $productId,
                'request' => $request->all()
            ]);

            return back()->withInput()
                ->with('error', 'Ошибка при создании предложения: ' . $e->getMessage());
        }
    }

    /**
     * Показывает детальную информацию о предложении
     *
     * @param Product $product
     * @param CatalogProductOffer $offer
     * @return View|RedirectResponse
     */
    public function show(Product $product, CatalogProductOffer $offer): View|RedirectResponse
    {
        // Загружаем теги предложения
        $offer->load('tags');

        return view('catalog::offers.show', [
            'product' => $product,
            'offer' => $offer
        ]);
    }

    /**
     * Показывает форму редактирования предложения
     *
     * @param Product $product
     * @param CatalogProductOffer $offer
     * @return View|RedirectResponse
     */
    public function edit(Product $product, CatalogProductOffer $offer): View|RedirectResponse
    {
        try {
            // Загружаем теги предложения и атрибуты
            $offer->load('tags', 'catalogAttributes');

            // Получаем активные типы цен
            $priceTypes = PriceType::active()->ordered()->get();

            // Получаем текущие цены предложения
            $currentPrices = collect();
            foreach ($offer->prices as $price) {
                if ($price->priceType) {
                    $currentPrices->push([
                        'price_type_id' => $price->price_type_id,
                        'value' => number_format($price->price, 2, '.', '')
                    ]);
                }
            }

            // Получаем все активные склады
            $warehouses = CatalogWarehouse::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->orderBy('title', 'asc')
                ->get();

            // Получаем все теги для мультиселекта
            $tags = Tag::orderBy('name')->get();

            // Получаем все атрибуты для виджета
            $attributes = CatalogAttribute::orderBy('name')->get();

            // Получаем текущие остатки на складах отдельно, чтобы избежать ошибки
            $warehouseStocks = [];

            try {
                // Используем прямой SQL запрос с явным приведением типов
                $stocks = DB::table('catalog_offers_warehouses')
                    ->whereRaw("offer_id = ?", [$offer->id])
                    ->get();

                foreach ($stocks as $stock) {
                    $warehouseStocks[$stock->warehouse_id] = $stock->count;
                }
            } catch (Exception $e) {
                Log::warning('Error loading warehouse stocks, using empty array', [
                    'error' => $e->getMessage(),
                    'offer_id' => $offer->id
                ]);
                $warehouseStocks = [];
            }

            Log::info('Offer edit form loaded', [
                'offer_id' => $offer->id,
                'product_id' => $product->id,
                'price_types_count' => $priceTypes->count(),
                'warehouses_count' => $warehouses->count(),
                'warehouse_stocks_count' => count($warehouseStocks),
                'tags_count' => $tags->count(),
                'attributes_count' => $attributes->count(),
                'offer_attributes_count' => $offer->catalogAttributes->count()
            ]);

            return view('catalog::offers.edit', [
                'product' => $product,
                'offer' => $offer,
                'priceTypes' => $priceTypes,
                'currentPrices' => $currentPrices,
                'warehouses' => $warehouses,
                'warehouseStocks' => $warehouseStocks,
                'tags' => $tags,
                'attributes' => $attributes,
            ]);
        } catch (Exception $e) {
            Log::error('Error loading offer edit form', [
                'error' => $e->getMessage(),
                'offer_id' => $offer->id,
                'product_id' => $product->id
            ]);
            return back()->with('error', 'Предложение не найдено: ' . $e->getMessage());
        }
    }

    /**
     * Обновляет предложение
     *
     * @param UpdateOfferRequest $request
     * @param int $productId
     * @param int $offerId
     * @return RedirectResponse
     */
    public function update(UpdateOfferRequest $request, $productId, $offerId)
    {
        DB::beginTransaction();

        try {
            $product = Product::findOrFail($productId);

            // Find offer by integer id (not offer_id)
            // Use integer product id for comparison
            $offer = CatalogProductOffer::where('id', $offerId)
                ->where('product_id', $product->id)
                ->firstOrFail();

            $validated = $request->validated();

            // Добавляем информацию об обновителе
            $validated['updated_by'] = auth()->id();

            // Обновляем предложение
            $offer->updateWithLog($validated);

            // Обновляем цены
            $prices = $request->input('prices', []);

            Log::info('Processing prices for update', [
                'offer_id' => $offerId,
                'prices_data' => $prices,
                'prices_count' => count($prices)
            ]);

            // Удаляем старые цены
            $deletedCount = CatalogOfferPrice::where('offer_id', $offerId)->delete();
            Log::info('Old prices deleted', ['deleted_count' => $deletedCount]);

            // Добавляем новые цены (только если есть значение)
            $newPrices = [];
            foreach ($prices as $price) {
                if (!empty($price['price_type_id']) && !empty($price['value']) && $price['value'] !== null) {
                    try {
                        CatalogOfferPrice::create([
                            'offer_id' => $offer->id,
                            'price_type_id' => $price['price_type_id'],
                            'price' => (float) str_replace(',', '.', $price['value'])
                        ]);
                        $newPrices[$price['price_type_id']] = $price['value'];
                        Log::debug('Price created successfully', [
                            'offer_id' => $offer->id,
                            'price_type_id' => $price['price_type_id'],
                            'price' => $price['value']
                        ]);
                    } catch (Exception $e) {
                        Log::error('Error creating offer price', [
                            'error' => $e->getMessage(),
                            'price_data' => $price,
                            'offer_id' => $offer->id
                        ]);
                    }
                }
            }

            // Обновляем остатки на складах
            $warehouseStocks = $request->input('warehouses', []);

            // Временное решение: используем прямой запрос для удаления
            try {
                $deletedStocksCount = DB::table('catalog_offers_warehouses')
                    ->where('offer_id', $offerId)
                    ->delete();
                Log::info('Old warehouse stocks deleted', ['deleted_count' => $deletedStocksCount]);
            } catch (Exception $e) {
                // Если возникает ошибка из-за типа данных, пытаемся по-другому
                Log::warning('Could not delete warehouse stocks using where, trying alternative', [
                    'error' => $e->getMessage()
                ]);
                try {
                    // Получаем все записи и удаляем по одной
                    $stocks = DB::table('catalog_offers_warehouses')
                        ->whereRaw("offer_id = ?", [$offerId])
                        ->get();

                    foreach ($stocks as $stock) {
                        DB::table('catalog_offers_warehouses')
                            ->where('id', $stock->id)
                            ->delete();
                    }
                    Log::info('Old warehouse stocks deleted individually', ['deleted_count' => count($stocks)]);
                } catch (Exception $ex) {
                    Log::error('Error deleting warehouse stocks', [
                        'error' => $ex->getMessage(),
                        'offer_id' => $offerId
                    ]);
                    throw $ex;
                }
            }

            // Добавляем новые остатки
            $newStocks = [];
            foreach ($warehouseStocks as $warehouseId => $count) {
                if (!empty($count) && $count > 0) {
                    try {
                        // Используем DB::table напрямую, чтобы избежать проблем с типом данных
                        DB::table('catalog_offers_warehouses')->insert([
                            'offer_id' => $offer->id,
                            'warehouse_id' => $warehouseId,
                            'count' => (int) $count,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        $newStocks[$warehouseId] = $count;
                        Log::debug('Warehouse stock created successfully', [
                            'offer_id' => $offer->id,
                            'warehouse_id' => $warehouseId,
                            'count' => $count
                        ]);
                    } catch (Exception $e) {
                        Log::error('Error creating warehouse stock', [
                            'error' => $e->getMessage(),
                            'warehouse_id' => $warehouseId,
                            'count' => $count,
                            'offer_id' => $offer->id
                        ]);
                    }
                }
            }

            // Синхронизируем теги
            if ($request->has('tags')) {
                $offer->tags()->sync($request->input('tags', []));
            }

            // Сохраняем атрибуты
            if ($request->has('attributes')) {
                $this->syncAttributes($offer, $request->input('attributes', []));
            }

            DB::commit();

            Log::info('Offer updated successfully', [
                'offer_id' => $offerId,
                'product_id' => $productId,
                'prices_count' => count($newPrices),
                'warehouse_stocks_count' => count($newStocks),
                'tags_count' => $offer->tags()->count(),
                'attributes_count' => $offer->catalogAttributes()->count()
            ]);

            return redirect()->route('catalog.products.offers.index', $productId)
                ->with('success', 'Предложение успешно обновлено');

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error updating offer', [
                'error' => $e->getMessage(),
                'offer_id' => $offerId,
                'product_id' => $productId,
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withInput()
                ->with('error', 'Ошибка при обновлении предложения: ' . $e->getMessage());
        }
    }

    /**
     * Удаляет предложение
     *
     * @param int $productId
     * @param int $offerId
     * @return RedirectResponse
     * @throws Throwable
     */
    public function destroy($productId, $offerId)
    {
        DB::beginTransaction();

        try {
            $product = Product::findOrFail($productId);

            // Find offer by primary key id
            // Note: product_id in offers table is the integer product id, not the string product_id
            $offer = CatalogProductOffer::where('id', $offerId)
                ->where('product_id', $product->id)
                ->firstOrFail();

            // Проверяем, есть ли связанные данные на складах
            $warehouseCount = $offer->warehouseOffers()->count();
            if ($warehouseCount > 0) {
                DB::rollBack();

                return back()->with('error', 'Невозможно удалить предложение, так как оно есть на складах. Сначала удалите наличие на складах.');
            }

            // Delete the offer
            $offer->deleteWithLog();

            DB::commit();

            Log::info('Offer deleted successfully', [
                'offer_id' => $offer->offer_id,
                'product_id' => $productId
            ]);

            return redirect()->route('catalog.products.offers.index', $productId)
                ->with('success', 'Предложение успешно удалено');

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error deleting offer', [
                'error' => $e->getMessage(),
                'offer_id' => $offerId,
                'product_id' => $productId,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Ошибка при удалении предложения: ' . $e->getMessage());
        }
    }

    /**
     * Синхронизирует атрибуты для модели
     *
     * @param mixed $model
     * @param array $attributes
     * @return void
     */
    private function syncAttributes($model, array $attributes): void
    {
        // Удаляем старые атрибуты
        $model->catalogAttributes()->detach();

        // Добавляем новые атрибуты
        foreach ($attributes as $attrData) {
            if (!empty($attrData['id']) && !empty($attrData['value'])) {
                try {
                    $model->catalogAttributes()->attach($attrData['id'], [
                        'value' => $attrData['value'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } catch (Exception $e) {
                    Log::error('Error attaching attribute', [
                        'error' => $e->getMessage(),
                        'attribute_id' => $attrData['id'],
                        'model_id' => $model->id,
                        'model_type' => get_class($model)
                    ]);
                }
            }
        }
    }
}
