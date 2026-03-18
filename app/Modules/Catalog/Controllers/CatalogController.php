<?php

namespace App\Modules\Catalog\Controllers;

use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\CatalogProductOffer;
use App\Modules\Catalog\Models\CatalogWarehouse;
use App\Modules\Catalog\Models\CatalogCategory;
use App\Modules\Catalog\Requests\CreateProductRequest;
use App\Modules\Catalog\Requests\UpdateProductRequest;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Контроллер каталога
 *
 * Основной контроллер для управления каталогом товаров.
 * Предоставляет методы для работы с товарами, предложениями и складами.
 */
class CatalogController
{
    /**
     * Показывает список товаров
     *
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function index(Request $request): View|RedirectResponse
    {
        try {
            $search = $request->input('search', '');
            $perPage = $request->input('per_page', 25);
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');

            $query = Product::query();

            // Поиск
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('brand', 'LIKE', "%{$search}%")
                      ->orWhere('model', 'LIKE', "%{$search}%")
                      ->orWhere('product_id', 'LIKE', "%{$search}%");
                });
            }

            // Сортировка
            $query->orderBy($sortBy, $sortOrder);

            // Пагинация
            $products = $query->paginate($perPage);

            Log::info('Catalog index loaded', [
                'search' => $search,
                'total_products' => $products->total()
            ]);

            return view('catalog::products.index', [
                'products' => $products,
                'search' => $search,
                'perPage' => $perPage,
                'sortBy' => $sortBy,
                'sortOrder' => $sortOrder,
                'totalProducts' => Product::count(),
                'totalOffers' => CatalogProductOffer::count(),
            ]);
        } catch (Exception $e) {
            Log::error('Error loading catalog index', ['error' => $e->getMessage()]);
            return back()->with('error', 'Произошла ошибка при загрузке каталога');
        }
    }

    /**
     * Показывает форму создания товара
     *
     * @return View|RedirectResponse
     */
    public function create(): View|RedirectResponse
    {
        try {
            // Генерируем уникальный ID товара
            $productId = 'U' . str_pad(mt_rand(1, 99999999999), 11, '0', STR_PAD_LEFT);

            // Получаем все категории для селекта
            $categories = CatalogCategory::orderBy('name')->get();

            Log::info('Catalog create form loaded', [
                'generated_product_id' => $productId,
                'categories_count' => $categories->count()
            ]);

            return view('catalog::products.create', [
                'productId' => $productId,
                'categories' => $categories
            ]);
        } catch (Exception $e) {
            Log::error('Error loading create form', ['error' => $e->getMessage()]);
            return back()->with('error', 'Произошла ошибка при загрузке формы создания');
        }
    }

    /**
     * Сохраняет новый товар
     *
     * @param CreateProductRequest $request
     * @return RedirectResponse
     */
    public function store(CreateProductRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();

            // Добавляем информацию о создателе
            $validated['created_by'] = auth()->id();
            $validated['updated_by'] = auth()->id();

            $product = Product::createWithLog($validated);

            Log::info('Product created successfully', [
                'product_id' => $product->id,
                'name' => $product->name
            ]);

            return redirect()->route('catalog.index')
                ->with('success', 'Товар успешно создан');
        } catch (Exception $e) {
            Log::error('Error creating product', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            return back()->withInput()
                ->with('error', 'Ошибка при создании товара: ' . $e->getMessage());
        }
    }

    /**
     * Показывает детальную информацию о товаре
     *
     * @param string $id
     * @return View|RedirectResponse
     */
    public function show($id): View|RedirectResponse
    {
        try {
            $product = Product::findOrFail($id);

            // Загружаем связанные данные
            $product->load(['offers' => function ($query) {
                $query->with(['prices', 'attributes', 'warehouseOffers.warehouse']);
            }]);

            Log::info('Product details loaded', ['product_id' => $product->id]);

            return view('catalog::products.show', [
                'product' => $product
            ]);
        } catch (Exception $e) {
            Log::error('Error loading product details', ['error' => $e->getMessage(), 'id' => $id]);
            return back()->with('error', 'Товар не найден');
        }
    }

    /**
     * Показывает форму редактирования товара
     *
     * @param int $id
     * @return View|RedirectResponse
     */
    public function edit($id): View|RedirectResponse
    {
        try {
            // Загружаем товар с отношениями создателя и редактора
            $product = Product::with(['creator', 'editor'])->findOrFail($id);

            // Получаем все категории для селекта
            $categories = CatalogCategory::orderBy('name')->get();

            Log::info('Product edit form loaded', [
                'product_id' => $product->id,
                'categories_count' => $categories->count()
            ]);

            return view('catalog::products.edit', [
                'product' => $product,
                'categories' => $categories
            ]);
        } catch (Exception $e) {
            Log::error('Error loading edit form', [
                'error' => $e->getMessage(),
                'id' => $id
            ]);
            return back()->with('error', 'Товар не найден');
        }
    }

