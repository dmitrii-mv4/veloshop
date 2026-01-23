<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Модель CatalogWarehouseOffer
 *
 * Модель наличия товаров на складах.
 * Связывает предложения товаров со складами и указывает количество.
 *
 * @property string $offer_id
 * @property int $warehouses_id
 * @property int $quantity
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class CatalogWarehouseOffer extends Model
{
    use CatalogWarehouseOfferRelationsTrait, CatalogWarehouseOfferScopesTrait;
    /**
     * Имя таблицы в базе данных
     *
     * @var string
     */
    protected $table = 'catalog_warehouses_offers';

    /**
     * Составной первичный ключ
     *
     * @var array
     */
    protected $primaryKey = ['offer_id', 'warehouses_id'];

    /**
     * Инкрементирование первичного ключа
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Поля, разрешенные для массового заполнения
     *
     * @var array
     */
    protected $fillable = [
        'offer_id',
        'warehouses_id',
        'quantity'
    ];

    /**
     * Атрибуты, которые должны быть приведены к определенным типам
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

}
