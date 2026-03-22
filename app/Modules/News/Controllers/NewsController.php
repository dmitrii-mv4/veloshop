<?php

namespace App\Modules\News\Controllers;

use App\Modules\News\Models\News;
use App\Modules\News\Models\Category;
use App\Modules\News\Requests\StoreNewsRequest;
use App\Modules\News\Requests\UpdateNewsRequest;
use App\Modules\News\Services\NewsImageService;
use App\Core\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NewsController extends Controller
{
    protected NewsImageService $imageService;

    public function __construct(NewsImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Отображение списка активных новостей.
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $perPage = $request->get('per_page', 10);

        $newsQuery = News::with(['author', 'updater', 'categories']);

        if ($search) {
            $newsQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $news = $newsQuery->orderBy($sortBy, $sortOrder)->paginate($perPage);

        // Статистика для отображения в шапке
        $totalNews = News::count();
        $trashedNews = News::onlyTrashed()->count();
        $totalCategories = Category::count();

        return view('news::index', compact('news', 'search', 'sortBy', 'sortOrder', 'perPage', 'totalNews', 'trashedNews', 'totalCategories'));
    }

    /**
     * Форма создания новости.
     */
    public function create()
    {
        $categories = Category::orderBy('title')->get();
        return view('news::create', compact('categories'));
    }

    /**
     * Сохранение новой новости.
     */
    public function store(StoreNewsRequest $request)
    {
        $validated = $request->validated();

        // Обработка изображения
        if ($request->hasFile('image')) {
            $path = $this->imageService->upload($request->file('image'));
            $validated['image'] = $path;
        }

        $news = News::create($validated);

        if ($request->has('categories')) {
            $news->categories()->sync($request->input('categories'));
        }

        Log::info('News: создана новая запись', ['news_id' => $news->id, 'user_id' => auth()->id()]);

        return redirect()->route('admin.news.index')->with('success', 'Новость успешно создана.');
    }

    /**
     * Форма редактирования новости.
     */
    public function edit(News $news)
    {
        $categories = Category::orderBy('title')->get();
        return view('news::edit', compact('news', 'categories'));
    }

    /**
     * Обновление новости.
     */
    public function update(UpdateNewsRequest $request, News $news)
    {
        $validated = $request->validated();

        // Обработка изображения: если загружен новый файл, удаляем старый и загружаем новый
        if ($request->hasFile('image')) {
            $path = $this->imageService->upload($request->file('image'), $news);
            $validated['image'] = $path;
        }

        $news->update($validated);

        if ($request->has('categories')) {
            $news->categories()->sync($request->input('categories'));
        } else {
            $news->categories()->sync([]); // открепить все, если ничего не выбрано
        }

        Log::info('News: запись обновлена', ['news_id' => $news->id, 'user_id' => auth()->id()]);

        return redirect()->route('admin.news.index')->with('success', 'Новость успешно обновлена.');
    }

    /**
     * Мягкое удаление (в корзину).
     */
    public function destroy(News $news)
    {
        $news->delete();

        return redirect()->route('admin.news.index')->with('success', 'Новость перемещена в корзину.');
    }

    /**
     * Отображение корзины.
     */
    public function trash(Request $request)
    {
        $search = $request->get('search', '');
        $perPage = $request->get('per_page', 10);

        $trashedQuery = News::onlyTrashed()->with('author');

        if ($search) {
            $trashedQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $news = $trashedQuery->orderBy('deleted_at', 'desc')->paginate($perPage);
        $trashedCount = News::onlyTrashed()->count();

        return view('news::trash', compact('news', 'search', 'perPage', 'trashedCount'));
    }

    /**
     * Восстановление новости из корзины.
     */
    public function restore($id)
    {
        $news = News::onlyTrashed()->findOrFail($id);
        $news->restore();

        return redirect()->route('admin.news.trash.index')->with('success', 'Новость восстановлена.');
    }

    /**
     * Полное удаление одной записи.
     */
    public function forceDelete($id)
    {
        $news = News::onlyTrashed()->findOrFail($id);

        // Удаляем файл изображения перед полным удалением записи
        if ($news->image) {
            $this->imageService->delete($news);
        }

        $news->forceDelete();

        return redirect()->route('admin.news.trash.index')->with('success', 'Новость удалена навсегда.');
    }

    /**
     * Очистка всей корзины (удаление всех записей навсегда).
     */
    public function emptyTrash()
    {
        $trashed = News::onlyTrashed()->get();
        $count = $trashed->count();

        foreach ($trashed as $news) {
            if ($news->image) {
                $this->imageService->delete($news);
            }
            $news->forceDelete();
        }

        Log::info('News: очищена корзина', ['deleted_count' => $count, 'user_id' => auth()->id()]);

        return redirect()->route('admin.news.trash.index')->with('success', "Корзина очищена. Удалено записей: {$count}.");
    }
}