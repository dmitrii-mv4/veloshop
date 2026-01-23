<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель CatalogOffersPrice
 *
 * Модель цен для предложений товара.
 * Содержит различные типы цен для каждого предложения.
 *
 * @property int $id
 * @property string $offer_id
 * @property string $price_type
 * @property float $price
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class CatalogOfferPrice extends Model
{
    /**
     * Имя таблицы в базе данных
     *
     * @var string
     */
    protected $table = 'catalog_offers_prices';

    /**
     * Первичный ключ таблицы
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Инкрементирование первичного ключа
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Поля, разрешенные для массового заполнения
     *
     * @var array
     */
    protected $fillable = [
        'offer_id',
        'price_type',
        'price'
    ];

    /**
     * Атрибуты, которые должны быть приведены к определенным типам
     *
     * @var array
     */
    protected $casts = [
        'price' => 'float',
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
        return $this->belongsTo(CatalogProductOffer::class, 'offer_id', 'offer_id');
    }
}
