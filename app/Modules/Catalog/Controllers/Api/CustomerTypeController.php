<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Modules\Catalog\Models\CustomerType;
use App\Modules\Catalog\Requests\Customer\CreateCustomerTypeRequest;
use App\Modules\Catalog\Requests\Customer\UpdateCustomerTypeRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * API контроллер для работы с типами покупателей
 *
 * Предоставляет CRUD операции для справочника типов (физлицо/юрлицо).
 */
class CustomerTypeController extends Controller
{
    /**
     * Получить список типов покупателей с фильтрацией и пагинацией.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 20);
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $trashed = $request->get('trashed', ''); // '', 'with', 'only'

        $query = CustomerType::query();

        if ($trashed === 'with') {
            $query->withTrashed();
        } elseif ($trashed === 'only') {
            $query->onlyTrashed();
        }

        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        $types = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);

        Log::info('API запрос списка типов покупателей', [
            'filters' => $request->only(['search', 'trashed']),
            'total' => $types->total()
        ]);

        return response()->json([
            'data' => $types->items(),
            'meta' => [
                'current_page' => $types->currentPage(),
                'last_page' => $types->lastPage(),
                'per_page' => $types->perPage(),
                'total' => $types->total(),
            ],
        ]);
    }

    /**
     * Получить детальную информацию о типе покупателя.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $type = CustomerType::withTrashed()->findOrFail($id);

        Log::info('API запрос типа покупателя', ['id' => $id]);

        return response()->json(['data' => $type]);
    }

    /**
     * Создать новый тип покупателя.
     *
     * @param CreateCustomerTypeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateCustomerTypeRequest $request)
    {
        $type = CustomerType::create($request->only('title'));

        Log::info('API: Тип покупателя создан', [
            'id' => $type->id,
            'title' => $type->title
        ]);

        return response()->json([
            'message' => 'Тип покупателя успешно создан.',
            'data' => $type,
        ], 201);
    }

    /**
     * Обновить данные типа покупателя.
     *
     * @param UpdateCustomerTypeRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateCustomerTypeRequest $request, $id)
    {
        $type = CustomerType::findOrFail($id);
        $type->update($request->only('title'));

        Log::info('API: Тип покупателя обновлён', ['id' => $type->id]);

        return response()->json([
            'message' => 'Тип покупателя успешно обновлён.',
            'data' => $type,
        ]);
    }

    /**
     * Мягкое удаление типа покупателя (перемещение в корзину).
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $type = CustomerType::findOrFail($id);
        $type->delete();

        Log::info('API: Тип покупателя перемещён в корзину', ['id' => $type->id]);

        return response()->json([
            'message' => 'Тип покупателя перемещён в корзину.',
        ]);
    }

    /**
     * Восстановить тип покупателя из корзины.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        $type = CustomerType::onlyTrashed()->findOrFail($id);
        $type->restore();

        Log::info('API: Тип покупателя восстановлен', ['id' => $type->id]);

        return response()->json([
            'message' => 'Тип покупателя восстановлен.',
            'data' => $type,
        ]);
    }

    /**
     * Принудительное удаление типа покупателя (навсегда).
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDelete($id)
    {
        $type = CustomerType::onlyTrashed()->findOrFail($id);
        $type->forceDelete();

        Log::info('API: Тип покупателя удалён навсегда', ['id' => $id]);

        return response()->json([
            'message' => 'Тип покупателя удалён навсегда.',
        ]);
    }
}