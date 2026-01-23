<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Модель заказов для модуля Catalog
 *
 * Предназначена для управления заказами с поддержкой мягкого удаления,
 * отслеживанием пользователей и статусов заказов.
 */
class Order extends Model
{
    use SoftDeletes, OrderRelationsTrait, OrderScopesTrait;

    /**
     * Название таблицы в базе данных
     */
    protected $table = 'catalog_orders';

    /**
     * Массово назначаемые атрибуты
     */
    protected $fillable = [
        'order_number',
        'customer_id',
        'is_paid',
        'is_cancelled',
        'cancellation_reason',
        'has_problem',
        'problem_description',
        'total_amount',
        'responsible_id',
        'comment',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    /**
     * Атрибуты, которые должны быть приведены к определенным типам
     */
    protected $casts = [
        'is_paid' => 'boolean',
        'is_cancelled' => 'boolean',
        'has_problem' => 'boolean',
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];


    /**
     * Генерация номера заказа
     * 
     * @return string
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = date('Ymd');
        $random = strtoupper(substr(uniqid(), -6));
        
        return $prefix . '-' . $date . '-' . $random;
    }

    /**
     * Получение статуса заказа в читаемом формате
     * 
     * @return string
     */
    public function getStatusAttribute(): string
    {
        if ($this->is_cancelled) {
            return 'Отменен';
        }
        
        if ($this->has_problem) {
            return 'Проблема';
        }
        
        if ($this->is_paid) {
            return 'Оплачен';
        }
        
        return 'Новый';
    }

    /**
     * Получение класса цвета статуса
     * 
     * @return string
     */
    public function getStatusColorAttribute(): string
    {
        if ($this->is_cancelled) {
            return 'danger';
        }
        
        if ($this->has_problem) {
            return 'warning';
        }
        
        if ($this->is_paid) {
            return 'success';
        }
        
        return 'primary';
    }
}