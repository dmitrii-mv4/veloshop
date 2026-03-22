<?php

namespace App\Modules\Menu\Controllers;

use App\Core\Controllers\Controller;
use App\Modules\Menu\Models\Menu;
use App\Modules\Menu\Models\MenuType;
use App\Modules\Menu\Requests\Menu\StoreMenuRequest;
use App\Modules\Menu\Requests\Menu\UpdateMenuRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\Request;

/**
 * Контроллер управления типами меню
 * Обрабатывает CRUD операции для меню
 */
class MenuController extends Controller
{
    /**
     * Отображение списка всех меню
     * Маршрут: GET /menu
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        try {
            // Получаем параметры фильтрации
            $search = $request->get('search', '');
            $menuTypeId = $request->get('menu_type_id', 'all');
            $status = $request->get('status', 'all');
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $perPage = $request->get('per_page', 25);
            
            // Базовый запрос
            $query = Menu::with(['creator', 'updater', 'menuType'])
                ->withCount('items');
            
            // Поиск
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('menuType', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
                });
            }
            
            // Фильтр по типу меню
            if ($menuTypeId !== 'all' && $menuTypeId) {
                $query->where('menu_type_id', $menuTypeId); // Исправлено: было 'type_id'
            }
            
            // Фильтр по статусу
            if ($status !== 'all') {
                $isActive = $status === 'active';
                $query->where('is_active', $isActive);
            }
            
            // Сортировка
            $query->orderBy($sortBy, $sortOrder);
            
            // Получаем статистику
            $totalMenus = Menu::count();
            $activeMenus = Menu::where('is_active', true)->count();
            $inactiveMenus = Menu::where('is_active', false)->count();
            
            // Получаем все типы меню для фильтра
            $menuTypes = MenuType::orderBy('name')->get();
            
            $menus = $query->paginate($perPage)
                ->appends($request->except('page'));
            
            Log::info('Отображение списка меню с фильтрами', [
                'search' => $search,
                'menu_type_id' => $menuTypeId, // Исправлено
                'status' => $status,
                'total' => $menus->total()
            ]);
            
            return view('menu::menu.index', compact(
                'menus',
                'menuTypes',
                'search',
                'menuTypeId',
                'status',
                'sortBy',
                'sortOrder',
                'perPage',
                'totalMenus',
                'activeMenus',
                'inactiveMenus'
            ));
            
        } catch (\Exception $e) {
            Log::error('Ошибка при получении списка меню', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            abort(500, 'Ошибка при загрузке списка меню');
        }
    }

    /**
     * Отображение формы создания нового меню
     * Маршрут: GET /menu/create
     *
     * @return View
     */
    public function create(): View
    {
        try {
            Log::info('Отображение формы создания меню');
            
            // Получаем все типы меню для выпадающего списка
            $menuTypes = MenuType::orderBy('name')->get();
            
            return view('menu::menu.create', compact('menuTypes'));
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке формы создания меню', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            abort(500, 'Ошибка при загрузке формы создания меню');
        }
    }

    /**
     * Сохранение нового меню в базу данных
     * Маршрут: POST /menu
     *
     * @param StoreMenuRequest $request
     * @return RedirectResponse
     */
    public function store(StoreMenuRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();
            
            $menu = Menu::create($data);
            
            Log::info('Меню успешно создано', [
                'menu_id' => $menu->id,
                'name' => $menu->name,
                'menu_type_id' => $menu->menu_type_id,
                'user_id' => auth()->id()
            ]);
            
            return redirect()->route('admin.menu.index')
                ->with('success', 'Меню "' . $menu->name . '" успешно создано');
                
        } catch (\Exception $e) {
            Log::error('Ошибка при создании меню', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
                'user_id' => auth()->id()
            ]);
            
            return back()->withInput()
                ->with('error', 'Ошибка при создании меню: ' . $e->getMessage());
        }
    }

    /**
     * Отображение формы редактирования меню
     * Маршрут: GET /menu/{menu}/edit
     *
     * @param Menu $menu
     * @return View
     */
    public function edit(Menu $menu): View
    {
        try {
            Log::info('Отображение формы редактирования меню', ['menu_id' => $menu->id]);
            
            // Получаем все типы меню для выпадающего списка
            $menuTypes = MenuType::orderBy('name')->get();
            
            return view('menu::menu.edit', compact('menu', 'menuTypes'));
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке формы редактирования меню', [
                'menu_id' => $menu->id,
                'error' => $e->getMessage()
            ]);
            
            abort(500, 'Ошибка при загрузке формы редактирования');
        }
    }

    /**
     * Обновление меню в базе данных
     * Маршрут: PUT/PATCH /menu/{menu}
     *
     * @param UpdateMenuRequest $request
     * @param Menu $menu
     * @return RedirectResponse
     */
    public function update(UpdateMenuRequest $request, Menu $menu): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['updated_by'] = auth()->id();
            
            $menu->update($data);
            
            Log::info('Меню успешно обновлено', [
                'menu_id' => $menu->id,
                'name' => $menu->name,
                'user_id' => auth()->id()
            ]);
            
            return redirect()->route('admin.menu.index')
                ->with('success', 'Меню "' . $menu->name . '" успешно обновлено');
                
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении меню', [
                'menu_id' => $menu->id,
                'error' => $e->getMessage(),
                'data' => $request->all(),
                'user_id' => auth()->id()
            ]);
            
            return back()->withInput()
                ->with('error', 'Ошибка при обновлении меню: ' . $e->getMessage());
        }
    }

    /**
     * Удаление меню из базы данных
     * Каскадное удаление пунктов меню настроено на уровне модели
     * Маршрут: DELETE /menu/{menu}
     *
     * @param Request $request
     * @param Menu $menu
     * @return RedirectResponse
     */
    public function destroy(Request $request, Menu $menu): RedirectResponse
    {
        try {
            $menuName = $menu->name;
            $menuId = $menu->id;
            $itemsCount = $menu->items_count ?? 0;
            
            Log::info('Начало удаления меню', [
                'menu_id' => $menuId,
                'menu_name' => $menuName,
                'items_count' => $itemsCount,
                'user_id' => auth()->id()
            ]);
            
            // Удаляем меню (пункты будут удалены автоматически через событие в модели)
            $menu->delete();
            
            Log::info('Меню успешно удалено', [
                'menu_id' => $menuId,
                'name' => $menuName,
                'items_deleted' => $itemsCount,
                'user_id' => auth()->id()
            ]);
            
            // Проверяем, является ли запрос AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Меню "' . $menuName . '" успешно удалено',
                    'redirect' => route('admin.menu.index')
                ]);
            }
            
            return redirect()->route('admin.menu.index')
                ->with('success', 'Меню "' . $menuName . '" успешно удалено');
                
        } catch (\Exception $e) {
            Log::error('Ошибка при удалении меню', [
                'menu_id' => $menu->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Проверяем, является ли запрос AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка при удалении меню: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Ошибка при удалении меню: ' . $e->getMessage());
        }
    }
}