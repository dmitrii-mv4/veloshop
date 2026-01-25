<?php

namespace App\Modules\Menu\Controllers;

use App\Core\Controllers\Controller;
use App\Modules\Menu\Models\MenuType;
use App\Modules\Menu\Requests\MenuType\StoreMenuTypeRequest;
use App\Modules\Menu\Requests\MenuType\UpdateMenuTypeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\Request;

/**
 * Контроллер управления типами меню
 * Обрабатывает CRUD операции для типов меню
 */
class MenuTypeController extends Controller
{
    /**
     * Отображение списка всех типов меню
     * Маршрут: GET /menu/types
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        try {
            // Получаем параметры фильтрации и сортировки
            $search = $request->get('search', '');
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $perPage = $request->get('per_page', 25);
            
            // Базовый запрос
            $query = MenuType::with(['creator', 'updater'])
                ->withCount('menus');
            
            // Поиск по названию
            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }
            
            // Сортировка
            $validSortColumns = ['name', 'created_at', 'updated_at', 'menus_count'];
            if (in_array($sortBy, $validSortColumns)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }
            
            // Получаем статистику
            $totalTypes = MenuType::count();
            $usedTypes = MenuType::has('menus')->count();
            $unusedTypes = MenuType::doesntHave('menus')->count();
            
            $menuTypes = $query->paginate($perPage)
                ->appends($request->except('page'));
            
            Log::info('Отображение списка типов меню', [
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'total' => $menuTypes->total(),
                'user_id' => auth()->id()
            ]);
            
            return view('menu::menutype.index', compact(
                'menuTypes',
                'search',
                'sortBy',
                'sortOrder',
                'perPage',
                'totalTypes',
                'usedTypes',
                'unusedTypes'
            ));
            
        } catch (\Exception $e) {
            Log::error('Ошибка при получении списка типов меню', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            abort(500, 'Ошибка при загрузке списка типов меню');
        }
    }

    /**
     * Отображение формы создания нового типа меню
     * Маршрут: GET /menu/types/create
     *
     * @return View
     */
    public function create(): View
    {
        Log::info('Отображение формы создания типа меню', [
            'user_id' => auth()->id()
        ]);
        
        return view('menu::menutype.create');
    }

    /**
     * Сохранение нового типа меню в базу данных
     * Маршрут: POST /menu/types
     *
     * @param StoreMenuTypeRequest $request
     * @return RedirectResponse
     */
    public function store(StoreMenuTypeRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();
            
            $menuType = MenuType::create($data);
            
            Log::info('Тип меню успешно создан', [
                'menu_type_id' => $menuType->id,
                'name' => $menuType->name,
                'user_id' => auth()->id()
            ]);
            
            return redirect()->route('admin.menu.types.index')
                ->with('success', 'Тип меню "' . $menuType->name . '" успешно создан');
                
        } catch (\Exception $e) {
            Log::error('Ошибка при создании типа меню', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()
                ->with('error', 'Ошибка при создании типа меню: ' . $e->getMessage());
        }
    }

    /**
     * Отображение формы редактирования типа меню
     * Маршрут: GET /menu/types/{menutype}/edit
     *
     * @param MenuType $menutype
     * @return View
     */
    public function edit(MenuType $menutype): View
    {
        try {
            Log::info('Отображение формы редактирования типа меню', [
                'menu_type_id' => $menutype->id,
                'user_id' => auth()->id()
            ]);
            
            return view('menu::menutype.edit', compact('menutype'));
            
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке формы редактирования типа меню', [
                'menu_type_id' => $menutype->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            abort(500, 'Ошибка при загрузке формы редактирования');
        }
    }

    /**
     * Обновление типа меню в базе данных
     * Маршрут: PUT/PATCH /menu/types/{menutype}
     *
     * @param UpdateMenuTypeRequest $request
     * @param MenuType $menutype
     * @return RedirectResponse
     */
    public function update(UpdateMenuTypeRequest $request, MenuType $menutype): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['updated_by'] = auth()->id();
            
            $menutype->update($data);
            
            Log::info('Тип меню успешно обновлен', [
                'menu_type_id' => $menutype->id,
                'name' => $menutype->name,
                'user_id' => auth()->id()
            ]);
            
            return redirect()->route('admin.menu.types.index')
                ->with('success', 'Тип меню "' . $menutype->name . '" успешно обновлен');
                
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении типа меню', [
                'menu_type_id' => $menutype->id,
                'error' => $e->getMessage(),
                'data' => $request->all(),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()
                ->with('error', 'Ошибка при обновлении типа меню: ' . $e->getMessage());
        }
    }

    /**
     * Удаление типа меню из базы данных
     * Полное удаление без мягкого удаления
     * Маршрут: DELETE /menu/types/{menutype}
     *
     * @param Request $request
     * @param MenuType $menutype
     * @return RedirectResponse
     */
    public function destroy(Request $request, MenuType $menutype): RedirectResponse
    {
        try {
            $menuTypeName = $menutype->name;
            $menuTypeId = $menutype->id;
            $menusCount = $menutype->menus_count ?? 0;
            
            Log::info('Начало удаления типа меню', [
                'menu_type_id' => $menuTypeId,
                'menu_type_name' => $menuTypeName,
                'menus_count' => $menusCount,
                'user_id' => auth()->id()
            ]);
            
            // Удаляем тип меню (в модели настроено обнуление menu_type_id у связанных меню)
            $menutype->delete();
            
            Log::info('Тип меню успешно удален', [
                'menu_type_id' => $menuTypeId,
                'name' => $menuTypeName,
                'menus_updated' => $menusCount,
                'user_id' => auth()->id()
            ]);
            
            // Проверяем, является ли запрос AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Тип меню "' . $menuTypeName . '" успешно удален',
                    'redirect' => route('admin.menu.types.index')
                ]);
            }
            
            return redirect()->route('admin.menu.types.index')
                ->with('success', 'Тип меню "' . $menuTypeName . '" успешно удален');
                
        } catch (\Exception $e) {
            Log::error('Ошибка при удалении типа меню', [
                'menu_type_id' => $menutype->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Проверяем, является ли запрос AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка при удалении типа меню: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Ошибка при удалении типа меню: ' . $e->getMessage());
        }
    }
}