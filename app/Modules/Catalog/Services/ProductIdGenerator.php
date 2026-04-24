<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\Product;
use Illuminate\Support\Facades\Log;

/**
 * Сервис генерации уникальных идентификаторов товаров
 *
 * Предоставляет методы для генерации уникальных ID товаров и предложений
 */
class ProductIdGenerator
{
    /**
     * Префиксы для различных типов идентификаторов
     */
    const PRODUCT_PREFIX = 'U';

    const OFFER_PREFIX = 'HQ-';

    /**
     * Генерирует уникальный ID товара
     */
    public function generateProductId(string $prefix = self::PRODUCT_PREFIX): string
    {
        try {
            do {
                $productId = $prefix.str_pad(mt_rand(1, 99999999999), 11, '0', STR_PAD_LEFT);
            } while (Product::where('product_id', $productId)->exists());

            Log::info('Product ID generated', ['product_id' => $productId]);

            return $productId;
        } catch (\Exception $e) {
            Log::error('Error generating product ID', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Генерирует уникальный ID предложения
     */
    public function generateOfferId(string $prefix = self::OFFER_PREFIX): string
    {
        try {
            do {
                $offerId = $prefix.str_pad(mt_rand(1, 9999999), 7, '0', STR_PAD_LEFT);
            } while (Offer::where('offer_id', $offerId)->exists());

            Log::info('Offer ID generated', ['offer_id' => $offerId]);

            return $offerId;
        } catch (\Exception $e) {
            Log::error('Error generating offer ID', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Проверяет валидность ID товара
     */
    public function isValidProductId(string $productId): bool
    {
        // Проверяем формат: U + 11 цифр
        return preg_match('/^U\d{11}$/', $productId) === 1;
    }

    /**
     * Проверяет валидность ID предложения
     */
    public function isValidOfferId(string $offerId): bool
    {
        // Проверяем формат: HQ- + 7 цифр
        return preg_match('/^HQ-\d{7}$/', $offerId) === 1;
    }

    /**
     * Генерирует несколько уникальных ID товаров
     */
    public function generateMultipleProductIds(int $count = 10, string $prefix = self::PRODUCT_PREFIX): array
    {
        try {
            $ids = [];
            for ($i = 0; $i < $count; $i++) {
                $ids[] = $this->generateProductId($prefix);
            }

            Log::info('Multiple product IDs generated', ['count' => $count]);

            return $ids;
        } catch (\Exception $e) {
            Log::error('Error generating multiple product IDs', [
                'error' => $e->getMessage(),
                'count' => $count,
            ]);
            throw $e;
        }
    }
}
