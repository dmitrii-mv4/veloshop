<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Modules\Catalog\Models\Basket;
use App\Modules\Catalog\Models\BasketItem;
use App\Modules\Catalog\Models\Customer;
use App\Modules\Catalog\Requests\Basket\AddToBasketRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BasketController
{
    public function addToBasket(AddToBasketRequest $request): JsonResponse
    {
        $user = Auth::user();

        $customer = Customer::where('user_id', $user->id)->first();

        if (! $customer) {
            $customer = new Customer(['user_id' => $user->id]);
        }

        $basket = Basket::firstOrCreate(['customer_id' => $customer->id]);

        $offerId = $request->input('offer_id');
        $quantity = $request->input('quantity');

        $existingItem = $basket->items()->where('offer_id', $offerId)->first();

        if ($existingItem) {
            $existingItem->quantity = ($existingItem->quantity ?? 1) + $quantity;
            $existingItem->save();
            $basket->recalculateTotals(true);

            return response()->json([
                'message' => 'Количество оффера обновлено в корзине.',
                'item' => $existingItem,
                'basket' => $basket,
            ]);
        }

        $item = BasketItem::create([
            'catalog_basket_id' => $basket->id,
            'offer_id' => $offerId,
            'quantity' => $quantity,
        ]);

        $basket->recalculateTotals(true);

        return response()->json([
            'message' => 'Оффер добавлен в корзину.',
            'item' => $item,
            'basket' => $basket,
        ], 201);
    }
}
