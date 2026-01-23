<?php

namespace App\Modules\Catalog\Controllers;

use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\CatalogProductOffer;
use App\Modules\Catalog\Models\CatalogOfferPrice;
use App\Modules\Catalog\Models\CatalogOfferAttribute;
use App\Modules\Catalog\Requests\CreateOfferRequest;
use App\Modules\Catalog\Requests\UpdateOfferRequest;
use App\Modules\Catalog\Services\ProductIdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
     * @return \Illuminate\View\View
     */
    public function index(Request $request, $productId)
    {
        try {
            $product = Product::findOrFail($productId);

            $search = $request->input('search', '');
            $perPage = $request->input('per_page', 25);
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');

            $query = $product->offers()->with(['prices', 'attributes']);

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
        } catch (\Exception $e) {
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
     * @return \Illuminate\View\View
     */
    public function create($productId)
    {
        try {
            $product = Product::findOrFail($productId);

            // Генерируем уникальный ID предложения
            $idGenerator = new ProductIdGenerator();
            $offerId = $idGenerator->generateOfferId();

            Log::info('Offer create form loaded', [
                'product_id' => $productId,
                'generated_offer_id' => $offerId
            ]);

            return view('catalog::offers.create', [
                'product' => $product,
                'offerId' => $offerId,
                'priceTypes' => $this->getPriceTypes(),
                'attributeTypes' => $this->getAttributeTypes()
            ]);
        } catch (\Exception $e) {
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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CreateOfferRequest $request, $productId)
    {
        DB::beginTransaction();

        try {
            $product = Product::findOrFail($productId);
            $validated = $request->validated();

            // Добавляем информацию о создателе
            $validated['created_by'] = auth()->id();
            $validated['updated_by'] = auth()->id();
            $validated['product_id'] = $product->product_id;

            // Создаем предложение
            $offer = CatalogProductOffer::createWithLog($validated);

            // Добавляем цены
            $prices = $request->input('prices', []);
            foreach ($prices as $price) {
                if (!empty($price['type']) && !empty($price['value'])) {
                    CatalogOfferPrice::create([
                        'offer_id' => $offer->offer_id,
                        'price_type' => $price['type'],
                        'price' => (float) str_replace(',', '.', $price['value'])
                    ]);
                }
            }

            // Добавляем атрибуты
            $attributes = $request->input('attributes', []);
            foreach ($attributes as $attribute) {
                if (!empty($attribute['type']) && !empty($attribute['value'])) {
                    CatalogOfferAttribute::create([
                        'offer_id' => $offer->offer_id,
                        'attributes_type' => $attribute['type'],
                        'attributes_value' => $attribute['value']
                    ]);
                }
            }

            DB::commit();

            Log::info('Offer created successfully', [
                'offer_id' => $offer->offer_id,
                'product_id' => $productId,
                'prices_count' => count($prices),
                'attributes_count' => count($attributes)
            ]);

            return redirect()->route('catalog.products.offers.index', $productId)
                ->with('success', 'Предложение успешно создано');

        } catch (\Exception $e) {
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
     * @param int $productId
     * @param string $offerId
     * @return \Illuminate\View\View
     */
    public function show($productId, $offerId)
    {
        try {
            $product = Product::findOrFail($productId);
            $offer = CatalogProductOffer::with(['prices', 'attributes', 'warehouseOffers.warehouse'])
                ->where('offer_id', $offerId)
                ->where('product_id', $product->product_id)
                ->firstOrFail();

            Log::info('Offer details loaded', [
                'offer_id' => $offerId,
                'product_id' => $productId
            ]);

            return view('catalog::offers.show', [
                'product' => $product,
                'offer' => $offer
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading offer details', [
                'error' => $e->getMessage(),
                'offer_id' => $offerId,
                'product_id' => $productId
            ]);
            return back()->with('error', 'Предложение не найдено');
        }
    }

    /**
     * Показывает форму редактирования предложения
     *
     * @param int $productId
     * @param string $offerId
     * @return \Illuminate\View\View
     */
    public function edit($productId, $offerId)
    {
        try {
            $product = Product::findOrFail($productId);
            $offer = CatalogProductOffer::with(['prices', 'attributes'])
                ->where('offer_id', $offerId)
                ->where('product_id', $product->product_id)
                ->firstOrFail();

            Log::info('Offer edit form loaded', [
                'offer_id' => $offerId,
                'product_id' => $productId
            ]);

            return view('catalog::offers.edit', [
                'product' => $product,
                'offer' => $offer,
                'priceTypes' => $this->getPriceTypes(),
                'attributeTypes' => $this->getAttributeTypes()
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading offer edit form', [
                'error' => $e->getMessage(),
                'offer_id' => $offerId,
                'product_id' => $productId
            ]);
            return back()->with('error', 'Предложение не найдено');
        }
    }

    /**
     * Обновляет предложение
     *
     * @param UpdateOfferRequest $request
     * @param int $productId
     * @param string $offerId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateOfferRequest $request, $productId, $offerId)
    {
        DB::beginTransaction();

        try {
            $product = Product::findOrFail($productId);
            $offer = CatalogProductOffer::where('offer_id', $offerId)
                ->where('product_id', $product->product_id)
                ->firstOrFail();

            $validated = $request->validated();

            // Добавляем информацию об обновителе
            $validated['updated_by'] = auth()->id();

            // Обновляем предложение
            $offer->updateWithLog($validated);

            // Обновляем цены
            $prices = $request->input('prices', []);

            // Удаляем старые цены
            CatalogOfferPrice::where('offer_id', $offerId)->delete();

            // Добавляем новые цены
            foreach ($prices as $price) {
                if (!empty($price['type']) && !empty($price['value'])) {
                    CatalogOfferPrice::create([
                        'offer_id' => $offer->offer_id,
                        'price_type' => $price['type'],
                        'price' => (float) str_replace(',', '.', $price['value'])
                    ]);
                }
            }

            // Обновляем атрибуты
            $attributes = $request->input('attributes', []);

            // Удаляем старые атрибуты
            CatalogOfferAttribute::where('offer_id', $offerId)->delete();

            // Добавляем новые атрибуты
            foreach ($attributes as $attribute) {
                if (!empty($attribute['type']) && !empty($attribute['value'])) {
                    CatalogOfferAttribute::create([
                        'offer_id' => $offer->offer_id,
                        'attributes_type' => $attribute['type'],
                        'attributes_value' => $attribute['value']
                    ]);
                }
            }

            DB::commit();

            Log::info('Offer updated successfully', [
                'offer_id' => $offerId,
                'product_id' => $productId,
                'prices_count' => count($prices),
                'attributes_count' => count($attributes)
            ]);

            return redirect()->route('catalog.products.offers.index', $productId)
                ->with('success', 'Предложение успешно обновлено');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating offer', [
                'error' => $e->getMessage(),
                'offer_id' => $offerId,
                'product_id' => $productId,
                'request' => $request->all()
            ]);

            return back()->withInput()
                ->with('error', 'Ошибка при обновлении предложения: ' . $e->getMessage());
        }
    }

    /**
     * Удаляет предложение
     *
     * @param int $productId
     * @param string $offerId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($productId, $offerId)
    {
        DB::beginTransaction();

        try {
            $product = Product::findOrFail($productId);
            $offer = CatalogProductOffer::where('offer_id', $offerId)
                ->where('product_id', $product->product_id)
                ->firstOrFail();

            // Проверяем, есть ли связанные данные на складах
            if ($offer->warehouseOffers()->count() > 0) {
                return back()->with('error', 'Невозможно удалить предложение, так как оно есть на складах. Сначала удалите наличие на складах.');
            }

            // Удаляем связанные цены и атрибуты
            CatalogOfferPrice::where('offer_id', $offerId)->delete();
            CatalogOfferAttribute::where('offer_id', $offerId)->delete();

            // Удаляем предложение
            $offer->deleteWithLog();

            DB::commit();

            Log::info('Offer deleted successfully', [
                'offer_id' => $offerId,
                'product_id' => $productId
            ]);

            return redirect()->route('catalog.products.offers.index', $productId)
                ->with('success', 'Предложение успешно удалено');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error deleting offer', [
                'error' => $e->getMessage(),
                'offer_id' => $offerId,
                'product_id' => $productId
            ]);

            return back()->with('error', 'Ошибка при удалении предложения: ' . $e->getMessage());
        }
    }

    /**
     * Получает список типов цен
     *
     * @return array
     */
    private function getPriceTypes(): array
    {
        return [
            'uprice' => 'Основная цена',
            'price_marketplace' => 'Цена на маркетплейсе',
            'price_wholesale' => 'Оптовая цена',
            'price_discount' => 'Цена со скидкой',
            'price_special' => 'Специальная цена'
        ];
    }

    /**
     * Получает список типов атрибутов
     *
     * @return array
     */
    private function getAttributeTypes(): array
    {
        return [
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
    }
}
