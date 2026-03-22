<?php

namespace App\Modules\Catalog\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Модель CatalogOfferWarehouse
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
class CatalogOfferWarehouse extends Model
{
    use CatalogOfferWarehouseRelationsTrait, CatalogOfferWarehouseScopesTrait;

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

    /**
     * Создание записи об остатке с логированием
     *
     * @param array $attributes
     * @return static
     * @throws Exception
     */
    public static function createWithLog(array $attributes): static
    {
        try {
            $stock = static::create($attributes);
            Log::info('Warehouse stock created', [
                'offer_id' => $stock->offer_id,
                'warehouse_id' => $stock->warehouse_id,
                'count' => $stock->count
            ]);
            return $stock;
        } catch (Exception $e) {
            Log::error('Error creating warehouse stock', [
                'error' => $e->getMessage(),
                'attributes' => $attributes
            ]);
            throw $e;
        }
    }

    /**
     * Обновление записи об остатке с логированием
     *
     * @param array $attributes
     * @return bool
     * @throws Exception
     */
    public function updateWithLog(array $attributes): bool
    {
        try {
            $oldCount = $this->count;
            $result = $this->update($attributes);

            if ($result) {
                Log::info('Warehouse stock updated', [
                    'id' => $this->id,
                    'offer_id' => $this->offer_id,
                    'warehouse_id' => $this->warehouse_id,
                    'old_count' => $oldCount,
                    'new_count' => $this->count
                ]);
            }
            return $result;
        } catch (Exception $e) {
            Log::error('Error updating warehouse stock', [
                'error' => $e->getMessage(),
                'id' => $this->id
            ]);
            throw $e;
        }
    }
}
