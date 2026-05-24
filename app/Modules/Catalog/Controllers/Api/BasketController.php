<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Modules\Catalog\Models\Basket;
use App\Modules\Catalog\Models\Customer;
use App\Modules\Catalog\Requests\Basket\AddToBasketRequest;
use App\Modules\Catalog\Resources\BasketResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BasketController
{

    public function getBasket(): JsonResponse
    {
        try {
            $user = Auth::user();
            $customer = Customer::firstOrCreate(['user_id' => $user->id]);
            $basket = Basket::firstOrCreate(['customer_id' => $customer->id]);

            return response()->json([
                'success' => true,
                'basket' => BasketResource::make($basket),
            ]);
        } catch (Exception $e) {
            Log::error('Error getting a basket', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Что-то пошло не так при получении корзины.',
            ], 500);
        }
    }

    public function clearBasket(): JsonResponse
    {
        try {
            $user = Auth::user();
            $customer = Customer::firstOrCreate(['user_id' => $user->id]);
            $basket = Basket::firstOrCreate(['customer_id' => $customer->id]);
            $basket->items()->delete();

            return response()->json([
                'success' => true,
                'basket' => BasketResource::make($basket),
            ]);
        } catch (Exception $e) {
            Log::error('Error clearing a basket', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Что-то пошло не так при очищении корзины.',
            ], 500);
        }
    }

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
                'basket' => BasketResource::make($basket),
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
