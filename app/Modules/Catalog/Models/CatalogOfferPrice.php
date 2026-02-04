<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Модель CatalogOfferPrice
 *
 * Модель цен для предложений товара.
 * Содержит различные типы цен для каждого предложения.
 *
 * @property int $id
 * @property string $offer_id
 * @property string $price_type
 * @property float $price
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class CatalogOfferPrice extends Model
{
    /**
     * Имя таблицы в базе данных
     *
     * @var string
     */
    protected $table = 'catalog_offers_prices';

    /**
     * Поля, разрешенные для массового заполнения
     *
     * @var array
     */
    protected $fillable = [
        'offer_id',
        'type_price_id',
        'price'
    ];

    /**
     * Атрибуты, которые должны быть приведены к определенным типам
     *
     * @var array
     */
    protected $casts = [
        'price' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Отношение с типом цены
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function typePrice()
    {
        return $this->belongsTo(CatalogTypePrice::class, 'type_price_id');
    }

    /**
     * Отношение с предложением
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function offer()
    {
        return $this->belongsTo(CatalogProductOffer::class, 'offer_id', 'offer_id');
    }

    /**
     * Получить цену с валютой
     *
     * @return string
     */
    public function getPriceWithCurrency(): string
    {
        $currency = $this->typePrice->currency ?? 'RUB';
        $currencySymbol = $this->getCurrencySymbol($currency);

        return number_format($this->price, 2, '.', ' ') . ' ' . $currencySymbol;
    }

    /**
     * Получить символ валюты
     *
     * @param string $currency
     * @return string
     */
    private function getCurrencySymbol(string $currency): string
    {
        $symbols = [
            'RUB' => '₽',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
        ];

        return $symbols[$currency] ?? $currency;
    }

    /**
     * Получить основную цену для предложения
     *
     * @param string $offerId
     * @return float|null
     */
    public static function getMainPrice(string $offerId): ?float
    {
        try {
            $mainType = CatalogTypePrice::getMainPriceType();
            if (!$mainType) {
                return null;
            }

            $price = self::where('offer_id', $offerId)
                ->where('type_price_id', $mainType->id)
                ->value('price');

            return $price ? (float) $price : null;
        } catch (\Exception $e) {
            Log::error('Error getting main price', [
                'error' => $e->getMessage(),
                'offer_id' => $offerId
            ]);
            return null;
        }
    }

    /**
     * Сохранение цены с логированием
     *
     * @param array $attributes
     * @return static
     * @throws \Exception
     */
    public static function createWithLog(array $attributes): static
    {
        try {
            $price = static::create($attributes);
            Log::info('Offer price created', [
                'offer_id' => $price->offer_id,
                'type_price_id' => $price->type_price_id,
                'price' => $price->price
            ]);
            return $price;
        } catch (\Exception $e) {
            Log::error('Error creating offer price', [
                'error' => $e->getMessage(),
                'attributes' => $attributes
            ]);
            throw $e;
        }
    }
}
