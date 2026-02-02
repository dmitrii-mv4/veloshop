<?php

namespace App\Modules\Catalog\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Modules\Catalog\Models\CatalogOfferWarehouse;
use App\Modules\Catalog\Models\CatalogWarehouse;

/**
 * Модель CatalogProductOffer
 *
 * Модель предложений товара (вариаций).
 * Содержит информацию о различных вариантах товара (цвет, размер и т.д.)
 * Атрибуты удалены как отдельная сущность, теперь хранятся в полях модели
 */
class CatalogProductOffer extends Model
{
    use CatalogProductOfferRelationsTrait, CatalogProductOfferScopesTrait;
    
    /**
     * Имя таблицы в базе данных
     *
     * @var string
     */
    protected $table = 'catalog_product_offers';

    /**
     * Первичный ключ таблицы
     *
     * @var string
     */
    protected $primaryKey = 'offer_id';

    /**
     * Тип первичного ключа
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Инкрементирование первичного ключа
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Поля, разрешенные для массового заполнения
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'offer_id',
        'product_id',
        'name',
        'size',
        'color',
        'main_color',
        'vcode',
        'articul_supplier',
        'is_active',
        'sort_order',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'created_by',
        'updated_by'
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
     * Создание нового предложения с логированием
     *
     * @param array $attributes
     * @return static
     * @throws Exception
     */
    public static function createWithLog(array $attributes): static
    {
        try {
            $offer = static::create($attributes);
            Log::info('Product offer created', [
                'offer_id' => $offer->offer_id,
                'product_id' => $offer->product_id,
                'name' => $offer->name
            ]);
            return $offer;
        } catch (Exception $e) {
            Log::error('Error creating product offer', [
                'error' => $e->getMessage(),
                'attributes' => $attributes
            ]);
            throw $e;
        }
    }

    /**
     * Обновление предложения с логированием
     *
     * @param array $attributes
     * @return bool
     * @throws Exception
     */
    public function updateWithLog(array $attributes): bool
    {
        try {
            $result = $this->update($attributes);
            if ($result) {
                Log::info('Product offer updated', [
                    'offer_id' => $this->offer_id,
                    'name' => $this->name
                ]);
            }
            return $result;
        } catch (Exception $e) {
            Log::error('Error updating product offer', [
                'error' => $e->getMessage(),
                'offer_id' => $this->offer_id
            ]);
            throw $e;
        }
    }

    /**
     * Удаление предложения с логированием
     *
     * @return bool|null
     */
    public function deleteWithLog(): ?bool
    {
        try {
            $result = $this->delete();
            if ($result) {
                Log::info('Product offer deleted', [
                    'offer_id' => $this->offer_id,
                    'name' => $this->name
                ]);
            }
            return $result;
        } catch (Exception $e) {
            Log::error('Error deleting product offer', [
                'error' => $e->getMessage(),
                'offer_id' => $this->offer_id
            ]);
            throw $e;
        }
    }

    /**
     * Получение основной цены предложения
     *
     * @param string|null $priceTypeCode
     * @return float|null
     */
    public function getPrice(?string $priceTypeCode = 'uprice'): ?float
    {
        try {
            if (!$priceTypeCode) {
                $priceType = CatalogTypePrice::getMainPriceType();
                if (!$priceType) {
                    return null;
                }
                $priceTypeCode = $priceType->type;
            }

            $priceType = CatalogTypePrice::where('type', $priceTypeCode)->first();
            if (!$priceType) {
                Log::warning('Price type not found', ['type' => $priceTypeCode]);
                return null;
            }

            $price = $this->prices()->where('type_price_id', $priceType->id)->first();
            return $price ? (float) $price->price : null;
        } catch (Exception $e) {
            Log::error('Error getting offer price', [
                'error' => $e->getMessage(),
                'offer_id' => $this->offer_id,
                'price_type' => $priceTypeCode
            ]);
            return null;
        }
    }

    /**
     * Получение атрибута по типу
     *
     * @param string $type
     * @return string|null
     */
    public function getAttributeByType(string $type): ?string
    {
        // Атрибуты теперь хранятся в полях модели, а не в отдельной таблице
        switch ($type) {
            case 'size':
                return $this->size;
            case 'color':
                return $this->color;
            case 'main-color':
                return $this->main_color;
            case 'vcode':
                return $this->vcode;
            case 'articul_supplier':
                return $this->articul_supplier;
            default:
                return null;
        }
    }

    /**
     * Связь с остатками на складах через промежуточную таблицу
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function warehouseOffers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CatalogOfferWarehouse::class, 'offer_id');
    }

    /**
     * Получение всех цен в виде массива с информацией о типах
     *
     * @return array
     */
    public function getPricesArray(): array
    {
        try {
            $prices = [];
            
            foreach ($this->prices as $price) {
                if ($price->typePrice) {
                    $prices[] = [
                        'type_price_id' => $price->type_price_id,
                        'type' => $price->typePrice->type,
                        'title' => $price->typePrice->title,
                        'price' => $price->price,
                        'currency' => $price->typePrice->currency,
                        'formatted' => $price->getPriceWithCurrency()
                    ];
                }
            }
            
            return $prices;
        } catch (Exception $e) {
            Log::error('Error getting prices array', [
                'error' => $e->getMessage(),
                'offer_id' => $this->offer_id
            ]);
            return [];
        }
    }

