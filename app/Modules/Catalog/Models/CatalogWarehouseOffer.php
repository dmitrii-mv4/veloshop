<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель CatalogWarehouseOffer
 *
 * Модель наличия товаров на складах.
 * Связывает предложения товаров со складами и указывает количество.
 *
 * @property string $offers_id
 * @property int $warehouses_id
 * @property int $quantity
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class CatalogWarehouseOffer extends Model
{
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
    protected $primaryKey = ['offers_id', 'warehouses_id'];

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
        'offers_id',
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

    /**
     * Отношение с предложением товара
     *
     * @return BelongsTo
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(CatalogProductOffer::class, 'offers_id', 'offers_id');
    }

    /**
     * Отношение со складом
     *
     * @return BelongsTo
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(CatalogWarehouse::class, 'warehouses_id', 'id');
    }
}
