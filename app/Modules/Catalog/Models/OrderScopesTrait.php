<?php declare(strict_types=1);

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Трейт скоупов для заказа.
 *
 * @method static paid()
 * @method static unpaid()
 * @method static cancelled()
 * @method static active()
 * @method static withProblems()
 * @method static byCustomer(int $customerId)
 * @method static byResponsible(int $responsibleId)
 * @method static byAmountRange(float $min, float $max)
 * @method static searchByOrderNumber(string $searchTerm)
 */

trait OrderScopesTrait
{
    /**
     * Только оплаченные заказы
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('is_paid', true);
    }

    /**
     * Только неоплаченные заказы
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->where('is_paid', false);
    }

    /**
     * Только отмененные заказы
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('is_cancelled', true);
    }

    /**
     * Только активные заказы (не отмененные)
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_cancelled', false);
    }

    /**
     * Только заказы с проблемами
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithProblems(Builder $query): Builder
    {
        return $query->where('has_problem', true);
    }

    /**
     * Только заказы без проблем
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithoutProblems(Builder $query): Builder
    {
        return $query->where('has_problem', false);
    }

    /**
     * Фильтр по покупателю
     *
     * @param Builder $query
     * @param int $customerId
     * @return Builder
     */
    public function scopeByCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Фильтр по ответственному
     *
     * @param Builder $query
     * @param int $responsibleId
     * @return Builder
     */
    public function scopeByResponsible(Builder $query, int $responsibleId): Builder
    {
        return $query->where('responsible_id', $responsibleId);
    }

    /**
     * Фильтр по диапазону суммы
     *
     * @param Builder $query
     * @param float $min
     * @param float $max
     * @return Builder
     */
    public function scopeByAmountRange(Builder $query, float $min, float $max): Builder
    {
        return $query->whereBetween('total_amount', [$min, $max]);
    }

    /**
     * Поиск по номеру заказа
     *
     * @param Builder $query
     * @param string $searchTerm
     * @return Builder
     */
    public function scopeSearchByOrderNumber(Builder $query, string $searchTerm): Builder
    {
        return $query->where('order_number', 'LIKE', "%{$searchTerm}%");
    }

    /**
     * Заказы выше указанной суммы
     *
     * @param Builder $query
     * @param float $amount
     * @return Builder
     */
    public function scopeAmountAbove(Builder $query, float $amount): Builder
    {
        return $query->where('total_amount', '>', $amount);
    }

    /**
     * Заказы ниже указанной суммы
     *
     * @param Builder $query
     * @param float $amount
     * @return Builder
     */
    public function scopeAmountBelow(Builder $query, float $amount): Builder
    {
        return $query->where('total_amount', '<', $amount);
    }

    /**
     * Сортировка по сумме (убывание)
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOrderByAmountDesc(Builder $query): Builder
    {
        return $query->orderBy('total_amount', 'desc');
    }

    /**
     * Сортировка по сумме (возрастание)
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOrderByAmountAsc(Builder $query): Builder
    {
        return $query->orderBy('total_amount', 'asc');
    }
}