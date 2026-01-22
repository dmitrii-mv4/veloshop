<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель заказов для модуля Catalog
 * 
 * Предназначена для управления заказами с поддержкой мягкого удаления,
 * отслеживанием пользователей и статусов заказов.
 */
class Order extends Model
{
    use SoftDeletes;

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
     * Связь с пользователем-покупателем
     * 
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'customer_id');
    }

    /**
     * Связь с ответственным пользователем
     * 
     * @return BelongsTo
     */
    public function responsible(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'responsible_id');
    }

    /**
     * Связь с пользователем, создавшим заказ
     * 
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'created_by');
    }

    /**
     * Связь с пользователем, обновившим заказ
     * 
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'updated_by');
    }

    /**
     * Связь с пользователем, удалившим заказ
     * 
     * @return BelongsTo
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'deleted_by');
    }

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