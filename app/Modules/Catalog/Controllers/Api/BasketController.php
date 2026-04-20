<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Modules\Catalog\Models\Basket;
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

        $item = $basket->addToBasket($offerId, $quantity);

        if ($item) {
            return response()->json([
                'success' => true,
                'message' => 'Оффер добавлен в корзину.',
                'basketItem' => $item,
                'basket' => $basket,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Что-то пошло не так при добавлении в корзину.',
                'basket' => $basket,
            ], 500);
        }
    }
}
