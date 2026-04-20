<?php

namespace App\Modules\Catalog\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Модель элемента корзины
 *
 * @property int $id
 * @property int $basket_id
 * @property int $offer_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class CatalogBasketItem extends Model
{
    use CatalogBasketItemRelationsTrait;

    /**
     * Таблица, связанная с моделью.
     *
     * @var string
     */
    protected $table = 'catalog_basket_items';

    /**
     * Поля, разрешённые для массового заполнения.
     *
     * @var array
     */
    protected $fillable = [
        'basket_id',
        'offer_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Безопасное удаление с логированием.
     * @throws Exception
     */
    public function deleteWithLog(): ?bool
    {
        try {
            $result = $this->delete();
            if ($result) {
                Log::info('Basket item deleted', [
                    'basket_item_id' => $this->id,
                    'basket_id' => $this->catalog_basket_id,
                    'offer_id' => $this->offer_id,
                ]);
            }

            return $result;
        } catch (Exception $e) {
            Log::error('Error deleting basket item', [
                'basket_item_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
