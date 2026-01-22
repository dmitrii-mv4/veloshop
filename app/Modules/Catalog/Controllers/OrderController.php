<?php

namespace App\Modules\Catalog\Controllers;

use App\Core\Controllers\Controller;
use App\Modules\Catalog\Models\Order;
use App\Modules\User\Models\User;
use App\Modules\Catalog\Requests\Orders\OrdersCreateRequest;
use App\Modules\Catalog\Requests\Orders\OrdersEditRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Контроллер для управления заказами в модуле Catalog
 * 
 * Обеспечивает полный CRUD функционал для заказов, включая корзину,
 * восстановление и автоматическую очистку.
 */
class OrderController extends Controller
{
    /**
     * Отображение списка активных заказов
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            // Получение параметров фильтрации
            $search = $request->get('search', '');
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $perPage = $request->get('per_page', 25);
            
            // Построение запроса с фильтрацией
            $query = Order::with(['customer', 'responsible', 'creator'])
                ->whereNull('deleted_at');
            
            // Поиск по номеру заказа или комментарию
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhere('comment', 'like', "%{$search}%")
                      ->orWhere('cancellation_reason', 'like', "%{$search}%")
                      ->orWhere('problem_description', 'like', "%{$search}%");
                });
            }
            
            // Сортировка
            $query->orderBy($sortBy, $sortOrder);
            
            // Пагинация
            $orders = $query->paginate($perPage);
            
            // Статистика
            $totalOrders = Order::count();
            $trashedOrders = Order::onlyTrashed()->count();
            
            Log::info('Просмотр списка заказов', [
                'total' => $totalOrders,
                'trashed' => $trashedOrders,
                'user_id' => auth()->id()
            ]);
            
            return view('catalog::orders.index', compact(
                'orders', 'totalOrders', 'trashedOrders',
                'search', 'sortBy', 'sortOrder', 'perPage'
            ));
        } catch (\Exception $e) {
            Log::error('Ошибка при получении списка заказов', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', 'Ошибка при загрузке списка заказов');
        }
    }

    /**
     * Отображение формы создания заказа
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            // Получение списка пользователей для выпадающих списков
            $customers = User::where('is_active', true)
                ->orderBy('name')
                ->pluck('name', 'id');
            
            $responsibles = User::where('is_active', true)
                ->orderBy('name')
                ->pluck('name', 'id');
            
            // Генерация номера заказа
            $orderNumber = Order::generateOrderNumber();
            
            Log::info('Открытие формы создания заказа', [
                'order_number' => $orderNumber,
                'user_id' => auth()->id()
            ]);
            
            return view('catalog::orders.create', compact(
                'customers', 'responsibles', 'orderNumber'
            ));
        } catch (\Exception $e) {
            Log::error('Ошибка при открытии формы создания заказа', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', 'Ошибка при открытии формы создания заказа');
        }
    }

    /**
     * Сохранение нового заказа
     * 
     * @param OrdersCreateRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(OrdersCreateRequest $request)
    {
        try {
            // Получаем валидированные данные
            $validated = $request->validated();
            
            // Добавление информации о создателе
            $validated['created_by'] = auth()->id();

            // Создание заказа
            $order = Order::create($validated);

            Log::info('Создан новый заказ', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'user_id' => auth()->id()
            ]);

            return redirect()
                ->route('catalog.orders.index')
                ->with('success', 'Заказ успешно создан');
        } catch (\Exception $e) {
            Log::error('Ошибка при создании заказа', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Ошибка при создании заказа');
        }
    }

    /**
     * Отображение формы редактирования заказа
     * 
     * @param Order $order
     * @return \Illuminate\View\View
     */
    public function edit(Order $order)
    {
        try {
            // Получение списка пользователей для выпадающих списков
            $customers = User::where('is_active', true)
                ->orderBy('name')
                ->pluck('name', 'id');
            
            $responsibles = User::where('is_active', true)
                ->orderBy('name')
                ->pluck('name', 'id');
            
            Log::info('Открытие формы редактирования заказа', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => auth()->id()
            ]);
            
            return view('catalog::orders.edit', compact(
                'order', 'customers', 'responsibles'
            ));
        } catch (\Exception $e) {
            Log::error('Ошибка при открытии формы редактирования заказа', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', 'Ошибка при открытии формы редактирования заказа');
        }
    }

