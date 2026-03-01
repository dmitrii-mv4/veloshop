<?php

namespace App\Modules\News\Controllers;

use App\Modules\News\Models\Category;
use App\Modules\News\Requests\StoreCategoryRequest;
use App\Modules\News\Requests\UpdateCategoryRequest;
use App\Core\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $categories = Category::query()
            ->when($search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('news::categories.index', compact('categories', 'search'));
    }

    public function create()
    {
        return view('news::categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();
        Category::create($validated);

        Log::info('News Category: создана', $validated);

        return redirect()->route('admin.news.categories.index')
            ->with('success', 'Категория успешно создана.');
    }

    public function edit(Category $category)
    {
        return view('news::categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $validated = $request->validated();
        $category->update($validated);

        Log::info('News Category: обновлена', ['id' => $category->id] + $validated);

        return redirect()->route('admin.news.categories.index')
            ->with('success', 'Категория успешно обновлена.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.news.categories.index')
            ->with('success', 'Категория удалена.');
    }
}