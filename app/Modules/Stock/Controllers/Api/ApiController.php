<?php

namespace App\Modules\Stock\Controllers\Api;

use App\Core\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Modules\Stock\Models\Stock;
use App\Modules\Stock\Models\Category;
use Illuminate\Support\Facades\Log;

/**
 * API-контроллер для модуля "Акции"
 */
class ApiController extends Controller
{
    /**
     * Возвращает дерево категорий с привязанными акциям,
     * включая виртуальную категорию для акций без категорий.
     *
     * Формат ответа:
     * {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Категория 1",
     *       "slug": "kategoriya-1",
     *       "description": "...",
     *       "stock": [...]
     *     },
     *     {
     *       "id": null,
     *       "title": "Без категории",
     *       "slug": "uncategorized",
     *       "description": null,
     *       "stock": [
     *         {
     *           "id": 5,
     *           "title": "Акции без категории",
     *           "slug": "novost-bez-kategorii",
     *           "excerpt": "...",
     *           "image": null,
     *           "created_at": "..."
     *         }
     *       ]
     *     }
     *   ]
     * }
     *
     * @return JsonResponse
     */
    public function getTree(): JsonResponse
    {
        try {
            // 1. Загружаем категории вместе с акциями (только не удалённые акции)
            $categories = Category::with(['stock' => function ($query) {
                $query->whereNull('deleted_at')->orderBy('created_at', 'desc');
            }])->get();

            // 2. Загружаем все не удалённые акции, которые НЕ принадлежат ни одной категории
            $uncategorizedStock = Stock::whereNull('deleted_at')
                ->whereDoesntHave('categories')
                ->orderBy('created_at', 'desc')
                ->get();

            // 3. Формируем основное дерево из реальных категорий
            $tree = $categories->map(function ($category) {
                return [
                    'id'          => $category->id,
                    'title'       => $category->title,
                    'slug'        => $category->slug,
                    'description' => $category->description,
                    'stock'        => $category->stock->map(function ($stock) {
                        return $this->formatStockItem($stock);
                    }),
                ];
            })->toArray();

            // 4. Если есть акции без категорий, добавляем виртуальную категорию
            if ($uncategorizedStock->isNotEmpty()) {
                $tree[] = [
                    'id'          => null,
                    'title'       => 'Без категории',
                    'slug'        => 'uncategorized',
                    'description' => null,
                    'stock'        => $uncategorizedStock->map(function ($stock) {
                        return $this->formatStockItem($stock);
                    }),
                ];
            }

            Log::info('Stock API: успешный запрос дерева категорий', [
                'count_categories' => count($tree),
                'uncategorized_stock_count' => $uncategorizedStock->count()
            ]);

            return response()->json([
                'data' => $tree
            ]);
        } catch (\Exception $e) {
            Log::error('Stock API: ошибка при получении дерева категорий', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Internal Server Error'
            ], 500);
        }
    }

    /**
     * Возвращает список всех категорий (без вложенных акций).
     *
     * Формат ответа:
     * {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Категория 1",
     *       "slug": "kategoriya-1",
     *       "description": "..."
     *     }
     *   ]
     * }
     *
     * @return JsonResponse
     */
    public function getCategories(): JsonResponse
    {
        try {
            $categories = Category::all(['id', 'title', 'slug', 'description']);

            Log::info('Stock API: успешный запрос списка категорий', ['count' => $categories->count()]);

            return response()->json([
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            Log::error('Stock API: ошибка при получении списка категорий', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Internal Server Error'
            ], 500);
        }
    }

    /**
     * Форматирует элемент акции для единообразного вывода.
     *
     * @param Stock $stock
     * @return array
     */
    private function formatStockItem(Stock $stock): array
    {
        return [
            'id'         => $stock->id,
            'title'      => $stock->title,
            'slug'       => $stock->slug,
            'excerpt'    => $stock->excerpt,
            'image'      => $stock->image,
            'created_at' => $stock->created_at->toIso8601String(),
        ];
    }
}