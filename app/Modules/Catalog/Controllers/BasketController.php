<?php

namespace App\Modules\Catalog\Controllers;

use App\Modules\Catalog\Models\Basket;
use App\Modules\Catalog\Models\Customer;
use App\Modules\Catalog\Requests\Basket\CreateBasketRequest;
use App\Modules\Catalog\Requests\Basket\UpdateBasketRequest;
use App\Modules\User\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class BasketController extends Controller
{
    /**
     * Создание экземпляра контроллера с проверкой прав доступа.
     */
    public function __construct()
    {
        //
    }

    /**
     * Список корзин.
     */
    public function index(Request $request): View
    {
        $query = Basket::with(['user', 'customer', 'creator', 'updater', 'items.offer']);

        // Поиск по пользователю (имя, email)
        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('customer', function ($q) use ($search) {
                // предполагаем, что в модели Customer есть поле name или связь с user
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        // Фильтр по пользователю (если передан user_id)
        if ($userId = $request->input('user_id')) {
            if ($userId !== 'all') {
                $query->where('user_id', $userId);
            }
        }

        // Фильтр по покупателю (customer_id)
        if ($customerId = $request->input('customer_id')) {
            if ($customerId !== 'all') {
                $query->where('customer_id', $customerId);
            }
        }

        // Сортировка
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'desc');
        $allowedSorts = ['id', 'total_price', 'total_quantity', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('id', 'desc');
        }

        // Пагинация
        $perPage = $request->input('per_page', 20);
        $baskets = $query->paginate($perPage)->withQueryString();

        // Данные для фильтров
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        $customers = Customer::with('user')->get(); // для фильтрации по покупателям

        return view('catalog::basket.index', compact(
            'baskets',
            'users',
            'customers',
            'search',
            'userId',
            'customerId',
            'sortBy',
            'sortOrder',
            'perPage'
        ));
    }

    /**
     * Форма создания новой корзины.
     */
    public function create(): View
    {
        $users = User::orderBy('name')->get();
        $customers = Customer::with('user')->get();
        $offers = Offer::with('product')->where('is_active', true)->get();

        return view('catalog::basket.edit', [
            'basket' => null,
            'users' => $users,
            'customers' => $customers,
            'offers' => $offers,
            'selectedOffers' => [],
        ]);
    }

    /**
     * Сохранение новой корзины.
     */
    public function store(CreateBasketRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Создаём корзину
        $basket = Basket::create([
            'customer_id' => $validated['customer_id'],
        ]);

        // Прикрепляем офферы
        if (! empty($validated['offers'])) {
            foreach ($validated['offers'] as $offerId) {
                $basket->addToBasket($offerId);
            }
        }

        Log::info('Basket created', ['basket_id' => $basket->id, 'user_id' => Auth::id()]);

        return redirect()
            ->route('catalog.basket.edit', $basket->id)
            ->with('success', 'Корзина успешно создана.');
    }

    /**
     * Форма редактирования корзины.
     */
    public function edit(int $id): View
    {
        $basket = Basket::with(['items.offer.product'])->findOrFail($id);

        $users = User::orderBy('name')->get();
        $customers = Customer::with('user')->get();
        $offers = Offer::with('product')->where('is_active', true)->get();

        $selectedOffers = $basket->items->pluck('offer_id')->toArray();

        return view('catalog::basket.edit', compact('basket', 'users', 'customers', 'offers', 'selectedOffers'));
    }

    /**
     * Обновление корзины.
     */
    public function update(UpdateBasketRequest $request, int $id): RedirectResponse
    {
        $basket = Basket::findOrFail($id);
        $validated = $request->validated();

        // Обновляем основные поля
        $basket->user_id = $validated['user_id'] ?? null;
        $basket->customer_id = $validated['customer_id'] ?? null;
        $basket->save();

        // Синхронизируем офферы
        $newOfferIds = $validated['offers'] ?? [];

        // Текущие ID офферов в корзине
        $currentOfferIds = $basket->items->pluck('offer_id')->toArray();

        // Определяем, какие добавить, какие удалить
        $toAdd = array_diff($newOfferIds, $currentOfferIds);
        $toRemove = array_diff($currentOfferIds, $newOfferIds);

        foreach ($toAdd as $offerId) {
            $basket->addToBasket($offerId);
        }

        foreach ($toRemove as $offerId) {
            $basket->removeOffer($offerId);
        }

        // Пересчёт итогов уже выполнен внутри addToBasket/removeOffer

        Log::info('Basket updated', ['basket_id' => $basket->id, 'user_id' => Auth::id()]);

        return redirect()
            ->route('catalog.basket.edit', $basket->id)
            ->with('success', 'Корзина успешно обновлена.');
    }

    /**
     * Удаление корзины.
     */
    public function destroy(int $id): RedirectResponse
    {
        $basket = Basket::findOrFail($id);
        $basket->delete();

        Log::info('Basket deleted', ['basket_id' => $id, 'user_id' => Auth::id()]);

        return redirect()
            ->route('catalog.basket.index')
            ->with('success', 'Корзина удалена.');
    }
}
