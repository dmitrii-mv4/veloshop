<?php

namespace App\Modules\Catalog\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Модель CatalogProductOffer
 *
 * Модель предложений товара (вариаций).
 * Содержит информацию о различных вариантах товара (цвет, размер и т.д.)
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
        'offer_id',
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
