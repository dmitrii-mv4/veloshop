<?php

namespace App\Modules\Catalog\Models;

use App\Core\Models\TableNameTrait;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

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
class Basket extends Model
{
    use BasketRelationsTrait, TableNameTrait;

    protected $table = 'catalog_baskets';

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
        'total_price' => 'float',
        'total_quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Пересчёт итоговых значений корзины (total_price, total_quantity)
     * на основе текущих цен офферов.
     *
     * @param  bool  $save  Сохранить ли модель после пересчёта
     */
    public function recalculateTotals(bool $save = true): bool
    {
        try {
            $totalQuantity = 0;
            $totalPrice = 0;

            foreach ($this->items as $item) {
                $price = $item->offer->getPrice();
                $totalPrice += $price * $item->quantity;
                $totalQuantity += $item->quantity;
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
     */
    public function addToBasket(int $offerID, int $quantity): void
    {
        if ($quantity === 0) {
            return;
        }

        $existingItem = $this->items()->where('offer_id', $offerID)->first();
        if ($existingItem) {
            $newQty = $existingItem->quantity + $quantity;
            if ($newQty < 1) {
                $existingItem->delete();
            } else {
                $existingItem->quantity = $newQty;
                $existingItem->save();
            }
        } else {
            if ($quantity > 0) {
                $this->items()->create([
                    'offer_id' => $offerID,
                    'quantity' => $quantity,
                ]);
            }
        }

        $this->recalculateTotals();
    }
}
