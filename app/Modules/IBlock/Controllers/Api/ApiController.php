<?php

namespace App\Modules\IBlock\Controllers\Api;

use App\Core\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Modules\IBlock\Models\IBlock;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    /**
     * Получение раздельных данных информационных блоков
     * Возвращает все информационные блоки в структурированном формате
     *
     * @return JsonResponse
     */
    public function getSeparate(): JsonResponse
    {
        try {
            Log::info('API IBlock: получение раздельных данных информационных блоков');
            
            // Получаем все информационные блоки с автором
            $iblocks = IBlock::with('author')->get();
            
            // Если нет информационных блоков
            if ($iblocks->isEmpty()) {
                Log::info('API IBlock: информационные блоки не найдены');
                return response()->json([
                    'success' => true,
                    'data' => [
                        'iblocks' => []
                    ]
                ]);
            }
            
            // Структурируем данные
            $structuredData = $this->structureIBlocksData($iblocks);
            
            Log::info('API IBlock: данные успешно получены', [
                'iblocks_count' => $iblocks->count(),
                'active_count' => $iblocks->whereNull('deleted_at')->count(),
                'deleted_count' => $iblocks->whereNotNull('deleted_at')->count()
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $structuredData
            ]);
            
        } catch (\Exception $e) {
            Log::error('API IBlock: ошибка получения раздельных данных', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении данных информационных блоков',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Структурирование данных информационных блоков
     *
     * @param \Illuminate\Database\Eloquent\Collection $iblocks
     * @return array
     */
    private function structureIBlocksData($iblocks): array
    {
        // Преобразуем информационные блоки
        $structuredIBlocks = $iblocks->map(function($iblock) {
            return [
                'id' => $iblock->id,
                'title' => $iblock->title,
                'content' => $iblock->content,
                'author' => $iblock->author ? [
                    'id' => $iblock->author->id,
                    'name' => $iblock->author->name,
                    'email' => $iblock->author->email
                ] : null,
                'author_id' => $iblock->author_id,
                'meta' => $this->extractMetaFromContent($iblock->content),
                'is_active' => is_null($iblock->deleted_at),
                'is_deleted' => !is_null($iblock->deleted_at),
                'created_at' => $iblock->created_at->toISOString(),
                'updated_at' => $iblock->updated_at->toISOString(),
                'deleted_at' => $iblock->deleted_at ? $iblock->deleted_at->toISOString() : null
            ];
        })->toArray();
        
        // Разделяем на активные и удаленные блоки
        $activeIBlocks = array_filter($structuredIBlocks, function($iblock) {
            return $iblock['is_active'];
        });
        
        $deletedIBlocks = array_filter($structuredIBlocks, function($iblock) {
            return $iblock['is_deleted'];
        });
        
        return [
            'iblocks' => $structuredIBlocks,
            // 'active_iblocks' => array_values($activeIBlocks),
            // 'deleted_iblocks' => array_values($deletedIBlocks),
            'meta' => [
                'total_iblocks' => count($structuredIBlocks),
                'active_count' => count($activeIBlocks),
                'deleted_count' => count($deletedIBlocks),
                'timestamp' => now()->toISOString()
            ]
        ];
    }
    
    /**
     * Извлечение мета-информации из содержимого
     *
     * @param string|null $content
     * @return array
     */
    private function extractMetaFromContent(?string $content): array
    {
        if (empty($content)) {
            return [
                'has_content' => false,
                'content_length' => 0,
                'has_images' => false,
                'has_links' => false
            ];
        }
        
        $contentLength = strlen($content);
        
        // Проверяем наличие изображений (img тегов)
        $hasImages = preg_match('/<img[^>]+>/i', $content) === 1;
        
        // Проверяем наличие ссылок (a тегов)
        $hasLinks = preg_match('/<a[^>]+>/i', $content) === 1;
        
        // Извлекаем первое изображение (если есть)
        $firstImage = null;
        if ($hasImages && preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches)) {
            $firstImage = $matches[1];
        }
        
        // Извлекаем первую ссылку (если есть)
        $firstLink = null;
        if ($hasLinks && preg_match('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $content, $matches)) {
            $firstLink = $matches[1];
        }
        
        // Извлекаем краткое описание (первые 200 символов без тегов)
        $plainText = strip_tags($content);
        $excerpt = mb_substr($plainText, 0, 200);
        if (mb_strlen($plainText) > 200) {
            $excerpt .= '...';
        }
        
        return [
            'has_content' => true,
            'content_length' => $contentLength,
            'character_count' => mb_strlen($plainText),
            'word_count' => str_word_count($plainText),
            'has_images' => $hasImages,
            'has_links' => $hasLinks,
            'first_image' => $firstImage,
            'first_link' => $firstLink,
            'excerpt' => $excerpt
        ];
    }
    
    /**
     * Получение информационного блока по ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getById(int $id): JsonResponse
    {
        try {
            Log::info('API IBlock: получение информационного блока по ID', ['id' => $id]);
            
            $iblock = IBlock::with('author')->find($id);
            
            if (!$iblock) {
                Log::warning('API IBlock: информационный блок не найден', ['id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Информационный блок не найден'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'iblock' => [
                        'id' => $iblock->id,
                        'title' => $iblock->title,
                        'content' => $iblock->content,
                        'author' => $iblock->author ? [
                            'id' => $iblock->author->id,
                            'name' => $iblock->author->name,
                            'email' => $iblock->author->email
                        ] : null,
                        'author_id' => $iblock->author_id,
                        'meta' => $this->extractMetaFromContent($iblock->content),
                        'is_active' => is_null($iblock->deleted_at),
                        'created_at' => $iblock->created_at->toISOString(),
                        'updated_at' => $iblock->updated_at->toISOString()
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API IBlock: ошибка получения информационного блока по ID', [
                'id' => $id,
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении информационного блока'
            ], 500);
        }
    }
    
    /**
     * Получение активных информационных блоков
     *
     * @return JsonResponse
     */
    public function getActive(): JsonResponse
    {
        try {
            Log::info('API IBlock: получение активных информационных блоков');
            
            $iblocks = IBlock::with('author')
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->get();
            
            if ($iblocks->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'iblocks' => []
                    ]
                ]);
            }
            
            $structuredIBlocks = $iblocks->map(function($iblock) {
                return [
                    'id' => $iblock->id,
                    'title' => $iblock->title,
                    'content' => $iblock->content,
                    'author' => $iblock->author ? [
                        'id' => $iblock->author->id,
                        'name' => $iblock->author->name
                    ] : null,
                    'excerpt' => $this->extractMetaFromContent($iblock->content)['excerpt'],
                    'created_at' => $iblock->created_at->toISOString(),
                    'updated_at' => $iblock->updated_at->toISOString()
                ];
            })->toArray();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'iblocks' => $structuredIBlocks,
                    'count' => count($structuredIBlocks)
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API IBlock: ошибка получения активных информационных блоков', [
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении активных информационных блоков'
            ], 500);
        }
    }
    
    /**
     * Поиск информационных блоков по заголовку или содержимому
     *
     * @param string $query
     * @return JsonResponse
     */
    public function search(string $query): JsonResponse
    {
        try {
            Log::info('API IBlock: поиск информационных блоков', ['query' => $query]);
            
            $iblocks = IBlock::with('author')
                ->where(function($q) use ($query) {
                    $q->where('title', 'ILIKE', "%{$query}%")
                      ->orWhere('content', 'ILIKE', "%{$query}%");
                })
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'query' => $query,
                    'iblocks' => $iblocks->map(function($iblock) {
                        return [
                            'id' => $iblock->id,
                            'title' => $iblock->title,
                            'content' => $iblock->content,
                            'author' => $iblock->author ? [
                                'id' => $iblock->author->id,
                                'name' => $iblock->author->name
                            ] : null,
                            'excerpt' => $this->extractMetaFromContent($iblock->content)['excerpt'],
                            'created_at' => $iblock->created_at->toISOString()
                        ];
                    }),
                    'count' => $iblocks->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API IBlock: ошибка поиска информационных блоков', [
                'query' => $query,
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при поиске информационных блоков'
            ], 500);
        }
    }
    
    /**
     * Получение информационных блоков по автору
     *
     * @param int $authorId
     * @return JsonResponse
     */
    public function getByAuthor(int $authorId): JsonResponse
    {
        try {
            Log::info('API IBlock: получение информационных блоков по автору', ['author_id' => $authorId]);
            
            $iblocks = IBlock::with('author')
                ->where('author_id', $authorId)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->get();
            
            if ($iblocks->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'author_id' => $authorId,
                        'iblocks' => []
                    ]
                ]);
            }
            
            $author = $iblocks->first()->author;
            
            $structuredIBlocks = $iblocks->map(function($iblock) {
                return [
                    'id' => $iblock->id,
                    'title' => $iblock->title,
                    'content' => $iblock->content,
                    'excerpt' => $this->extractMetaFromContent($iblock->content)['excerpt'],
                    'created_at' => $iblock->created_at->toISOString(),
                    'updated_at' => $iblock->updated_at->toISOString()
                ];
            })->toArray();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'author' => $author ? [
                        'id' => $author->id,
                        'name' => $author->name,
                        'email' => $author->email
                    ] : null,
                    'author_id' => $authorId,
                    'iblocks' => $structuredIBlocks,
                    'count' => $iblocks->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API IBlock: ошибка получения информационных блоков по автору', [
                'author_id' => $authorId,
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении информационных блоков по автору'
            ], 500);
        }
    }
    
    /**
     * Получение статистики по информационным блокам
     *
     * @return JsonResponse
     */
    public function getStats(): JsonResponse
    {
        try {
            Log::info('API IBlock: получение статистики по информационным блокам');
            
            $totalIBlocks = IBlock::count();
            $activeIBlocks = IBlock::whereNull('deleted_at')->count();
            $deletedIBlocks = IBlock::whereNotNull('deleted_at')->count();
            
            // Статистика по авторам
            $authorsStats = IBlock::select('author_id')
                ->selectRaw('COUNT(*) as block_count')
                ->whereNull('deleted_at')
                ->groupBy('author_id')
                ->with('author')
                ->get()
                ->map(function($item) {
                    return [
                        'author_id' => $item->author_id,
                        'author_name' => $item->author ? $item->author->name : 'Неизвестный',
                        'block_count' => $item->block_count
                    ];
                })
                ->sortByDesc('block_count')
                ->values()
                ->toArray();
            
            // Статистика по датам создания
            $creationStats = IBlock::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->whereNull('deleted_at')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->limit(30)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_blocks' => $totalIBlocks,
                    'active_blocks' => $activeIBlocks,
                    'deleted_blocks' => $deletedIBlocks,
                    'authors_stats' => $authorsStats,
                    'creation_stats' => $creationStats,
                    'meta' => [
                        'timestamp' => now()->toISOString()
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API IBlock: ошибка получения статистики', [
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении статистики'
            ], 500);
        }
    }
}