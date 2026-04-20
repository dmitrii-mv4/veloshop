<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Модель корзины покупателя
 *
 * @property int $id
 * @property int $customer_id
 * @property float $total_price
 * @property int $total_quantity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class CatalogBasket extends Model
{
    use CatalogBasketRelationsTrait;

    /**
     * Поля, разрешённые для массового заполнения.
     *
     * @var array
     */
    protected $fillable = [
        'customer_id',
        'total_price',
        'total_quantity',
    ];

    /**
     * Приведение типов.
     *
     * @var array
     */
    protected $casts = [
        'total_price'       => 'float',
        'total_quantity'    => 'integer',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    /**
     * Пересчёт итоговых значений корзины (total_price, total_quantity)
     * на основе текущих цен офферов.
     *
     * @param bool $save Сохранить ли модель после пересчёта
     * @return bool
     */
    public function recalculateTotals(bool $save = true): bool
    {
        try {
            $totalQuantity = 0;
            $totalPrice = 0;

            foreach ($this->items as $item) {
                if ($item->offer) {
                    $price = $item->offer->getPrice(); // получаем текущую цену оффера
                    $totalPrice += $price;
                    $totalQuantity++;
                }
            }

            $this->total_price = $totalPrice;
            $this->total_quantity = $totalQuantity;

            if ($save) {
                $result = $this->save();
                Log::info('Basket totals recalculated', [
                    'basket_id' => $this->id,
                    'total_price' => $totalPrice,
                    'total_quantity' => $totalQuantity,
                ]);
                return $result;
            }

            return true;
        } catch (Exception $e) {
            Log::error('Error recalculating basket totals', [
                'basket_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Добавление оффера в корзину.
     *
     * @param int $offerId
     * @return CatalogBasketItem|null
     */
    public function addOffer(int $offerId): ?CatalogBasketItem
    {
        try {
            // Проверяем, есть ли уже такой оффер
            $existing = $this->items()->where('offer_id', $offerId)->first();
            if ($existing) {
                Log::info('Offer already in basket', [
                    'basket_id' => $this->id,
                    'offer_id' => $offerId,
                ]);
                return $existing;
            }

            $item = $this->items()->create([
                'offer_id' => $offerId,
            ]);

            $this->recalculateTotals(true);

            Log::info('Offer added to basket', [
                'basket_id' => $this->id,
                'offer_id' => $offerId,
            ]);

            return $item;
        } catch (Exception $e) {
            Log::error('Error adding offer to basket', [
                'basket_id' => $this->id,
                'offer_id' => $offerId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Удаление оффера из корзины.
     *
     * @param int $offerId
     * @return bool
     */
    public function removeOffer(int $offerId): bool
    {
        try {
            $deleted = $this->items()->where('offer_id', $offerId)->delete();
            if ($deleted) {
                $this->recalculateTotals(true);
                Log::info('Offer removed from basket', [
                    'basket_id' => $this->id,
                    'offer_id' => $offerId,
                ]);
            }
            return (bool)$deleted;
        } catch (Exception $e) {
            Log::error('Error removing offer from basket', [
                'basket_id' => $this->id,
                'offer_id' => $offerId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
