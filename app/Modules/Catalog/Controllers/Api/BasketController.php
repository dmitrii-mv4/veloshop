<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Modules\Catalog\Models\Basket;
use App\Modules\Catalog\Models\Customer;
use App\Modules\Catalog\Requests\Basket\AddToBasketRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BasketController
{
    public function addToBasket(AddToBasketRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $customer = Customer::firstOrCreate(['user_id' => $user->id]);
            $basket = Basket::firstOrCreate(['customer_id' => $customer->id]);
            $offerID = $request->input('offer_id');
            $quantity = $request->input('quantity');
            $basket->addToBasket($offerID, $quantity);

            return response()->json([
                'success' => true,
                'basket' => $basket,
            ]);
        } catch (Exception $e) {
            Log::error('Error adding to basket', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Что-то пошло не так при обновлении корзины.',
            ], 500);
        }
    }
}