    /**
     * Получение цен в виде массива для форм
     *
     * @return array
     */
    public function getPricesForForm(): array
    {
        $prices = [];
        
        foreach ($this->prices as $price) {
            $prices[] = [
                'type_price_id' => $price->type_price_id,
                'value' => number_format($price->price, 2, '.', '')
            ];
        }
        
        return $prices;
    }

    /**
     * Получение основной цены с валютой
     *
     * @return string|null
     */
    public function getMainPriceWithCurrency(): ?string
    {
        try {
            $mainType = CatalogTypePrice::getMainPriceType();
            if (!$mainType) {
                return null;
            }

            $price = $this->prices()->where('type_price_id', $mainType->id)->first();
            if (!$price) {
                return null;
            }

            return $price->getPriceWithCurrency();
        } catch (Exception $e) {
            Log::error('Error getting main price with currency', [
                'error' => $e->getMessage(),
                'offer_id' => $this->offer_id
            ]);
            return null;
        }
    }

    /**
     * Получение всех атрибутов в виде массива (теперь атрибуты - это поля модели)
     *
     * @return array
     */
    public function getAttributesArray(): array
    {
        return [
            'size' => $this->size,
            'color' => $this->color,
            'main-color' => $this->main_color, 
            'vcode' => $this->vcode,
            'articul_supplier' => $this->articul_supplier
        ];
    }

    /**
     * Связь со складами через промежуточную таблицу
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function warehouses(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            \App\Modules\Catalog\Models\CatalogWarehouse::class,
            'catalog_offers_warehouses',
            'offer_id',
            'warehouse_id'
        )->withPivot('count')->withTimestamps();
    }

    /**
     * Получение остатков на складах в виде массива
     *
     * @return array
     */
    public function getWarehouseStocksArray(): array
    {
        try {
            $stocks = [];
            
            foreach ($this->warehouseOffers as $stock) {
                if ($stock->warehouse) {
                    $stocks[$stock->warehouse_id] = [
                        'warehouse_id' => $stock->warehouse_id,
                        'title' => $stock->warehouse->title,
                        'count' => $stock->count
                    ];
                }
            }
            
            return $stocks;
        } catch (\Exception $e) {
            Log::error('Error getting warehouse stocks array', [
                'error' => $e->getMessage(),
                'offer_id' => $this->offer_id
            ]);
            return [];
        }
    }

    /**
     * Обновление остатков на складах
     *
     * @param array $warehouseStocks
     * @return bool
     */
    public function updateWarehouseStocks(array $warehouseStocks): bool
    {
        DB::beginTransaction();
        
        try {
            // Получаем текущие остатки
            $currentStocks = $this->warehouseOffers()
                ->pluck('count', 'warehouse_id')
                ->toArray();
            
            $processed = [];
            
            // Обрабатываем каждый склад
            foreach ($warehouseStocks as $warehouseId => $count) {
                $count = (int) $count;
                
                if (isset($currentStocks[$warehouseId])) {
                    // Обновляем существующую запись
                    if ($count > 0) {
                        $this->warehouseOffers()
                            ->where('warehouse_id', $warehouseId)
                            ->update(['count' => $count]);
                        $processed[$warehouseId] = 'updated';
                    } else {
                        // Удаляем запись если количество 0
                        $this->warehouseOffers()
                            ->where('warehouse_id', $warehouseId)
                            ->delete();
                        $processed[$warehouseId] = 'deleted';
                    }
                } else {
                    // Создаем новую запись если количество > 0
                    if ($count > 0) {
                        CatalogOfferWarehouse::create([
                            'offer_id' => $this->offer_id,
                            'warehouse_id' => $warehouseId,
                            'count' => $count
                        ]);
                        $processed[$warehouseId] = 'created';
                    }
                }
            }
            
            // Удаляем записи для складов, которых нет в новом массиве
            $warehousesToDelete = array_diff(array_keys($currentStocks), array_keys($warehouseStocks));
            if (!empty($warehousesToDelete)) {
                $this->warehouseOffers()
                    ->whereIn('warehouse_id', $warehousesToDelete)
                    ->delete();
                
                foreach ($warehousesToDelete as $warehouseId) {
                    $processed[$warehouseId] = 'removed';
                }
            }
            
            DB::commit();
            
            Log::info('Warehouse stocks updated successfully', [
                'offer_id' => $this->offer_id,
                'processed' => $processed,
                'total_warehouses' => count($warehouseStocks)
            ]);
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating warehouse stocks', [
                'error' => $e->getMessage(),
                'offer_id' => $this->offer_id,
                'warehouse_stocks' => $warehouseStocks
            ]);
            
            return false;
        }
    }

    /**
     * Связь с количеством товаров на складах
     *
     * @return HasMany
     */
    public function offerWarehouses(): HasMany
    {
        return $this->hasMany(OfferWarehouse::class, 'offer_id');
    }

    /**
     * Общее количество товара на всех складах
     *
     * @return int
     */
    public function getTotalStockAttribute(): int
    {
        return $this->offerWarehouses()->sum('count');
    }
}