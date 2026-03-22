<?php

namespace App\Modules\Page\Controllers\Api;

use App\Core\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Modules\Page\Models\Page;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    /**
     * Получение раздельных данных страниц
     * Возвращает все страницы в плоском списке
     *
     * @return JsonResponse
     */
    public function getSeparate(): JsonResponse
    {
        try {
            Log::info('API Page: получение раздельных данных страниц');
            
            $pages = Page::all();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'pages' => $pages
                ],
                'meta' => [
                    'pages_count' => $pages->count(),
                    'published_count' => $pages->where('status', 'published')->count(),
                    'draft_count' => $pages->where('status', 'draft')->count(),
                    'private_count' => $pages->where('status', 'private')->count(),
                    'timestamp' => now()->toISOString()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Page: ошибка получения раздельных данных', [
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении данных страниц',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Получение древовидной структуры страниц
     * Возвращает страницы с вложенными дочерними страницами в виде дерева
     *
     * @return JsonResponse
     */
    public function getTree(): JsonResponse
    {
        try {
            Log::info('API Page: получение древовидной структуры страниц');
            
            // Получаем все страницы с дочерними элементами и родителями
            $pages = Page::with(['children' => function($query) {
                $query->where('status', 'published')
                      ->orderBy('order', 'asc');
            }, 'parent'])->where('parent_id', null) // Начинаем с корневых страниц
               ->orderBy('order', 'asc')
               ->get();
            
            // Если нет страниц
            if ($pages->isEmpty()) {
                Log::info('API Page: страницы не найдены');
                return response()->json([
                    'success' => true,
                    'data' => [
                        'pages' => []
                    ]
                ]);
            }
            
            // Структурируем данные в древовидный формат
            $treeData = $this->buildPagesTree($pages);
            
            Log::info('API Page: древовидная структура успешно построена', [
                'root_pages_count' => $pages->count(),
                'total_pages' => $this->countTotalPages($pages)
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $treeData
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Page: ошибка получения древовидной структуры', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при построении древовидной структуры страниц',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Построение древовидной структуры страниц
     *
     * @param \Illuminate\Database\Eloquent\Collection $pages
     * @param string $statusFilter Статус для фильтрации (null - все статусы)
     * @return array
     */
    private function buildPagesTree($pages, $statusFilter = 'published'): array
    {
        $structuredPages = $pages->map(function($page) use ($statusFilter) {
            // Если фильтр по статусу и страница не соответствует - пропускаем
            if ($statusFilter && $page->status !== $statusFilter && $page->status !== 'published') {
                return null;
            }
            
            // Рекурсивно строим дерево для дочерних страниц
            $childrenTree = $this->buildPagesTree($page->children, $statusFilter);
            
            $pageData = [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'content' => $page->content,
                'excerpt' => $page->excerpt,
                'status' => $page->status,
                'meta' => [
                    'title' => $page->meta_title,
                    'description' => $page->meta_description,
                    'keywords' => $page->meta_keywords
                ],
                'published_at' => $page->published_at ? $page->published_at->toISOString() : null,
                'order' => $page->order,
                'parent_id' => $page->parent_id,
                'url' => $this->generatePageUrl($page),
                'breadcrumbs' => $this->generateBreadcrumbs($page),
                'children' => $childrenTree,
                'children_count' => count($this->flattenTree($childrenTree)),
                'created_by' => $page->created_by,
                'updated_by' => $page->updated_by,
                'created_at' => $page->created_at->toISOString(),
                'updated_at' => $page->updated_at->toISOString(),
                'deleted_at' => $page->deleted_at ? $page->deleted_at->toISOString() : null
            ];
            
            return $pageData;
        })->filter()->values()->toArray(); // Удаляем null значения и сбрасываем ключи
        
        return [
            'pages' => $structuredPages,
            'meta' => [
                'total_pages' => count($this->flattenTree($structuredPages)),
                'root_pages' => count($structuredPages),
                'status_filter' => $statusFilter,
                'timestamp' => now()->toISOString()
            ]
        ];
    }
    
    /**
     * Подсчет общего количества страниц в дереве
     *
     * @param \Illuminate\Database\Eloquent\Collection $pages
     * @return int
     */
    private function countTotalPages($pages): int
    {
        $count = 0;
        
        foreach ($pages as $page) {
            $count++; // Текущая страница
            if ($page->children->isNotEmpty()) {
                $count += $this->countTotalPages($page->children);
            }
        }
        
        return $count;
    }
    
    /**
     * Генерация URL для страницы
     *
     * @param Page $page
     * @return string
     */
    private function generatePageUrl(Page $page): string
    {
        // Если это корневая страница
        if (!$page->parent_id) {
            return '/' . $page->slug;
        }
        
        // Для вложенных страниц собираем полный путь
        $slugs = [];
        $currentPage = $page;
        
        while ($currentPage) {
            $slugs[] = $currentPage->slug;
            $currentPage = $currentPage->parent;
        }
        
        return '/' . implode('/', array_reverse($slugs));
    }
    
    /**
     * Генерация цепочки навигации (breadcrumbs) для страницы
     *
     * @param Page $page
     * @return array
     */
    private function generateBreadcrumbs(Page $page): array
    {
        $breadcrumbs = [];
        $currentPage = $page;
        
        while ($currentPage) {
            $breadcrumbs[] = [
                'id' => $currentPage->id,
                'title' => $currentPage->title,
                'slug' => $currentPage->slug,
                'url' => $this->generatePageUrl($currentPage)
            ];
            
            $currentPage = $currentPage->parent;
        }
        
        return array_reverse($breadcrumbs);
    }
    
    /**
     * Преобразование дерева в плоский список для подсчета элементов
     *
     * @param array $tree
     * @return array
     */
    private function flattenTree(array $tree): array
    {
        $flat = [];
        
        foreach ($tree as $item) {
            $flat[] = $item;
            
            if (isset($item['children']) && is_array($item['children'])) {
                $flat = array_merge($flat, $this->flattenTree($item['children']));
            }
        }
        
        return $flat;
    }
    
    /**
     * Получение опубликованных страниц в виде дерева
     * (специальный метод для фронтенда)
     *
     * @return JsonResponse
     */
    public function getPublishedTree(): JsonResponse
    {
        try {
            Log::info('API Page: получение дерева опубликованных страниц');
            
            // Получаем только опубликованные страницы с родителями
            $pages = Page::with(['children' => function($query) {
                $query->where('status', 'published')
                      ->where('published_at', '<=', now())
                      ->orderBy('order', 'asc');
            }, 'parent'])->where('parent_id', null)
               ->where('status', 'published')
               ->where('published_at', '<=', now())
               ->orderBy('order', 'asc')
               ->get();
            
            // Структурируем данные
            $treeData = $this->buildPagesTree($pages, 'published');
            
            return response()->json([
                'success' => true,
                'data' => $treeData
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Page: ошибка получения дерева опубликованных страниц', [
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении дерева опубликованных страниц'
            ], 500);
        }
    }
    
    /**
     * Альтернативный метод построения дерева без рекурсивных запросов
     * (более производительный для больших объемов данных)
     *
     * @return JsonResponse
     */
    public function getTreeOptimized(): JsonResponse
    {
        try {
            Log::info('API Page: получение оптимизированной древовидной структуры страниц');
            
            // Получаем все страницы одним запросом
            $allPages = Page::with('parent')
                ->orderBy('parent_id', 'asc')
                ->orderBy('order', 'asc')
                ->get();
            
            if ($allPages->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'pages' => []
                    ]
                ]);
            }
            
            // Группируем страницы по parent_id
            $groupedPages = [];
            foreach ($allPages as $page) {
                $parentId = $page->parent_id ?? 0;
                if (!isset($groupedPages[$parentId])) {
                    $groupedPages[$parentId] = [];
                }
                $groupedPages[$parentId][] = $page;
            }
            
            // Строим дерево начиная с корневых элементов
            $pagesTree = $this->buildTreeFromGrouped($groupedPages, 0);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'pages' => $pagesTree,
                    'meta' => [
                        'total_pages' => $allPages->count(),
                        'timestamp' => now()->toISOString()
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Page: ошибка получения оптимизированной древовидной структуры', [
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при построении оптимизированной древовидной структуры страниц'
            ], 500);
        }
    }
    
    /**
     * Построение дерева из сгруппированных страниц
     *
     * @param array $groupedPages
     * @param int $parentId
     * @return array
     */
    private function buildTreeFromGrouped(array $groupedPages, int $parentId = 0): array
    {
        $tree = [];
        
        if (isset($groupedPages[$parentId])) {
            foreach ($groupedPages[$parentId] as $page) {
                $children = $this->buildTreeFromGrouped($groupedPages, $page->id);
                
                $pageData = [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'content' => $page->content,
                    'excerpt' => $page->excerpt,
                    'status' => $page->status,
                    'meta' => [
                        'title' => $page->meta_title,
                        'description' => $page->meta_description,
                        'keywords' => $page->meta_keywords
                    ],
                    'published_at' => $page->published_at ? $page->published_at->toISOString() : null,
                    'order' => $page->order,
                    'parent_id' => $page->parent_id,
                    'url' => $this->generatePageUrl($page),
                    'breadcrumbs' => $this->generateBreadcrumbs($page),
                    'children' => $children,
                    'children_count' => count($children),
                    'created_by' => $page->created_by,
                    'updated_by' => $page->updated_by,
                    'created_at' => $page->created_at->toISOString(),
                    'updated_at' => $page->updated_at->toISOString(),
                    'deleted_at' => $page->deleted_at ? $page->deleted_at->toISOString() : null
                ];
                
                $tree[] = $pageData;
            }
        }
        
        return $tree;
    }
    
    /**
     * Получение страницы по slug
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function getBySlug(string $slug): JsonResponse
    {
        try {
            Log::info('API Page: получение страницы по slug', ['slug' => $slug]);
            
            $page = Page::with('parent')->where('slug', $slug)->first();
            
            if (!$page) {
                Log::warning('API Page: страница не найдена', ['slug' => $slug]);
                return response()->json([
                    'success' => false,
                    'message' => 'Страница не найдена'
                ], 404);
            }
            
            // Получаем дочерние страницы (если нужны)
            $children = Page::with('parent')
                          ->where('parent_id', $page->id)
                          ->where('status', 'published')
                          ->orderBy('order', 'asc')
                          ->get();
            
            // Строим дерево для дочерних страниц
            $childrenData = [];
            if ($children->isNotEmpty()) {
                $grouped = [];
                foreach ($children as $child) {
                    $parentId = $child->parent_id ?? 0;
                    if (!isset($grouped[$parentId])) {
                        $grouped[$parentId] = [];
                    }
                    $grouped[$parentId][] = $child;
                }
                $childrenData = $this->buildTreeFromGrouped($grouped, $page->id);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'page' => [
                        'id' => $page->id,
                        'title' => $page->title,
                        'slug' => $page->slug,
                        'content' => $page->content,
                        'excerpt' => $page->excerpt,
                        'status' => $page->status,
                        'meta' => [
                            'title' => $page->meta_title,
                            'description' => $page->meta_description,
                            'keywords' => $page->meta_keywords
                        ],
                        'published_at' => $page->published_at ? $page->published_at->toISOString() : null,
                        'order' => $page->order,
                        'parent_id' => $page->parent_id,
                        'url' => $this->generatePageUrl($page),
                        'breadcrumbs' => $this->generateBreadcrumbs($page),
                        'created_by' => $page->created_by,
                        'updated_by' => $page->updated_by,
                        'created_at' => $page->created_at->toISOString(),
                        'updated_at' => $page->updated_at->toISOString()
                    ],
                    'children' => $childrenData,
                    'siblings' => $this->getSiblings($page)
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Page: ошибка получения страницы по slug', [
                'slug' => $slug,
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении страницы'
            ], 500);
        }
    }
    
    /**
     * Получение страниц одного уровня (братья и сестры)
     *
     * @param Page $page
     * @return array
     */
    private function getSiblings(Page $page): array
    {
        return Page::with('parent')
                  ->where('parent_id', $page->parent_id)
                  ->where('id', '!=', $page->id)
                  ->where('status', 'published')
                  ->orderBy('order', 'asc')
                  ->get()
                  ->map(function($sibling) {
                      return [
                          'id' => $sibling->id,
                          'title' => $sibling->title,
                          'slug' => $sibling->slug,
                          'url' => $this->generatePageUrl($sibling),
                          'order' => $sibling->order,
                          'excerpt' => $sibling->excerpt
                      ];
                  })->toArray();
    }
    
    /**
     * Получение страниц по статусу
     *
     * @param string $status
     * @return JsonResponse
     */
    public function getByStatus(string $status): JsonResponse
    {
        try {
            Log::info('API Page: получение страниц по статусу', ['status' => $status]);
            
            // Валидация статуса
            $validStatuses = ['draft', 'published', 'private'];
            if (!in_array($status, $validStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Неверный статус страницы'
                ], 400);
            }
            
            $pages = Page::all()->where('status', $status);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'pages' => $pages,
                    'count' => $pages->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Page: ошибка получения страниц по статусу', [
                'status' => $status,
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении страниц по статусу'
            ], 500);
        }
    }
    
    /**
     * Поиск страниц по заголовку или содержимому
     *
     * @param string $query
     * @return JsonResponse
     */
    public function search(string $query): JsonResponse
    {
        try {
            Log::info('API Page: поиск страниц', ['query' => $query]);
            
            $pages = Page::with('parent')
                        ->where('title', 'ILIKE', "%{$query}%")
                        ->orWhere('content', 'ILIKE', "%{$query}%")
                        ->orWhere('excerpt', 'ILIKE', "%{$query}%")
                        ->where('status', 'published')
                        ->where('published_at', '<=', now())
                        ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'query' => $query,
                    'pages' => $pages->map(function($page) {
                        return [
                            'id' => $page->id,
                            'title' => $page->title,
                            'slug' => $page->slug,
                            'excerpt' => $page->excerpt,
                            'url' => $this->generatePageUrl($page),
                            'published_at' => $page->published_at
                        ];
                    }),
                    'count' => $pages->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Page: ошибка поиска страниц', [
                'query' => $query,
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при поиске страниц'
            ], 500);
        }
    }
}   