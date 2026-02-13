<?php

namespace App\Modules\Catalog\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель CatalogAttributeValue
 *
 * Модель значений атрибутов каталога.
 * Содержит значения атрибутов для товаров и предложений.
 *
 * @property int $id
 * @property int $attribute_id
 * @property int $attributable_id
 * @property string $attributable_type
 * @property string $value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class CatalogAttributeValue extends Model
{
    /**
     * Имя таблицы в базе данных
     *
     * @var string
     */
    protected $table = 'catalog_attributes_values';

    /**
     * Поля, разрешенные для массового заполнения
     *
     * @var array
     */
    protected $fillable = [
        'attribute_id',
        'attributable_id',
        'attributable_type',
        'value',
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
