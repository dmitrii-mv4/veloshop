<?php

namespace App\Modules\Catalog\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Модель OfferWarehouse
 *
 * Модель наличия товаров на складах.
 * Связывает предложения товаров со складами и указывает количество.
 *
 * @property int $id
 * @property string $offer_id
 * @property int $warehouse_id
 * @property int $count
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class OfferWarehouse extends Model
{
    use OfferWarehouseRelationsTrait, OfferWarehouseScopesTrait;

    /**
     * Имя таблицы в базе данных
     *
     * @var string
     */
    protected $table = 'catalog_offers_warehouses';

    /**
     * Поля, разрешенные для массового заполнения
     *
     * @var array
     */
    protected $fillable = [
        'offer_id',
        'warehouse_id',
        'count',
        'sort_order'
    ];

    /**
     * Атрибуты, которые должны быть приведены к определенным типам
     *
     * @var array
     */
    protected $casts = [
        'count' => 'integer',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Значения по умолчанию для атрибутов модели
     *
     * @var array
     */
    protected $attributes = [
        'count' => 0,
        'sort_order' => 100,
    ];
}
