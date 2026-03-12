<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Modules\Catalog\Models\Customer;
use App\Modules\Catalog\Requests\Customer\CreateCustomerRequest;
use App\Modules\Catalog\Requests\Customer\UpdateCustomerRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Контроллер API для работы с покупателями
 * 
 * Предоставляет методы для CRUD операций и управления корзиной.
 */
class CustomersController extends Controller
{
    /**
     * Получить список покупателей с фильтрацией и пагинацией.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $typeId = $request->get('type_id', 'all');
        $perPage = $request->get('per_page', 20);
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $trashed = $request->get('trashed', ''); // '', 'with', 'only'

        $query = Customer::with(['user', 'type']);

        if ($trashed === 'with') {
            $query->withTrashed();
        } elseif ($trashed === 'only') {
            $query->onlyTrashed();
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($typeId && $typeId !== 'all') {
            $query->where('type_id', $typeId);
        }

        $customers = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);

        Log::info('API запрос списка покупателей', [
            'filters' => $request->only(['search', 'type_id', 'trashed']),
            'total' => $customers->total()
        ]);

        return response()->json([
            'data' => $customers->items(),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
            ],
        ]);
    }

    /**
     * Получить детальную информацию о покупателе.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $customer = Customer::with(['user', 'type', 'creator', 'updater', 'deleter'])
            ->withTrashed()
            ->findOrFail($id);

        Log::info('API запрос покупателя', ['id' => $id]);

        return response()->json(['data' => $customer]);
    }

    /**
     * Создать нового покупателя.
     *
     * @param CreateCustomerRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateCustomerRequest $request)
    {
        $customer = Customer::create($request->only('user_id', 'type_id'));

        Log::info('API: Покупатель создан', [
            'id' => $customer->id,
            'user_id' => $customer->user_id,
            'type_id' => $customer->type_id
        ]);

        return response()->json([
            'message' => 'Покупатель успешно создан.',
            'data' => $customer->load(['user', 'type']),
        ], 201);
    }

    /**
     * Обновить данные покупателя.
     *
     * @param UpdateCustomerRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateCustomerRequest $request, $id)
    {
        $customer = Customer::withTrashed()->findOrFail($id);
        $customer->update($request->only('user_id', 'type_id'));

        Log::info('API: Покупатель обновлён', ['id' => $customer->id]);

        return response()->json([
            'message' => 'Покупатель успешно обновлён.',
            'data' => $customer->load(['user', 'type']),
        ]);
    }

    /**
     * Мягкое удаление покупателя (перемещение в корзину).
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        Log::info('API: Покупатель перемещён в корзину', ['id' => $customer->id]);

        return response()->json([
            'message' => 'Покупатель перемещён в корзину.',
        ]);
    }

    /**
     * Восстановить покупателя из корзины.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        $customer = Customer::onlyTrashed()->findOrFail($id);
        $customer->restore();

        Log::info('API: Покупатель восстановлен', ['id' => $customer->id]);

        return response()->json([
            'message' => 'Покупатель восстановлен.',
            'data' => $customer->load(['user', 'type']),
        ]);
    }

    /**
     * Принудительное удаление покупателя (навсегда).
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDelete($id)
    {
        $customer = Customer::onlyTrashed()->findOrFail($id);
        $customer->forceDelete();

        Log::info('API: Покупатель удалён навсегда', ['id' => $id]);

        return response()->json([
            'message' => 'Покупатель удалён навсегда.',
        ]);
    }
}