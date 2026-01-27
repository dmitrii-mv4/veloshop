<?php

namespace App\Modules\Menu\Controllers\Api;

use App\Core\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Modules\Menu\Models\Menu;
use App\Modules\Menu\Models\MenuType;
use App\Modules\Menu\Models\MenuItem;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    /**
     * Получение раздельных данных меню
     * Возвращает типы, меню и пункты отдельными массивами
     *
     * @return JsonResponse
     */
    public function getSeparate(): JsonResponse
    {
        try {
            Log::info('API Menu: получение раздельных данных меню');
            
            $types = MenuType::all();
            $menus = Menu::all();
            $items = MenuItem::all();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'menu_types' => $types,
                    'menus' => $menus,
                    'menu_items' => $items
                ],
                'meta' => [
                    'types_count' => $types->count(),
                    'menus_count' => $menus->count(),
                    'items_count' => $items->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Menu: ошибка получения раздельных данных', [
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении данных меню'
            ], 500);
        }
    }
    
    /**
     * Получение древовидной структуры меню
     * Возвращает меню с вложенными пунктами в виде дерева
     *
     * @return JsonResponse
     */
    public function getTree(): JsonResponse
    {
        try {
            Log::info('API Menu: получение древовидной структуры меню');
            
            // Получаем все активные меню с их пунктами
            $menus = Menu::with(['menuType', 'items' => function($query) {
                $query->where('is_active', true)
                      ->orderBy('order', 'asc');
            }])->where('is_active', true)->get();
            
            // Если нет активных меню
            if ($menus->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'menus' => []
                    ]
                ]);
            }
            
            // Структурируем меню в древовидный формат
            $treeData = $this->buildMenuTree($menus);
            
            Log::info('API Menu: древовидная структура успешно построена', [
                'menus_count' => $menus->count(),
                'total_items' => $menus->sum(fn($menu) => $menu->items->count())
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $treeData
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Menu: ошибка получения древовидной структуры', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при построении древовидной структуры меню',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Построение древовидной структуры меню
     *
     * @param \Illuminate\Database\Eloquent\Collection $menus
     * @return array
     */
    private function buildMenuTree($menus): array
    {
        $structuredMenus = $menus->map(function($menu) {
            // Формируем древовидную структуру пунктов меню
            $itemsTree = $this->buildItemsTree($menu->items);
            
            return [
                'id' => $menu->id,
                'name' => $menu->name,
                'description' => $menu->description,
                'menu_type_id' => $menu->menu_type_id,
                'menu_type' => $menu->menuType ? [
                    'id' => $menu->menuType->id,
                    'name' => $menu->menuType->name,
                    'created_at' => $menu->menuType->created_at,
                    'updated_at' => $menu->menuType->updated_at
                ] : null,
                'is_active' => (bool)$menu->is_active,
                'items' => $itemsTree,
                'items_count' => count($this->flattenTree($itemsTree)),
                'created_at' => $menu->created_at,
                'updated_at' => $menu->updated_at
            ];
        })->toArray();
        
        return [
            'menus' => $structuredMenus,
            'meta' => [
                'total_menus' => count($structuredMenus),
                'total_items' => array_sum(array_column($structuredMenus, 'items_count')),
                'timestamp' => now()->toISOString()
            ]
        ];
    }
    
    /**
     * Построение древовидной структуры пунктов меню
     *
     * @param \Illuminate\Database\Eloquent\Collection $items
     * @param int|null $parentId
     * @return array
     */
    private function buildItemsTree($items, $parentId = null): array
    {
        $tree = [];
        
        foreach ($items as $item) {
            if ($item->parent_id == $parentId) {
                $children = $this->buildItemsTree($items, $item->id);
                
                $node = [
                    'id' => $item->id,
                    'title' => $item->title,
                    'url' => $item->url,
                    'icon' => $item->icon,
                    'order' => $item->order,
                    'is_active' => (bool)$item->is_active,
                    'seo_title' => $item->seo_title,
                    'target' => $item->target ?? '_self',
                    'class' => $item->class ?? null,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at
                ];
                
                if (!empty($children)) {
                    $node['children'] = $children;
                }
                
                $tree[] = $node;
            }
        }
        
        // Сортировка по полю order
        usort($tree, function($a, $b) {
            return $a['order'] <=> $b['order'];
        });
        
        return $tree;
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
     * Получение меню по типу (например, для header, footer)
     *
     * @param string $typeName
     * @return JsonResponse
     */
    public function getByType(string $typeName): JsonResponse
    {
        try {
            Log::info('API Menu: получение меню по типу', ['type' => $typeName]);
            
            $menuType = MenuType::where('name', $typeName)->first();
            
            if (!$menuType) {
                Log::warning('API Menu: тип меню не найден', ['type' => $typeName]);
                return response()->json([
                    'success' => false,
                    'message' => 'Тип меню не найден'
                ], 404);
            }
            
            $menu = Menu::with(['items' => function($query) {
                $query->where('is_active', true)
                      ->orderBy('order', 'asc');
            }])->where('menu_type_id', $menuType->id)
               ->where('is_active', true)
               ->first();
            
            if (!$menu) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'menu' => null,
                        'items' => []
                    ]
                ]);
            }
            
            $itemsTree = $this->buildItemsTree($menu->items);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'menu' => [
                        'id' => $menu->id,
                        'name' => $menu->name,
                        'description' => $menu->description,
                        'type' => $menuType->name
                    ],
                    'items' => $itemsTree
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API Menu: ошибка получения меню по типу', [
                'type' => $typeName,
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении меню'
            ], 500);
        }
    }
}