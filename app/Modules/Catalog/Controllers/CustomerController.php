<?php

namespace App\Modules\Catalog\Controllers;

use App\Modules\Catalog\Models\Customer;
use App\Modules\Catalog\Requests\Customer\CreateCustomerRequest;
use App\Modules\Catalog\Requests\Customer\UpdateCustomerRequest;
use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    /**
     * Список активных покупателей.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $typeId = $request->get('type_id', 'all');
        $perPage = $request->get('per_page', 20);
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');

        $query = Customer::with(['user', 'type'])->whereNull('deleted_at'); // только активные

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

        $totalCustomers = Customer::count();
        $trashedCount = Customer::onlyTrashed()->count();

        return view('catalog::customers.index', compact(
            'customers',
            'search',
            'typeId',
            'perPage',
            'sortBy',
            'sortOrder',
            'totalCustomers',
            'trashedCount'
        ));
    }

    /**
     * Корзина (удалённые покупатели).
     */
    public function trash(Request $request)
    {
        $search = $request->get('search');
        $typeId = $request->get('type_id', 'all');
        $perPage = $request->get('per_page', 20);
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');

        $query = Customer::onlyTrashed()->with(['user', 'type']);

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
        $trashedCount = Customer::onlyTrashed()->count();

        return view('catalog::customers.trash', compact(
            'customers', 'search', 'typeId', 'perPage',
            'sortBy', 'sortOrder', 'trashedCount'
        ));
    }

    public function create()
    {
        $users = User::all();
        return view('catalog::customers.create', compact( 'users'));
    }

    public function store(CreateCustomerRequest $request)
    {
        $customer = Customer::create($request->only('user_id', 'type_id'));

        Log::info('Покупатель создан', ['id' => $customer->id, 'user_id' => $customer->user_id]);

        return redirect()->route('catalog.customers.index')
            ->with('success', 'Покупатель успешно создан.');
    }

    public function edit($id)
    {
        $customer = Customer::withTrashed()->findOrFail($id);
        $users = User::all();
        return view('catalog::customers.edit', compact('customer', 'users'));
    }

    public function update(UpdateCustomerRequest $request, $id)
    {
        $customer = Customer::withTrashed()->findOrFail($id);
        $customer->update($request->only('user_id', 'type_id'));

        Log::info('Покупатель обновлён', ['id' => $customer->id]);

        return redirect()->route('catalog.customers.index')
            ->with('success', 'Покупатель успешно обновлён.');
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        Log::info('Покупатель перемещён в корзину', ['id' => $customer->id]);

        return redirect()->route('catalog.customers.index')
            ->with('success', 'Покупатель перемещён в корзину.');
    }

    public function restore($id)
    {
        $customer = Customer::onlyTrashed()->findOrFail($id);
        $customer->restore();

        Log::info('Покупатель восстановлен', ['id' => $customer->id]);

        return redirect()->route('catalog.customers.index')
            ->with('success', 'Покупатель восстановлен.');
    }

    public function forceDelete($id)
    {
        $customer = Customer::onlyTrashed()->findOrFail($id);
        $customer->forceDelete();

        Log::info('Покупатель удалён навсегда', ['id' => $id]);

        return redirect()->route('catalog.customers.trash')
            ->with('success', 'Покупатель удалён навсегда.');
    }

    public function forceDeleteAll()
    {
        $count = Customer::onlyTrashed()->count();
        Customer::onlyTrashed()->forceDelete();

        Log::info('Все покупатели в корзине удалены навсегда', ['count' => $count]);

        return redirect()->route('catalog.customers.trash')
            ->with('success', 'Все покупатели в корзине удалены навсегда.');
    }
}
