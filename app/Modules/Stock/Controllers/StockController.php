<?php

namespace App\Modules\Stock\Controllers;

use App\Modules\Stock\Models\Stock;
use App\Modules\Stock\Models\Category;
use App\Modules\Stock\Requests\StoreStockRequest;
use App\Modules\Stock\Requests\UpdateStockRequest;
use App\Modules\Stock\Services\StockImageService;
use App\Core\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StockController extends Controller
{
    protected StockImageService $imageService;

    public function __construct(StockImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Отображение списка активных акций.
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $perPage = $request->get('per_page', 10);

        $stockQuery = Stock::with(['author', 'updater', 'categories']);

        if ($search) {
            $stockQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $stock = $stockQuery->orderBy($sortBy, $sortOrder)->paginate($perPage);

        // Статистика для отображения в шапке
        $totalStock = Stock::count();
        $trashedStock = Stock::onlyTrashed()->count();
        $totalCategories = Category::count();

        return view('stock::index', compact('stock', 'search', 'sortBy', 'sortOrder', 'perPage', 'totalStock', 'trashedStock', 'totalCategories'));
    }

    /**
     * Форма создания акции.
     */
    public function create()
    {
        $categories = Category::orderBy('title')->get();
        return view('stock::create', compact('categories'));
    }

    /**
     * Сохранение новой акции.
     */
    public function store(StoreStockRequest $request)
    {
        $validated = $request->validated();

        // Обработка изображения
        if ($request->hasFile('image')) {
            $path = $this->imageService->upload($request->file('image'));
            $validated['image'] = $path;
        }

        $stock = Stock::create($validated);

        if ($request->has('categories')) {
            $stock->categories()->sync($request->input('categories'));
        }

        Log::info('Stock: создана новая запись', ['stock_id' => $stock->id, 'user_id' => auth()->id()]);

        return redirect()->route('admin.stock.index')->with('success', 'Акция успешно создана.');
    }

    /**
     * Форма редактирования акции.
     */
    public function edit(Stock $stock)
    {
        $categories = Category::orderBy('title')->get();
        return view('stock::edit', compact('stock', 'categories'));
    }

    /**
     * Обновление акции.
     */
    public function update(UpdateStockRequest $request, Stock $stock)
    {
        $validated = $request->validated();

        // Обработка изображения: если загружен новый файл, удаляем старый и загружаем новый
        if ($request->hasFile('image')) {
            $path = $this->imageService->upload($request->file('image'), $stock);
            $validated['image'] = $path;
        }

        $stock->update($validated);

        if ($request->has('categories')) {
            $stock->categories()->sync($request->input('categories'));
        } else {
            $stock->categories()->sync([]); // открепить все, если ничего не выбрано
        }

        Log::info('Stock: запись обновлена', ['stock_id' => $stock->id, 'user_id' => auth()->id()]);

        return redirect()->route('admin.stock.index')->with('success', 'Акция успешно обновлена.');
    }

    /**
     * Мягкое удаление (в корзину).
     */
    public function destroy(Stock $stock)
    {
        $stock->delete();

        return redirect()->route('admin.stock.index')->with('success', 'Акция перемещена в корзину.');
    }

    /**
     * Отображение корзины.
     */
    public function trash(Request $request)
    {
        $search = $request->get('search', '');
        $perPage = $request->get('per_page', 10);

        $trashedQuery = Stock::onlyTrashed()->with('author');

        if ($search) {
            $trashedQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $stock = $trashedQuery->orderBy('deleted_at', 'desc')->paginate($perPage);
        $trashedCount = Stock::onlyTrashed()->count();

        return view('stock::trash', compact('stock', 'search', 'perPage', 'trashedCount'));
    }

    /**
     * Восстановление акции из корзины.
     */
    public function restore($id)
    {
        $stock = Stock::onlyTrashed()->findOrFail($id);
        $stock->restore();

        return redirect()->route('admin.stock.trash.index')->with('success', 'Акция восстановлена.');
    }

    /**
     * Полное удаление одной записи.
     */
    public function forceDelete($id)
    {
        $stock = Stock::onlyTrashed()->findOrFail($id);

        // Удаляем файл изображения перед полным удалением записи
        if ($stock->image) {
            $this->imageService->delete($stock);
        }

        $stock->forceDelete();

        return redirect()->route('admin.stock.trash.index')->with('success', 'Акция удалена навсегда.');
    }

    /**
     * Очистка всей корзины (удаление всех записей навсегда).
     */
    public function emptyTrash()
    {
        $trashed = Stock::onlyTrashed()->get();
        $count = $trashed->count();

        foreach ($trashed as $stock) {
            if ($stock->image) {
                $this->imageService->delete($stock);
            }
            $stock->forceDelete();
        }

        Log::info('Stock: очищена корзина', ['deleted_count' => $count, 'user_id' => auth()->id()]);

        return redirect()->route('admin.stock.trash.index')->with('success', "Корзина очищена. Удалено записей: {$count}.");
    }
}