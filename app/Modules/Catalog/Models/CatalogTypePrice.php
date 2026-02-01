<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/*
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
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class CatalogTypePrice extends Model
{
    /**
     * Имя таблицы в базе данных
     *
     * @var string
     */
    protected $table = 'catalog_type_price';

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
     * Отношение с ценами предложений
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function offerPrices()
    {
        return $this->hasMany(CatalogOfferPrice::class, 'type_price_id');
    }

    /**
     * Получить активные типы цен
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Получить типы цен отсортированные по порядку
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    /**
     * Получить тип цены по техническому идентификатору
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Получить тип основной цены
     *
     * @return \App\Modules\Catalog\Models\CatalogTypePrice|null
     */
    public static function getMainPriceType(): ?self
    {
        try {
            return self::where('type', 'uprice')->active()->first();
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            Log::error('Error getting price types for select', ['error' => $e->getMessage()]);
            return [];
        }
    }
}