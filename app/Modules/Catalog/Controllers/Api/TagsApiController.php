<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Modules\Catalog\Models\Tag;
use App\Modules\Catalog\Resources\TagCollection;
use App\Modules\Catalog\Resources\TagResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * API контроллер для управления тегами
 * Предоставляет методы для работы с тегами через API
 */
class TagsApiController
{
    /**
     * Получить список всех тегов
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->get('search', '');
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $perPage = $request->get('per_page', 25);

            $query = Tag::query();

            // Поиск по названию или слагу
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            }

            // Сортировка
            $validSortColumns = ['name', 'slug', 'created_at', 'updated_at'];
            $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'created_at';
            $sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? $sortOrder : 'desc';

            $query->orderBy($sortBy, $sortOrder);

            // Пагинация или получение всех записей
            if ($perPage === 'all') {
                $tags = $query->get();
                $result = [
                    'data' => TagCollection::make($tags),
                    'meta' => [
                        'total' => $tags->count(),
                        'per_page' => 'all',
                    ],
                ];
            } else {
                $perPage = (int) $perPage;
                $tags = $query->paginate($perPage);
                $result = [
                    'data' => TagCollection::make($tags->items()),
                    'meta' => [
                        'total' => $tags->total(),
                        'per_page' => $tags->perPage(),
                        'current_page' => $tags->currentPage(),
                        'last_page' => $tags->lastPage(),
                        'from' => $tags->firstItem(),
                        'to' => $tags->lastItem(),
                    ],
                ];
            }

            Log::info('API: Получен список тегов', [
                'total' => $result['meta']['total'],
                'search' => $search,
            ]);

            return response()->json($result);
        } catch (Exception $e) {
            Log::error('API: Ошибка при получении списка тегов', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Ошибка при получении списка тегов',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Получить конкретный тег по ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $tag = Tag::findOrFail($id);

            Log::info('API: Получен тег', ['tag_id' => $id]);

            return response()->json([
                'data' => new TagResource($tag),
            ]);
        } catch (Exception $e) {
            Log::error('API: Ошибка при получении тега', [
                'tag_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Тег не найден',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Получить список тегов по их ID
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listByIds(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return response()->json([
                    'data' => [],
                    'meta' => ['total' => 0],
                ]);
            }

            $tags = Tag::whereIn('id', $ids)->get();

            Log::info('API: Получены теги по ID', [
                'requested_ids' => $ids,
                'found_count' => $tags->count(),
            ]);

            return response()->json([
                'data' => TagCollection::make($tags),
                'meta' => ['total' => $tags->count()],
            ]);
        } catch (Exception $e) {
            Log::error('API: Ошибка при получении тегов по ID', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Ошибка при получении тегов',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
