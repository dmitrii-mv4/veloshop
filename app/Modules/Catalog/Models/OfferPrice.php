<?php

namespace App\Modules\Catalog\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Модель цен для предложений товара.
 *
 * @property int $id
 * @property string $offer_id
 * @property int $price_type_id
 * @property float $price
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class OfferPrice extends Model
{
    use OfferPriceRelationsTrait;

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
        'price_type_id',
        'price',
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
     * Получить цену с валютой
     */
    public function getPriceWithCurrency(): string
    {
        $currency = $this->priceType->currency ?? 'RUB';
        $currencySymbol = $this->getCurrencySymbol($currency);

        return number_format($this->price, 2, '.', ' ').' '.$currencySymbol;
    }

    /**
     * Получить символ валюты
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
     */
    public static function getMainPrice(string $offerId): ?float
    {
        try {
            $mainType = PriceType::getMainPriceType();
            if (! $mainType) {
                return null;
            }

            $price = self::where('offer_id', $offerId)
                ->where('price_type_id', $mainType->id)
                ->value('price');

            return $price ? (float) $price : null;
        } catch (Exception $e) {
            Log::error('Error getting main price', [
                'error' => $e->getMessage(),
                'offer_id' => $offerId,
            ]);

            return null;
        }
    }
}
