<?php

namespace App\Modules\Catalog\Controllers;

use App\Modules\Catalog\Models\CustomerType;
use App\Modules\Catalog\Requests\Customer\CreateCustomerTypeRequest;
use App\Modules\Catalog\Requests\Customer\UpdateCustomerTypeRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class CustomerTypeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 20);
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');

        $query = CustomerType::query();

        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        $types = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);

        $totalTypes = CustomerType::count();
        $trashedCount = CustomerType::onlyTrashed()->count();

        return view('catalog::customers.type.index', compact(
            'types', 'search', 'perPage', 'sortBy', 'sortOrder',
            'totalTypes', 'trashedCount'
        ));
    }

    public function trash(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 20);
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');

        $query = CustomerType::onlyTrashed();

        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        $types = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);
        $trashedCount = CustomerType::onlyTrashed()->count();

        return view('catalog::customers.type.trash', compact(
            'types', 'search', 'perPage', 'sortBy', 'sortOrder', 'trashedCount'
        ));
    }

    public function create()
    {
        return view('catalog::customers.type.create');
    }

    public function store(CreateCustomerTypeRequest $request)
    {
        $data = $request->only('title');
        $data['is_active'] = $request->has('is_active'); // true, если чекбокс отмечен

        $type = CustomerType::create($data);

        Log::info('Тип покупателя создан', [
            'id'        => $type->id,
            'title'     => $type->title,
            'is_active' => $type->is_active,
        ]);

        return redirect()->route('catalog.customers.type.index')
            ->with('success', 'Тип покупателя успешно создан.');
    }

    public function edit($id)
    {
        $type = CustomerType::findOrFail($id);
        return view('catalog::customers.type.edit', compact('type'));
    }

    public function update(UpdateCustomerTypeRequest $request, $id)
    {
        $type = CustomerType::findOrFail($id);

        $data = $request->only('title');
        $data['is_active'] = $request->has('is_active');

        $type->update($data);

        Log::info('Тип покупателя обновлён', [
            'id'        => $type->id,
            'is_active' => $type->is_active,
        ]);

        return redirect()->route('catalog.customers.type.index')
            ->with('success', 'Тип покупателя успешно обновлён.');
    }

    public function destroy($id)
    {
        $type = CustomerType::findOrFail($id);
        $type->delete();

        Log::info('Тип покупателя перемещён в корзину', ['id' => $type->id]);

        return redirect()->route('catalog.customers.type.index')
            ->with('success', 'Тип покупателя перемещён в корзину.');
    }

    public function restore($id)
    {
        $type = CustomerType::onlyTrashed()->findOrFail($id);
        $type->restore();

        Log::info('Тип покупателя восстановлен', ['id' => $type->id]);

        return redirect()->route('catalog.customers.type.index')
            ->with('success', 'Тип покупателя восстановлен.');
    }

    public function forceDelete($id)
    {
        $type = CustomerType::onlyTrashed()->findOrFail($id);
        $type->forceDelete();

        Log::info('Тип покупателя удалён навсегда', ['id' => $id]);

        return redirect()->route('catalog.customers.type.trash')
            ->with('success', 'Тип покупателя удалён навсегда.');
    }

    public function forceDeleteAll()
    {
        $count = CustomerType::onlyTrashed()->count();
        CustomerType::onlyTrashed()->forceDelete();

        Log::info('Все типы в корзине удалены навсегда', ['count' => $count]);

        return redirect()->route('catalog.customers.type.trash')
            ->with('success', 'Все типы покупателей в корзине удалены навсегда.');
    }
}