    /**
     * Обновление информации о заказе
     * 
     * @param OrdersEditRequest $request
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(OrdersEditRequest $request, Order $order)
    {
        try {
            // Получаем валидированные данные
            $validated = $request->validated();

            // Добавление информации об обновлении
            $validated['updated_by'] = auth()->id();

            // Обновление заказа
            $order->update($validated);

            Log::info('Обновлен заказ', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'is_cancelled' => $order->is_cancelled,
                'has_problem' => $order->has_problem,
                'user_id' => auth()->id()
            ]);

            return redirect()
                ->route('catalog.orders.index')
                ->with('success', 'Заказ успешно обновлен');
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении заказа', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'user_id' => auth()->id()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Ошибка при обновлении заказа');
        }
    }

    /**
     * Мягкое удаление заказа (в корзину)
     * 
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Order $order)
    {
        try {
            // Заполнение информации об удалении
            $order->deleted_by = auth()->id();
            $order->save();
            
            // Мягкое удаление
            $order->delete();
            
            Log::info('Заказ перемещен в корзину', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => auth()->id()
            ]);
            
            return redirect()
                ->route('catalog.orders.index')
                ->with('success', 'Заказ успешно перемещен в корзину');
        } catch (\Exception $e) {
            Log::error('Ошибка при перемещении заказа в корзину', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', 'Ошибка при удалении заказа');
        }
    }

    /**
     * Отображение корзины удаленных заказов
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function trash(Request $request)
    {
        try {
            // Получение параметров фильтрации
            $search = $request->get('search', '');
            $perPage = $request->get('per_page', 25);
            
            // Построение запроса для удаленных заказов
            $query = Order::onlyTrashed()
                ->with(['customer', 'responsible', 'deleter']);
            
            // Поиск по номеру заказа или комментарию
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhere('comment', 'like', "%{$search}%")
                      ->orWhere('cancellation_reason', 'like', "%{$search}%")
                      ->orWhere('problem_description', 'like', "%{$search}%");
                });
            }
            
            // Сортировка по дате удаления
            $query->orderBy('deleted_at', 'desc');
            
            // Пагинация
            $orders = $query->paginate($perPage);
            
            // Статистика
            $totalOrders = Order::withTrashed()->count();
            $trashedOrders = Order::onlyTrashed()->count();
            
            Log::info('Просмотр корзины заказов', [
                'trashed_count' => $trashedOrders,
                'user_id' => auth()->id()
            ]);
            
            return view('catalog::orders.trash', compact(
                'orders', 'totalOrders', 'trashedOrders',
                'search', 'perPage'
            ));
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке корзины заказов', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', 'Ошибка при загрузке корзины заказов');
        }
    }

    /**
     * Восстановление заказа из корзины
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($id)
    {
        try {
            $order = Order::onlyTrashed()->findOrFail($id);
            
            // Восстановление заказа
            $order->restore();
            
            // Очистка информации об удалении
            $order->deleted_by = null;
            $order->save();
            
            Log::info('Заказ восстановлен из корзины', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => auth()->id()
            ]);
            
            return redirect()
                ->route('catalog.orders.trash.index')
                ->with('success', 'Заказ успешно восстановлен');
        } catch (\Exception $e) {
            Log::error('Ошибка при восстановлении заказа из корзины', [
                'error' => $e->getMessage(),
                'order_id' => $id,
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', 'Ошибка при восстановлении заказа');
        }
    }

    /**
     * Полное удаление заказа из корзины
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forceDelete($id)
    {
        try {
            $order = Order::onlyTrashed()->findOrFail($id);
            
            // Полное удаление
            $order->forceDelete();
            
            Log::info('Заказ полностью удален из корзины', [
                'order_id' => $id,
                'user_id' => auth()->id()
            ]);
            
            return redirect()
                ->route('catalog.orders.trash.index')
                ->with('success', 'Заказ полностью удален');
        } catch (\Exception $e) {
            Log::error('Ошибка при полном удалении заказа', [
                'error' => $e->getMessage(),
                'order_id' => $id,
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', 'Ошибка при полном удалении заказа');
        }
    }

    /**
     * Очистка всей корзины заказов
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function emptyTrash()
    {
        try {
            $trashedOrders = Order::onlyTrashed()->get();
            $count = $trashedOrders->count();
            
            // Полное удаление всех заказов в корзине
            foreach ($trashedOrders as $order) {
                $order->forceDelete();
            }
            
            Log::info('Корзина заказов полностью очищена', [
                'deleted_count' => $count,
                'user_id' => auth()->id()
            ]);
            
            return redirect()
                ->route('catalog.orders.trash.index')
                ->with('success', "Корзина очищена. Удалено заказов: {$count}");
        } catch (\Exception $e) {
            Log::error('Ошибка при очистке корзины заказов', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', 'Ошибка при очистке корзины');
        }
    }

    /**
     * Автоматическая очистка старых заказов из корзины (через 30 дней)
     * 
     * Этот метод должен вызываться через планировщик (Scheduler)
     * 
     * @return int Количество удаленных заказов
     */
    public static function cleanupOldTrash(): int
    {
        try {
            $thirtyDaysAgo = now()->subDays(30);
            
            $oldTrashedOrders = Order::onlyTrashed()
                ->where('deleted_at', '<=', $thirtyDaysAgo)
                ->get();
            
            $count = $oldTrashedOrders->count();
            
            foreach ($oldTrashedOrders as $order) {
                $order->forceDelete();
            }
            
            Log::info('Автоматическая очистка старых заказов из корзины', [
                'deleted_count' => $count,
                'cutoff_date' => $thirtyDaysAgo->toDateTimeString()
            ]);
            
            return $count;
        } catch (\Exception $e) {
            Log::error('Ошибка при автоматической очистке корзины заказов', [
                'error' => $e->getMessage()
            ]);
            
            return 0;
        }
    }
}