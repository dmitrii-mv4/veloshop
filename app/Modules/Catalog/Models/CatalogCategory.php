<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Модель CatalogCategory
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $code
 * @property string $external_id
 * @property int $parent_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class CatalogCategory extends Model
{
    use CatalogCategoryRelationsTrait;

    /**
     * Поля, разрешенные для массового заполнения
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'code',
        'external_id',
        'parent_id',
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
