<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель CatalogOffersAttribute
 *
 * Модель атрибутов для предложений товара.
 * Содержит характеристики предложений (цвет, размер и т.д.)
 *
 * @property int $id
 * @property string $offer_id
 * @property string $attributes_type
 * @property string $attributes_value
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class CatalogOfferAttribute extends Model
{
    use CatalogOfferAttributeRelationsTrait, CatalogOfferAttributeScopesTrait;
    /**
     * Имя таблицы в базе данных
     *
     * @var string
     */
    protected $table = 'catalog_offers_attributes';

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
        'attributes_type',
        'attributes_value'
    ];

    /**
     * Атрибуты, которые должны быть приведены к определенным типам
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

}
