<?php

namespace App\Modules\News\Controllers\Api;

use App\Core\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Modules\News\Models\News;
use App\Modules\News\Models\Category;
use Illuminate\Support\Facades\Log;

/**
 * API-контроллер для модуля "Новости"
 */
class ApiController extends Controller
{
    /**
     * Возвращает дерево категорий с привязанными новостями,
     * включая виртуальную категорию для новостей без категорий.
     *
     * Формат ответа:
     * {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Категория 1",
     *       "slug": "kategoriya-1",
     *       "description": "...",
     *       "news": [...]
     *     },
     *     {
     *       "id": null,
     *       "title": "Без категории",
     *       "slug": "uncategorized",
     *       "description": null,
     *       "news": [
     *         {
     *           "id": 5,
     *           "title": "Новость без категории",
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
            // 1. Загружаем категории вместе с новостями (только не удалённые новости)
            $categories = Category::with(['news' => function ($query) {
                $query->whereNull('deleted_at')->orderBy('created_at', 'desc');
            }])->get();

            // 2. Загружаем все не удалённые новости, которые НЕ принадлежат ни одной категории
            $uncategorizedNews = News::whereNull('deleted_at')
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
                    'news'        => $category->news->map(function ($news) {
                        return $this->formatNewsItem($news);
                    }),
                ];
            })->toArray();

            // 4. Если есть новости без категорий, добавляем виртуальную категорию
            if ($uncategorizedNews->isNotEmpty()) {
                $tree[] = [
                    'id'          => null,
                    'title'       => 'Без категории',
                    'slug'        => 'uncategorized',
                    'description' => null,
                    'news'        => $uncategorizedNews->map(function ($news) {
                        return $this->formatNewsItem($news);
                    }),
                ];
            }

            Log::info('News API: успешный запрос дерева категорий', [
                'count_categories' => count($tree),
                'uncategorized_news_count' => $uncategorizedNews->count()
            ]);

            return response()->json([
                'data' => $tree
            ]);
        } catch (\Exception $e) {
            Log::error('News API: ошибка при получении дерева категорий', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Internal Server Error'
            ], 500);
        }
    }

    /**
     * Возвращает список всех категорий (без вложенных новостей).
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

            Log::info('News API: успешный запрос списка категорий', ['count' => $categories->count()]);

            return response()->json([
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            Log::error('News API: ошибка при получении списка категорий', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Internal Server Error'
            ], 500);
        }
    }

    /**
     * Форматирует элемент новости для единообразного вывода.
     *
     * @param News $news
     * @return array
     */
    private function formatNewsItem(News $news): array
    {
        return [
            'id'         => $news->id,
            'title'      => $news->title,
            'slug'       => $news->slug,
            'excerpt'    => $news->excerpt,
            'image'      => $news->image,
            'created_at' => $news->created_at->toIso8601String(),
        ];
    }
}