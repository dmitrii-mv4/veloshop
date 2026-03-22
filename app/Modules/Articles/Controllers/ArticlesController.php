<?php

namespace App\Modules\Articles\Controllers;

use App\Modules\Articles\Models\Articles;
use App\Modules\Articles\Models\Category;
use App\Modules\Articles\Requests\StoreArticlesRequest;
use App\Modules\Articles\Requests\UpdateArticlesRequest;
use App\Modules\Articles\Services\ArticlesImageService;
use App\Core\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ArticlesController extends Controller
{
    protected ArticlesImageService $imageService;

    public function __construct(ArticlesImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Отображение списка активных статей.
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $perPage = $request->get('per_page', 10);

        $articlesQuery = Articles::with(['author', 'updater', 'categories']);

        if ($search) {
            $articlesQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $articles = $articlesQuery->orderBy($sortBy, $sortOrder)->paginate($perPage);

        // Статистика для отображения в шапке
        $totalArticles = Articles::count();
        $trashedArticles = Articles::onlyTrashed()->count();
        $totalCategories = Category::count();

        return view('articles::index', compact('articles', 'search', 'sortBy', 'sortOrder', 'perPage', 'totalArticles', 'trashedArticles', 'totalCategories'));
    }

    /**
     * Форма создания статей.
     */
    public function create()
    {
        $categories = Category::orderBy('title')->get();
        return view('articles::create', compact('categories'));
    }

    /**
     * Сохранение новой статьи.
     */
    public function store(StoreArticlesRequest $request)
    {
        $validated = $request->validated();

        // Обработка изображения
        if ($request->hasFile('image')) {
            $path = $this->imageService->upload($request->file('image'));
            $validated['image'] = $path;
        }

        $articles = Articles::create($validated);

        if ($request->has('categories')) {
            $articles->categories()->sync($request->input('categories'));
        }

        Log::info('Articles: создана новая запись', ['articles_id' => $articles->id, 'user_id' => auth()->id()]);

        return redirect()->route('admin.articles.index')->with('success', 'Статья успешно создана.');
    }

    /**
     * Форма редактирования статьи.
     */
    public function edit(Articles $articles)
    {
        $categories = Category::orderBy('title')->get();
        return view('articles::edit', compact('articles', 'categories'));
    }

    /**
     * Обновление статьи.
     */
    public function update(UpdateArticlesRequest $request, Articles $articles)
    {
        $validated = $request->validated();

        // Обработка изображения: если загружен новый файл, удаляем старый и загружаем новый
        if ($request->hasFile('image')) {
            $path = $this->imageService->upload($request->file('image'), $articles);
            $validated['image'] = $path;
        }

        $articles->update($validated);

        if ($request->has('categories')) {
            $articles->categories()->sync($request->input('categories'));
        } else {
            $articles->categories()->sync([]); // открепить все, если ничего не выбрано
        }

        Log::info('Articles: запись обновлена', ['articles_id' => $articles->id, 'user_id' => auth()->id()]);

        return redirect()->route('admin.articles.index')->with('success', 'Статья успешно обновлена.');
    }

    /**
     * Мягкое удаление (в корзину).
     */
    public function destroy(Articles $articles)
    {
        $articles->delete();

        return redirect()->route('admin.articles.index')->with('success', 'Статья перемещена в корзину.');
    }

    /**
     * Отображение корзины.
     */
    public function trash(Request $request)
    {
        $search = $request->get('search', '');
        $perPage = $request->get('per_page', 10);

        $trashedQuery = Articles::onlyTrashed()->with('author');

        if ($search) {
            $trashedQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $articles = $trashedQuery->orderBy('deleted_at', 'desc')->paginate($perPage);
        $trashedCount = Articles::onlyTrashed()->count();

        return view('articles::trash', compact('articles', 'search', 'perPage', 'trashedCount'));
    }

    /**
     * Восстановление статьи из корзины.
     */
    public function restore($id)
    {
        $articles = Articles::onlyTrashed()->findOrFail($id);
        $articles->restore();

        return redirect()->route('admin.articles.trash.index')->with('success', 'Статья восстановлена.');
    }

    /**
     * Полное удаление одной записи.
     */
    public function forceDelete($id)
    {
        $articles = Articles::onlyTrashed()->findOrFail($id);

        // Удаляем файл изображения перед полным удалением записи
        if ($articles->image) {
            $this->imageService->delete($articles);
        }

        $articles->forceDelete();

        return redirect()->route('admin.articles.trash.index')->with('success', 'Статья удалена навсегда.');
    }

    /**
     * Очистка всей корзины (удаление всех записей навсегда).
     */
    public function emptyTrash()
    {
        $trashed = Articles::onlyTrashed()->get();
        $count = $trashed->count();

        foreach ($trashed as $articles) {
            if ($articles->image) {
                $this->imageService->delete($articles);
            }
            $articles->forceDelete();
        }

        Log::info('Articles: очищена корзина', ['deleted_count' => $count, 'user_id' => auth()->id()]);

        return redirect()->route('admin.articles.trash.index')->with('success', "Корзина очищена. Удалено записей: {$count}.");
    }
}