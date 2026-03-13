<?php

namespace App\Modules\Stock\Controllers;

use App\Modules\Stock\Models\Category;
use App\Modules\Stock\Requests\StoreCategoryRequest;
use App\Modules\Stock\Requests\UpdateCategoryRequest;
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

        return view('stock::categories.index', compact('categories', 'search'));
    }

    public function create()
    {
        return view('stock::categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();
        Category::create($validated);

        Log::info('stock Category: создана', $validated);

        return redirect()->route('admin.stock.categories.index')
            ->with('success', 'Категория успешно создана.');
    }

    public function edit(Category $category)
    {
        return view('stock::categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $validated = $request->validated();
        $category->update($validated);

        Log::info('Stock Category: обновлена', ['id' => $category->id] + $validated);

        return redirect()->route('admin.stock.categories.index')
            ->with('success', 'Категория успешно обновлена.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.stock.categories.index')
            ->with('success', 'Категория удалена.');
    }
}