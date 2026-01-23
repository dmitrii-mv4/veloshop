<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

/**
 * Модель CatalogProductOffer
 *
 * Модель предложений товара (вариаций).
 * Содержит информацию о различных вариантах товара (цвет, размер и т.д.)
 */
class CatalogProductOffer extends Model
{
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
    protected $primaryKey = 'offers_id';

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
        'offers_id',
        'product_id',
        'articul_supplier',
        'name',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'updated_by',
        'created_by'
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
     * Отношение с товаром
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    /**
     * Отношение с ценами предложения
     *
     * @return HasMany
     */
    public function prices(): HasMany
    {
        return $this->hasMany(CatalogOffersPrice::class, 'offers_id', 'offers_id');
    }

    /**
     * Отношение с атрибутами предложения
     *
     * @return HasMany
     */
    public function attributes(): HasMany
    {
        return $this->hasMany(CatalogOffersAttribute::class, 'offers_id', 'offers_id');
    }

    /**
     * Отношение с наличием на складах
     *
     * @return HasMany
     */
    public function warehouseOffers(): HasMany
    {
        return $this->hasMany(CatalogWarehouseOffer::class, 'offers_id', 'offers_id');
    }

    /**
     * Отношение с пользователем-создателем
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        if (class_exists(\App\Modules\User\Models\User::class)) {
            return $this->belongsTo(\App\Modules\User\Models\User::class, 'created_by');
        }

        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Отношение с пользователем-редактором
     *
     * @return BelongsTo
     */
    public function editor(): BelongsTo
    {
        if (class_exists(\App\Modules\User\Models\User::class)) {
            return $this->belongsTo(\App\Modules\User\Models\User::class, 'updated_by');
        }

        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Создание нового предложения с логированием
     *
     * @param array $attributes
     * @return static
     */
    public static function createWithLog(array $attributes)
    {
        try {
            $offer = static::create($attributes);
            Log::info('Product offer created', [
                'offers_id' => $offer->offers_id,
                'product_id' => $offer->product_id,
                'name' => $offer->name
            ]);
            return $offer;
        } catch (\Exception $e) {
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
     */
    public function updateWithLog(array $attributes)
    {
        try {
            $result = $this->update($attributes);
            if ($result) {
                Log::info('Product offer updated', [
                    'offers_id' => $this->offers_id,
                    'name' => $this->name
                ]);
            }
            return $result;
        } catch (\Exception $e) {
            Log::error('Error updating product offer', [
                'error' => $e->getMessage(),
                'offers_id' => $this->offers_id
            ]);
            throw $e;
        }
    }

    /**
     * Удаление предложения с логированием
     *
     * @return bool|null
     */
    public function deleteWithLog()
    {
        try {
            $result = $this->delete();
            if ($result) {
                Log::info('Product offer deleted', [
                    'offers_id' => $this->offers_id,
                    'name' => $this->name
                ]);
            }
            return $result;
        } catch (\Exception $e) {
            Log::error('Error deleting product offer', [
                'error' => $e->getMessage(),
                'offers_id' => $this->offers_id
            ]);
            throw $e;
        }
    }

    /**
     * Получение основной цены предложения
     *
     * @param string $priceType
     * @return float|null
     */
    public function getPrice(string $priceType = 'uprice'): ?float
    {
        $price = $this->prices()->where('price_type', $priceType)->first();
        return $price ? (float) $price->price : null;
    }

    /**
     * Получение атрибута по типу
     *
     * @param string $type
     * @return string|null
     */
    public function getAttributeByType(string $type): ?string
    {
        $attribute = $this->attributes()->where('attributes_type', $type)->first();
        return $attribute ? $attribute->attributes_value : null;
    }

    /**
     * Получение общего количества на всех складах
     *
     * @return int
     */
    public function getTotalQuantity(): int
    {
        return (int) $this->warehouseOffers()->sum('quantity');
    }

    /**
     * Получение всех цен в виде массива
     *
     * @return array
     */
    public function getPricesArray(): array
    {
        return $this->prices->mapWithKeys(function ($price) {
            return [$price->price_type => $price->price];
        })->toArray();
    }

    /**
     * Получение всех атрибутов в виде массива
     *
     * @return array
     */
    public function getAttributesArray(): array
    {
        return $this->attributes->mapWithKeys(function ($attribute) {
            return [$attribute->attributes_type => $attribute->attributes_value];
        })->toArray();
    }
}
