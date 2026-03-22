<?php

namespace App\Modules\Catalog\Models;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Модель CatalogTypePrice
 *
 * Модель типов цен в каталоге.
 * Содержит информацию о различных типах цен (основная, оптовая и т.д.)
 *
 * @property int $id
 * @property string $title Название типа цены
 * @property string $type Технический идентификатор типа
 * @property string $currency Валюта
 * @property bool $is_active Активен ли тип
 * @property int $sort_order Порядок сортировки
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class CatalogTypePrice extends Model
{
    use CatalogTypePriceRelationsTrait;

    /**
     * Имя таблицы в базе данных
     *
     * @var string
     */
    protected $table = 'catalog_type_price';

    /**
     * Поля, разрешенные для массового заполнения
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'type',
        'currency',
        'is_active',
        'sort_order'
    ];

    /**
     * Атрибуты, которые должны быть приведены к определенным типам
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Получить активные типы цен
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Получить типы цен отсортированные по порядку
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    /**
     * Получить тип цены по техническому идентификатору
     *
     * @param Builder $query
     * @param string $type
     * @return Builder
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Получить тип основной цены
     *
     * @return CatalogTypePrice|null
     */
    public static function getMainPriceType(): ?self
    {
        try {
            return self::where('type', 'uprice')->active()->first();
        } catch (Exception $e) {
            Log::error('Error getting main price type', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Получить список типов цен для select
     *
     * @return array
     */
    public static function getForSelect(): array
    {
        try {
            return self::active()->ordered()->pluck('title', 'id')->toArray();
        } catch (Exception $e) {
            Log::error('Error getting price types for select', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