    /**
     * Обновляет товар
     *
     * @param UpdateProductRequest $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(UpdateProductRequest $request, int $id): RedirectResponse
    {
        try {
            Log::info('=== UPDATE PRODUCT STARTED ===', [
                'id' => $id,
                'request_method' => $request->method(),
                'request_all' => $request->all(),
                'request_headers' => $request->headers->all(),
                'route_params' => $request->route()->parameters()
            ]);

            $product = Product::findOrFail($id);

            Log::info('Product found for update', [
                'product_id' => $product->id,
                'product_product_id' => $product->product_id,
                'product_name' => $product->name,
                'current_category_id' => $product->category_id
            ]);

            $validated = $request->validated();

            Log::info('Request validated successfully', [
                'validated_data' => $validated,
                'category_id_in_validated' => $validated['category_id'] ?? 'NOT_PRESENT'
            ]);

            // Добавляем информацию об обновителе
            $validated['updated_by'] = auth()->id();

            Log::info('Attempting to update product', [
                'product_id' => $product->id,
                'update_data' => $validated
            ]);

            $result = $product->updateWithLog($validated);

            Log::info('Update result', ['success' => $result]);

            Log::info('Product updated successfully', [
                'product_id' => $product->id,
                'name' => $product->name,
                'new_category_id' => $product->category_id,
                'updated_values' => $product->getChanges()
            ]);

            return redirect()->route('catalog.products.edit', $id)
                ->with('success', 'Товар успешно обновлен');
        } catch (Exception $e) {
            Log::error('Error updating product', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id' => $id,
                'request' => $request->all()
            ]);
            return back()->withInput()
                ->with('error', 'Ошибка при обновлении товара: ' . $e->getMessage());
        }
    }

    /**
     * Удаляет товар
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $product = Product::findOrFail($id);
            $product->deleteWithLog();

            Log::info('Product deleted successfully', ['product_id' => $product->id]);

            return redirect()->route('catalog.index')
                ->with('success', 'Товар успешно удален');
        } catch (Exception $e) {
            Log::error('Error deleting product', ['error' => $e->getMessage(), 'id' => $id]);
            return back()->with('error', 'Ошибка при удалении товара: ' . $e->getMessage());
        }
    }

    /**
     * Показывает список предложений товара
     *
     * @param Request $request
     * @param string $productId
     * @return View|RedirectResponse
     */
    public function offers(Request $request, $productId): View|RedirectResponse
    {
        try {
            $product = Product::findOrFail($productId);

            $search = $request->input('search', '');
            $perPage = $request->input('per_page', 25);

            $query = $product->offers();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('articul_supplier', 'LIKE', "%{$search}%")
                      ->orWhere('offer_id', 'LIKE', "%{$search}%");
                });
            }

            $offers = $query->paginate($perPage);

            Log::info('Product offers loaded', ['product_id' => $productId, 'total_offers' => $offers->total()]);

            return view('catalog::offers.index', [
                'product' => $product,
                'offers' => $offers,
                'search' => $search,
                'perPage' => $perPage
            ]);
        } catch (Exception $e) {
            Log::error('Error loading product offers', [
                'error' => $e->getMessage(),
                'product_id' => $productId
            ]);
            return back()->with('error', 'Произошла ошибка при загрузке предложений');
        }
    }

    /**
     * Показывает список складов
     *
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function warehouses(Request $request): View|RedirectResponse
    {
        try {
            $search = $request->input('search', '');
            $perPage = $request->input('per_page', 25);

            $query = CatalogWarehouse::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('address', 'LIKE', "%{$search}%")
                      ->orWhere('phone', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
                });
            }

            $warehouses = $query->orderBy('address')->paginate($perPage);

            Log::info('Warehouses list loaded', ['total_warehouses' => $warehouses->total()]);

            return view('catalog::warehouses.index', [
                'warehouses' => $warehouses,
                'search' => $search,
                'perPage' => $perPage,
                'totalWarehouses' => CatalogWarehouse::count(),
                'totalQuantity' => CatalogWarehouse::with('warehouseOffers')->get()->sum('getTotalQuantity')
            ]);
        } catch (Exception $e) {
            Log::error('Error loading warehouses list', ['error' => $e->getMessage()]);
            return back()->with('error', 'Произошла ошибка при загрузке списка складов');
        }
    }

    /**
     * Показывает статистику каталога
     *
     * @return View|RedirectResponse
     */
    public function statistics(): View|RedirectResponse
    {
        try {
            $totalProducts = Product::count();
            $totalOffers = CatalogProductOffer::count();
            $totalWarehouses = CatalogWarehouse::count();

            // Получаем товары с наибольшим количеством предложений
            $topProducts = Product::withCount('offers')
                ->orderBy('offers_count', 'desc')
                ->limit(10)
                ->get();

            // Получаем склады с наибольшим количеством товаров
            $topWarehouses = CatalogWarehouse::withCount('warehouseOffers')
                ->orderBy('warehouse_offers_count', 'desc')
                ->limit(10)
                ->get();

            Log::info('Catalog statistics loaded', [
                'total_products' => $totalProducts,
                'total_offers' => $totalOffers,
                'total_warehouses' => $totalWarehouses
            ]);

            return view('catalog::statistics', [
                'totalProducts' => $totalProducts,
                'totalOffers' => $totalOffers,
                'totalWarehouses' => $totalWarehouses,
                'topProducts' => $topProducts,
                'topWarehouses' => $topWarehouses
            ]);
        } catch (Exception $e) {
            Log::error('Error loading catalog statistics', ['error' => $e->getMessage()]);
            return back()->with('error', 'Произошла ошибка при загрузке статистики');
        }
    }
}
