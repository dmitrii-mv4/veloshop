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
        'offer_id',
        'product_id',
        'size',
        'color',
        'main-color',
        'articul_supplier',
        'name',
        'vcode',
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
     * Значения по умолчанию для атрибутов модели
     *
     * @var array
     */
    protected $attributes = [
        'size' => '',
        'color' => '',
        'main-color' => '',
        'vcode' => '',
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
                return $this->{'main-color'};
            case 'vcode':
                return $this->vcode;
            case 'articul_supplier':
                return $this->articul_supplier;
            default:
                return null;
        }
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
            'main-color' => $this->{'main-color'},
            'vcode' => $this->vcode,
            'articul_supplier' => $this->articul_supplier
        ];
    }
}