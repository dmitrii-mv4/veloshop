<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Модель элемента корзины (связь корзины с оффером)
 *
 * @property int $id
 * @property int $catalog_basket_id
 * @property int $offer_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read CatalogBasket $basket
 * @property-read CatalogProductOffer $offer
 */
class CatalogBasketItem extends Model
{
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
        'catalog_basket_id',
        'offer_id',
    ];

    /**
     * Корзина, к которой относится элемент.
     *
     * @return BelongsTo
     */
    public function basket(): BelongsTo
    {
        return $this->belongsTo(CatalogBasket::class, 'catalog_basket_id');
    }

    /**
     * Оффер (предложение товара).
     *
     * @return BelongsTo
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(CatalogProductOffer::class, 'offer_id', 'offer_id');
    }

    /**
     * Безопасное удаление с логированием.
     *
     * @return bool|null
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