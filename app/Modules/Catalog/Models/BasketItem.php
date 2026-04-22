<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Модель элемента корзины
 *
 * @property int $id
 * @property int $basket_id
 * @property int $offer_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class BasketItem extends Model
{
    use BasketItemRelationsTrait;

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
}
