<?php

namespace App\Modules\Catalog\Models;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Модель CatalogWarehouse
 *
 * Модель складов в системе каталога.
 * Содержит информацию о физических складах товаров.
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string|null $contacts
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class CatalogWarehouse extends Model
{
    use CatalogWarehouseRelationsTrait, CatalogWarehouseScopesTrait;

    /**
     * Имя таблицы в базе данных
     *
     * @var string
     */
    protected $table = 'catalog_warehouses';

    /**
     * Поля, разрешенные для массового заполнения
     *
     * @var array
     */
    protected $fillable = [
        'warehouse_id',
        'title',
        'description',
        'contacts',
        'is_active',
        'sort_order',
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
     * Значения по умолчанию для атрибутов модели
     *
     * @var array
     */
    protected $attributes = [
        'is_active' => true,
        'sort_order' => 100,
    ];

    /**
     * Создание нового склада с логированием
     *
     * @throws Exception
     */
    public static function createWithLog(array $attributes): static
    {
        try {
            $warehouse = static::create($attributes);
            Log::info('Warehouse created', [
                'warehouse_id' => $warehouse->id,
                'title' => $warehouse->title,
            ]);

            return $warehouse;
        } catch (Exception $e) {
            Log::error('Error creating warehouse', [
                'error' => $e->getMessage(),
                'attributes' => $attributes,
            ]);
            throw $e;
        }
    }

    /**
     * Обновление склада с логированием
     *
     * @throws Exception
     */
    public function updateWithLog(array $attributes): bool
    {
        try {
            $result = $this->update($attributes);
            if ($result) {
                Log::info('Warehouse updated', [
                    'warehouse_id' => $this->id,
                    'title' => $this->title,
                ]);
            }

            return $result;
        } catch (Exception $e) {
            Log::error('Error updating warehouse', [
                'error' => $e->getMessage(),
                'warehouse_id' => $this->id,
            ]);
            throw $e;
        }
    }

    /**
     * Удаление склада с логированием
     */
    public function deleteWithLog(): ?bool
    {
        try {
            // Проверяем, есть ли связанные остатки
            if ($this->warehouseOffers()->count() > 0) {
                throw new Exception('Cannot delete warehouse with existing stock records');
            }

            $result = $this->delete();
            if ($result) {
                Log::info('Warehouse deleted', [
                    'warehouse_id' => $this->id,
                    'title' => $this->title,
                ]);
            }

            return $result;
        } catch (Exception $e) {
            Log::error('Error deleting warehouse', [
                'error' => $e->getMessage(),
                'warehouse_id' => $this->id,
            ]);
            throw $e;
        }
    }

    /**
     * Получение общего количества товаров на складе
     */
    public function getTotalQuantity(): int
    {
        return (int) $this->warehouseOffers()->sum('count');
    }

    /**
     * Получение уникальных предложений на складе
     */
    public function getUniqueOffers(): Collection
    {
        return $this->warehouseOffers()->with('offer')->get()->unique('offer_id');
    }

    /**
     * Получение количества уникальных предложений на складе
     */
    public function getUniqueOffersCountAttribute(): int
    {
        return $this->warehouseOffers()->distinct('offer_id')->count('offer_id');
    }

    /**
     * Получение общего количества единиц товара на складе
     */
    public function getTotalProductsCountAttribute(): int
    {
        return $this->warehouseOffers()->sum('count');
    }

    /**
     * Получение всех активных складов
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllActive()
    {
        try {
            // Используем обычный запрос без scope, так как scope active() не существует
            return self::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->orderBy('title', 'asc')
                ->get();
        } catch (Exception $e) {
            Log::error('Error getting all active warehouses', [
                'error' => $e->getMessage(),
            ]);

            return collect();
        }
    }

    /**
     * Получение всех складов с пагинацией
     *
     * @param  int  $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public static function getAllPaginated($perPage = 25)
    {
        return self::active()->ordered()->paginate($perPage);
    }
}
