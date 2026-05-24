<?php

namespace App\Modules\Catalog\Models;

use App\Core\Models\TableNameTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Модель Attribute
 *
 * Модель атрибутов каталога.
 * Содержит информацию об атрибутах товаров и предложений.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Attribute extends Model
{
    use AttributeRelationsTrait, TableNameTrait;

    /**
     * Имя таблицы в базе данных
     *
     * @var string
     */
    protected $table = 'catalog_attributes';

    /**
     * Поля, разрешенные для массового заполнения
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
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
