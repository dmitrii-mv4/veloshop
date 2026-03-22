<?php

namespace App\Modules\Articles\Controllers\Api;

use App\Core\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Modules\Articles\Models\Articles;
use App\Modules\Articles\Models\Category;
use Illuminate\Support\Facades\Log;

/**
 * API-контроллер для модуля "Статьи"
 */
class ApiController extends Controller
{
    /**
     * Возвращает дерево категорий с привязанными сатьями,
     * включая виртуальную категорию для статей без категорий.
     *
     * Формат ответа:
     * {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Категория 1",
     *       "slug": "kategoriya-1",
     *       "description": "...",
     *       "articles": [...]
     *     },
     *     {
     *       "id": null,
     *       "title": "Без категории",
     *       "slug": "uncategorized",
     *       "description": null,
     *       "articles": [
     *         {
     *           "id": 5,
     *           "title": "Статья без категории",
     *           "slug": "article-bez-kategorii",
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
            // 1. Загружаем категории вместе с статьями (только не удалённые статьи)
            $categories = Category::with(['articles' => function ($query) {
                $query->whereNull('deleted_at')->orderBy('created_at', 'desc');
            }])->get();

            // 2. Загружаем все не удалённые статьи, которые НЕ принадлежат ни одной категории
            $uncategorizedArticles = Articles::whereNull('deleted_at')
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
                    'articles'        => $category->articles->map(function ($articles) {
                        return $this->formatArticlesItem($articles);
                    }),
                ];
            })->toArray();

            // 4. Если есть статьи без категорий, добавляем виртуальную категорию
            if ($uncategorizedArticles->isNotEmpty()) {
                $tree[] = [
                    'id'          => null,
                    'title'       => 'Без категории',
                    'slug'        => 'uncategorized',
                    'description' => null,
                    'articles'        => $uncategorizedArticles->map(function ($articles) {
                        return $this->formatArticlesItem($articles);
                    }),
                ];
            }

            Log::info('Articles API: успешный запрос дерева категорий', [
                'count_categories' => count($tree),
                'uncategorized_articles_count' => $uncategorizedArticles->count()
            ]);

            return response()->json([
                'data' => $tree
            ]);
        } catch (\Exception $e) {
            Log::error('Articles API: ошибка при получении дерева категорий', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Internal Server Error'
            ], 500);
        }
    }

    /**
     * Возвращает список всех категорий (без вложенных статей).
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

            Log::info('Articles API: успешный запрос списка категорий', ['count' => $categories->count()]);

            return response()->json([
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            Log::error('Articles API: ошибка при получении списка категорий', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Internal Server Error'
            ], 500);
        }
    }

    /**
     * Форматирует элемент статьи для единообразного вывода.
     *
     * @param Articles $articles
     * @return array
     */
    private function formatArticlesItem(Articles $articles): array
    {
        return [
            'id'         => $articles->id,
            'title'      => $articles->title,
            'slug'       => $articles->slug,
            'excerpt'    => $articles->excerpt,
            'image'      => $articles->image,
            'created_at' => $articles->created_at->toIso8601String(),
        ];
    }
}