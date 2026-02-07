<?php

namespace App\Modules\Catalog\Controllers;

use App\Core\Controllers\Controller;
use App\Modules\Catalog\Models\CatalogWarehouse;
use App\Modules\Catalog\Requests\Warehouses\CreateWarehousesRequest;
use App\Modules\Catalog\Requests\Warehouses\UpdateWarehousesRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Контроллер для управления складами в административной панели
 * Обеспечивает CRUD операции для складов
 */
class WarehouseController extends Controller
{
    /**
     * Отображение списка складов с фильтрацией и пагинацией
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        Log::info('Отображение списка складов');

        // Получаем параметры фильтрации
        $search = $request->get('search', '');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $perPage = $request->get('per_page', 25);
        $status = $request->get('status', '');

        // Строим запрос
        $query = CatalogWarehouse::query();

        // Применяем поиск
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('contacts', 'like', "%{$search}%");
            });
            Log::info('Применен поиск по складам: ' . $search);
        }

        // Фильтр по статусу
        if ($status !== '') {
            $isActive = $status === 'active';
            $query->where('is_active', $isActive);
            Log::info('Применен фильтр по статусу: ' . $status);
        }

        // Применяем сортировку
        $validSortColumns = ['title', 'created_at', 'updated_at'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'created_at';
        $sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? $sortOrder : 'desc';
        
        $query->orderBy($sortBy, $sortOrder);
        Log::info('Применена сортировка по полю: ' . $sortBy . ' в порядке: ' . $sortOrder);

        // Получаем склады с пагинацией
        $warehouses = $query->paginate($perPage);
        $totalWarehouses = $warehouses->total();

        // Получаем склады с подсчетом статистики
        $warehouses = $query->withCount([
            'warehouseOffers as unique_offers_count' => function($q) {
                $q->select(DB::raw('count(distinct offer_id)'));
            }
        ])->withSum('warehouseOffers as total_products_count', 'count')
        ->paginate($perPage);

        Log::info('Получено ' . $warehouses->count() . ' складов из ' . $totalWarehouses);

        return view('catalog::warehouses.index', compact(
            'warehouses',
            'search',
            'sortBy',
            'sortOrder',
            'perPage',
            'status',
            'totalWarehouses'
        ));
    }

    /**
     * Отображение формы создания нового склада
     *
     * @return View
     */
    public function create(): View
    {
        Log::info('Отображение формы создания нового склада');
        return view('catalog::warehouses.create');
    }

    /**
     * Сохранение нового склада в базу данных
     *
     * @param CreateWarehousesRequest $request
     * @return RedirectResponse
     */
    public function store(CreateWarehousesRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();
            
            $warehouse = CatalogWarehouse::create($data);

            // dd($warehouse);
            
            Log::info('Warehouse created successfully', [
                'warehouse_id' => $warehouse->id,
                'title' => $warehouse->title,
                'created_by' => auth()->id()
            ]);
            
            return redirect()
                ->route('catalog.warehouses.index')
                ->with('success', 'Склад "' . $warehouse->title . '" успешно создан.');
        } catch (\Exception $e) {
            Log::error('Error creating warehouse', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Произошла ошибка при создании склада. Пожалуйста, попробуйте снова.');
        }
    }

    /**
     * Отображение формы редактирования существующего склада
     *
     * @param CatalogWarehouse $warehouse
     * @return View
     */
    public function edit(CatalogWarehouse $warehouse): View
    {
        Log::info('Отображение формы редактирования склада ID: ' . $warehouse->id);
        return view('catalog::warehouses.edit', compact('warehouse'));
    }

    /**
     * Обновление информации о складе
     *
     * @param UpdateWarehousesRequest $request
     * @param Warehouse $warehouse
     * @return RedirectResponse
     */
    public function update(UpdateWarehousesRequest $request, CatalogWarehouse $warehouse): RedirectResponse
    {
        try {
            $oldTitle = $warehouse->title;
            $warehouse->update($request->validated());
            
            Log::info('Склад успешно обновлен: ' . $warehouse->id . ' - ' . $oldTitle . ' -> ' . $warehouse->title);
            
            return redirect()
                ->route('catalog.warehouses.index')
                ->with('success', 'Склад "' . $warehouse->title . '" успешно обновлен.');
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении склада ID: ' . $warehouse->id . ' - ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Произошла ошибка при обновлении склада. Пожалуйста, попробуйте снова.');
        }
    }

    /**
     * Удаление склада (полное удаление без корзины)
     *
     * @param CatalogWarehouse $warehouse
     * @return RedirectResponse
     */
    public function destroy(CatalogWarehouse $warehouse): RedirectResponse
    {
        try {
            $warehouseTitle = $warehouse->title;
            $warehouse->delete();
            
            Log::info('Склад полностью удален: ' . $warehouse->id . ' - ' . $warehouseTitle);
            
            return redirect()
                ->route('catalog.warehouses.index')
                ->with('success', 'Склад "' . $warehouseTitle . '" успешно удален.');
        } catch (\Exception $e) {
            Log::error('Ошибка при удалении склада ID: ' . $warehouse->id . ' - ' . $e->getMessage());
            
            return back()
                ->with('error', 'Произошла ошибка при удалении склада. Пожалуйста, попробуйте снова.');
        }
    }

    /**
     * Быстрое изменение статуса активности склада
     *
     * @param Request $request
     * @param CatalogWarehouse $warehouse
     * @return RedirectResponse
     */
    public function toggleStatus(Request $request, CatalogWarehouse $warehouse): RedirectResponse
    {
        try {
            $warehouse->is_active = !$warehouse->is_active;
            $warehouse->save();
            
            $status = $warehouse->is_active ? 'активирован' : 'деактивирован';
            Log::info('Статус склада изменен: ' . $warehouse->id . ' - ' . $warehouse->title . ' -> ' . $status);
            
            return back()
                ->with('success', 'Статус склада "' . $warehouse->title . '" успешно изменен.');
        } catch (\Exception $e) {
            Log::error('Ошибка при изменении статуса склада ID: ' . $warehouse->id . ' - ' . $e->getMessage());
            
            return back()
                ->with('error', 'Произошла ошибка при изменении статуса склада.');
        }
    }
}