<?php

namespace App\Modules\Catalog\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Модель корзины покупателя
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $customer_id
 * @property float $total_price
 * @property int $total_quantity
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read User|null $user
 * @property-read Customer|null $customer
 * @property-read \Illuminate\Database\Eloquent\Collection|CatalogBasketItem[] $items
 * @property-read User|null $creator
 * @property-read User|null $updater
 */
class CatalogBasket extends Model
{
    /**
     * Таблица, связанная с моделью.
     *
     * @var string
     */
    protected $table = 'catalog_baskets';

    /**
     * Поля, разрешённые для массового заполнения.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'customer_id',
        'total_price',
        'total_quantity',
        'created_by',
        'updated_by',
    ];

    /**
     * Приведение типов.
     *
     * @var array
     */
    protected $casts = [
        'total_price'   => 'float',
        'total_quantity' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /**
     * Значения по умолчанию.
     *
     * @var array
     */
    protected $attributes = [
        'total_price'   => 0,
        'total_quantity' => 0,
    ];

    /**
     * Пользователь-владелец корзины.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Связанный покупатель (если корзина привязана к клиенту каталога).
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Товары в корзине.
     *
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(CatalogBasketItem::class, 'catalog_basket_id');
    }

    /**
     * Кто создал запись.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Кто последний обновил запись.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Пересчёт итоговых значений корзины (total_price, total_quantity)
     * на основе текущих цен офферов.
     *
     * @param bool $save Сохранить ли модель после пересчёта
     * @return bool
     */
    public function recalculateTotals(bool $save = true): bool
    {
        try {
            $totalQuantity = 0;
            $totalPrice = 0;

            foreach ($this->items as $item) {
                if ($item->offer) {
                    $price = $item->offer->getPrice(); // получаем текущую цену оффера
                    $totalPrice += $price;
                    $totalQuantity++;
                }
            }

            $this->total_price = $totalPrice;
            $this->total_quantity = $totalQuantity;

            if ($save) {
                $result = $this->save();
                Log::info('Basket totals recalculated', [
                    'basket_id' => $this->id,
                    'total_price' => $totalPrice,
                    'total_quantity' => $totalQuantity,
                ]);
                return $result;
            }

            return true;
        } catch (Exception $e) {
            Log::error('Error recalculating basket totals', [
                'basket_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Добавление оффера в корзину.
     *
     * @param int $offerId
     * @return CatalogBasketItem|null
     */
    public function addOffer(int $offerId): ?CatalogBasketItem
    {
        try {
            // Проверяем, есть ли уже такой оффер
            $existing = $this->items()->where('offer_id', $offerId)->first();
            if ($existing) {
                Log::info('Offer already in basket', [
                    'basket_id' => $this->id,
                    'offer_id' => $offerId,
                ]);
                return $existing;
            }

            $item = $this->items()->create([
                'offer_id' => $offerId,
            ]);

            $this->recalculateTotals(true);

            Log::info('Offer added to basket', [
                'basket_id' => $this->id,
                'offer_id' => $offerId,
            ]);

            return $item;
        } catch (Exception $e) {
            Log::error('Error adding offer to basket', [
                'basket_id' => $this->id,
                'offer_id' => $offerId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Удаление оффера из корзины.
     *
     * @param int $offerId
     * @return bool
     */
    public function removeOffer(int $offerId): bool
    {
        try {
            $deleted = $this->items()->where('offer_id', $offerId)->delete();
            if ($deleted) {
                $this->recalculateTotals(true);
                Log::info('Offer removed from basket', [
                    'basket_id' => $this->id,
                    'offer_id' => $offerId,
                ]);
            }
            return (bool)$deleted;
        } catch (Exception $e) {
            Log::error('Error removing offer from basket', [
                'basket_id' => $this->id,
                'offer_id' => $offerId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}