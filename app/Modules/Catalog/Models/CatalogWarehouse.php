<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

/**
 * Модель CatalogWarehouse
 * 
 * Модель складов в системе каталога.
 * Содержит информацию о физических складах товаров.
 * 
 * @property int $id
 * @property string $address
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $operating_mode
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class CatalogWarehouse extends Model
{
    /**
     * Имя таблицы в базе данных
     *
     * @var string
     */
    protected $table = 'catalog_warehouses';

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
        'address',
        'phone',
        'email',
        'operating_mode',
        'description'
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

    /**
     * Отношение с наличием товаров на складе
     *
     * @return HasMany
     */
    public function warehouseOffers(): HasMany
    {
        return $this->hasMany(CatalogWarehouseOffer::class, 'warehouses_id', 'id');
    }

    /**
     * Создание нового склада с логированием
     *
     * @param array $attributes
     * @return static
     */
    public static function createWithLog(array $attributes)
    {
        try {
            $warehouse = static::create($attributes);
            Log::info('Warehouse created', ['warehouse_id' => $warehouse->id, 'address' => $warehouse->address]);
            return $warehouse;
        } catch (\Exception $e) {
            Log::error('Error creating warehouse', ['error' => $e->getMessage(), 'attributes' => $attributes]);
            throw $e;
        }
    }

    /**
     * Получение общего количества товаров на складе
     *
     * @return int
     */
    public function getTotalQuantity(): int
    {
        return (int) $this->warehouseOffers()->sum('quantity');
    }

    /**
     * Получение уникальных предложений на складе
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUniqueOffers()
    {
        return $this->warehouseOffers()->with('offer')->get()->unique('offers_id');
    }
}