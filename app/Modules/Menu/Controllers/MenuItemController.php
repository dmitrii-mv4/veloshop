<?php

namespace App\Modules\Menu\Controllers;

use App\Core\Controllers\Controller;
use App\Modules\Menu\Models\Menu;
use App\Modules\Menu\Models\MenuItem;
use App\Modules\Menu\Requests\MenuItem\StoreMenuItemRequest;
use App\Modules\Menu\Requests\MenuItem\UpdateMenuItemRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\Request;

/**
 * Контроллер управления пунктами меню
 * Обрабатывает CRUD операции для пунктов конкретного меню
 */
class MenuItemController extends Controller
{
    /**
     * Отображение списка пунктов меню
     * Маршрут: GET /menu/{menu}/
     *
     * @param Menu $menu
     * @return View
     */
    public function index(Menu $menu): View
    {
        try {
            $items = MenuItem::where('menu_id', $menu->id)
                ->whereNull('parent_id')
                ->with(['children' => function ($query) {
                    $query->orderBy('order');
                }, 'creator', 'updater'])
                ->orderBy('order')
                ->get();
            
            Log::info('Отображение списка пунктов меню', [
                'menu_id' => $menu->id,
                'menu_name' => $menu->name,
                'items_count' => $items->count()
            ]);
            
            return view('menu::menuitem.index', compact('menu', 'items'));
        } catch (\Exception $e) {
            Log::error('Ошибка при получении списка пунктов меню', [
                'menu_id' => $menu->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            abort(500, 'Ошибка при загрузке пунктов меню');
        }
    }

    /**
     * Отображение формы создания нового пункта меню
     * Маршрут: GET /menu/{menu}/create
     *
     * @param Menu $menu
     * @return View
     */
    public function create(Menu $menu): View
    {
        try {
            Log::info('Отображение формы создания пункта меню', ['menu_id' => $menu->id]);
            
            // Получаем родительские пункты в правильном формате для Blade
            $parentItems = $this->getParentItemsForSelect($menu->id);
            
            return view('menu::menuitem.create', compact('menu', 'parentItems'));
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке формы создания пункта меню', [
                'menu_id' => $menu->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            abort(500, 'Ошибка при загрузке формы создания');
        }
    }

    /**
     * Сохранение нового пункта меню в базу данных
     * Маршрут: POST /menu/{menu}
     *
     * @param StoreMenuItemRequest $request
     * @param Menu $menu
     * @return RedirectResponse
     */
    public function store(StoreMenuItemRequest $request, Menu $menu): RedirectResponse
    {
        try {
            Log::info('Начало создания пункта меню', [
                'menu_id' => $menu->id,
                'data' => $request->all(),
                'user_id' => auth()->id()
            ]);
            
            $data = $request->validated();
            $data['menu_id'] = $menu->id;
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();
            
            // Устанавливаем порядок по умолчанию, если не указан
            if (!isset($data['order']) || $data['order'] === null) {
                $maxOrder = MenuItem::where('menu_id', $menu->id)
                    ->where('parent_id', $data['parent_id'] ?? null)
                    ->max('order') ?? 0;
                $data['order'] = $maxOrder + 1;
            }
            
            $menuItem = MenuItem::create($data);
            
            Log::info('Пункт меню успешно создан', [
                'menu_item_id' => $menuItem->id,
                'title' => $menuItem->title,
                'menu_id' => $menu->id,
                'user_id' => auth()->id()
            ]);
            
            return redirect()->route('admin.menu.items.index', $menu)
                ->with('success', 'Пункт меню "' . $menuItem->title . '" успешно создан');
                
        } catch (\Exception $e) {
            Log::error('Ошибка при создании пункта меню', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
                'menu_id' => $menu->id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()
                ->with('error', 'Ошибка при создании пункта меню: ' . $e->getMessage());
        }
    }

    /**
     * Отображение формы редактирования пункта меню
     * Маршрут: GET /menu/{menu}/{menuitem}/edit
     *
     * @param Menu $menu
     * @param MenuItem $menuitem
     * @return View
     */
    public function edit(Menu $menu, MenuItem $menuitem): View
    {
        try {
            // Проверка, что пункт меню принадлежит указанному меню
            if ($menuitem->menu_id !== $menu->id) {
                Log::warning('Попытка редактирования пункта меню из другого меню', [
                    'menu_id' => $menu->id,
                    'item_menu_id' => $menuitem->menu_id,
                    'item_id' => $menuitem->id
                ]);
                
                abort(404);
            }
            
            // Получаем родительские пункты в правильном формате
            $parentItems = $this->getParentItemsForSelect($menu->id, $menuitem->id);
            
            Log::info('Отображение формы редактирования пункта меню', [
                'menu_id' => $menu->id,
                'item_id' => $menuitem->id
            ]);
            
            return view('menu::menuitem.edit', compact('menu', 'menuitem', 'parentItems'));
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке формы редактирования пункта меню', [
                'menu_id' => $menu->id,
                'item_id' => $menuitem->id,
                'error' => $e->getMessage()
            ]);
            
            abort(500, 'Ошибка при загрузке формы редактирования');
        }
    }

    /**
     * Обновление пункта меню в базе данных
     * Маршрут: PUT/PATCH /menu/{menu}/{menuitem}
     *
     * @param UpdateMenuItemRequest $request
     * @param Menu $menu
     * @param MenuItem $menuitem
     * @return RedirectResponse
     */
    public function update(UpdateMenuItemRequest $request, Menu $menu, MenuItem $menuitem): RedirectResponse
    {
        try {
            // Проверка, что пункт меню принадлежит указанному меню
            if ($menuitem->menu_id !== $menu->id) {
                Log::warning('Попытка обновления пункта меню из другого меню', [
                    'menu_id' => $menu->id,
                    'item_menu_id' => $menuitem->menu_id,
                    'item_id' => $menuitem->id
                ]);
                
                abort(404);
            }
            
            $data = $request->validated();
            $data['updated_by'] = auth()->id();
            
            $menuitem->update($data);
            
            Log::info('Пункт меню успешно обновлен', [
                'menu_item_id' => $menuitem->id,
                'title' => $menuitem->title,
                'menu_id' => $menu->id,
                'user_id' => auth()->id()
            ]);
            
            return redirect()->route('admin.menu.items.index', $menu)
                ->with('success', 'Пункт меню "' . $menuitem->title . '" успешно обновлен');
                
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении пункта меню', [
                'menu_item_id' => $menuitem->id,
                'menu_id' => $menu->id,
                'error' => $e->getMessage(),
                'data' => $request->all(),
                'user_id' => auth()->id()
            ]);
            
            return back()->withInput()
                ->with('error', 'Ошибка при обновлении пункта меню: ' . $e->getMessage());
        }
    }

    /**
     * Удаление пункта меню из базы данных
     * Каскадное удаление дочерних пунктов настроено на уровне БД
     * Маршрут: DELETE /menu/{menu}/{menuitem}
     *
     * @param Menu $menu
     * @param MenuItem $menuitem
     * @return RedirectResponse|JsonResponse
     */
    public function destroy(Request $request, Menu $menu, MenuItem $menuitem)
    {
        try {
            // Проверка, что пункт меню принадлежит указанному меню
            if ($menuitem->menu_id !== $menu->id) {
                Log::warning('Попытка удаления пункта меню из другого меню', [
                    'menu_id' => $menu->id,
                    'item_menu_id' => $menuitem->menu_id,
                    'item_id' => $menuitem->id
                ]);
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Пункт меню не найден'
                    ], 404);
                }
                
                abort(404);
            }
            
            $itemTitle = $menuitem->title;
            $itemId = $menuitem->id;
            
            // Удаляем пункт меню (дочерние будут удалены рекурсивно через событие модели)
            $menuitem->delete();
            
            Log::info('Пункт меню успешно удален', [
                'menu_item_id' => $itemId,
                'title' => $itemTitle,
                'menu_id' => $menu->id,
                'user_id' => auth()->id()
            ]);
            
            // Проверяем, является ли запрос AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Пункт меню "' . $itemTitle . '" успешно удален',
                    'redirect' => route('admin.menu.items.index', $menu)
                ]);
            }
            
            return redirect()->route('admin.menu.items.index', $menu)
                ->with('success', 'Пункт меню "' . $itemTitle . '" успешно удален');
                
        } catch (\Exception $e) {
            Log::error('Ошибка при удалении пункта меню', [
                'menu_item_id' => $menuitem->id,
                'menu_id' => $menu->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Проверяем, является ли запрос AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка при удалении пункта меню: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Ошибка при удалении пункта меню: ' . $e->getMessage());
        }
    }

    /**
     * Получить родительские пункты для select в правильном формате
     *
     * @param int $menuId
     * @param int|null $excludeId
     * @return array
     */
    private function getParentItemsForSelect(int $menuId, ?int $excludeId = null): array
    {
        try {
            Log::debug('Получение родительских пунктов для select', [
                'menu_id' => $menuId,
                'exclude_id' => $excludeId
            ]);
            
            $items = MenuItem::where('menu_id', $menuId)
                ->whereNull('parent_id')
                ->orderBy('order')
                ->get();
            
            $result = [];
            
            foreach ($items as $item) {
                if ($excludeId && $item->id == $excludeId) {
                    continue;
                }
                
                $result[] = [
                    'id' => $item->id,
                    'title' => $item->title,
                    'level' => 0
                ];
                
                $result = array_merge($result, $this->getChildrenForSelect($item, 1, $excludeId));
            }
            
            Log::debug('Родительские пункты получены', [
                'menu_id' => $menuId,
                'items_count' => count($result)
            ]);
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Ошибка при получении родительских пунктов', [
                'menu_id' => $menuId,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Рекурсивно получить дочерние элементы для select
     *
     * @param MenuItem $parent
     * @param int $level
     * @param int|null $excludeId
     * @return array
     */
    private function getChildrenForSelect(MenuItem $parent, int $level, ?int $excludeId = null): array
    {
        $children = $parent->children()->orderBy('order')->get();
        $result = [];
        
        foreach ($children as $child) {
            if ($excludeId && $child->id == $excludeId) {
                continue;
            }
            
            $result[] = [
                'id' => $child->id,
                'title' => $child->title,
                'level' => $level
            ];
            
            $result = array_merge($result, $this->getChildrenForSelect($child, $level + 1, $excludeId));
        }
        
        return $result;
    }

    /**
     * Удаление пункта меню через AJAX запрос
     * Маршрут: POST /menu/item/{menuitem}/destroy
     *
     * @param Request $request
     * @param MenuItem $menuitem
     * @return JsonResponse
     */
    public function destroyAjax(Request $request, MenuItem $menuitem): JsonResponse
    {
        try {
            Log::info('AJAX удаление пункта меню', [
                'menu_item_id' => $menuitem->id,
                'title' => $menuitem->title,
                'user_id' => auth()->id()
            ]);

            // Проверяем права пользователя
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Требуется авторизация'
                ], 401);
            }

            $menuId = $menuitem->menu_id;
            $itemTitle = $menuitem->title;
            
            // Удаляем пункт меню
            $menuitem->delete();
            
            Log::info('Пункт меню успешно удален через AJAX', [
                'menu_item_id' => $menuitem->id,
                'title' => $itemTitle,
                'menu_id' => $menuId,
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Пункт меню "' . $itemTitle . '" успешно удален',
                'redirect' => route('admin.menu.items.index', ['menu' => $menuId])
            ]);
                
        } catch (\Exception $e) {
            Log::error('Ошибка при AJAX удалении пункта меню', [
                'menu_item_id' => $menuitem->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при удалении пункта меню: ' . $e->getMessage()
            ], 500);
        }
    }
}