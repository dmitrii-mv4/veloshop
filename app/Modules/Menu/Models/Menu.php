<?php

namespace App\Modules\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

/**
 * Модель типов меню
 * Отвечает за хранение основных настроек меню
 * 
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int|null $type_id
 * @property bool $is_active
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read MenuType|null $menuType
 */
class Menu extends Model
{
    /**
     * Поля, доступные для массового заполнения
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'menu_type_id',
        'is_active',
        'created_by',
        'updated_by'
    ];

    /**
     * Преобразование типов атрибутов
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Boot the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        
        // Рекурсивное удаление пунктов меню перед удалением меню
        static::deleting(function ($menu) {
            Log::info('Начало удаления меню с пунктами', [
                'menu_id' => $menu->id,
                'menu_name' => $menu->name
            ]);
            
            // Удаляем все пункты меню (вызывается событие удаления в MenuItem)
            $menu->items()->delete();
            
            Log::info('Все пункты меню удалены', [
                'menu_id' => $menu->id
            ]);
        });
    }

    /**
     * Отношение: меню принадлежит типу меню
     *
     * @return BelongsTo
     */
    public function menuType(): BelongsTo
    {
        return $this->belongsTo(MenuType::class);
    }

    /**
     * Отношение: меню имеет много пунктов меню
     * Каскадное удаление настроено на уровне БД
     *
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('order');
    }

    /**
     * Отношение: создатель меню
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'created_by');
    }

    /**
     * Отношение: обновивший меню
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'updated_by');
    }

    /**
     * Получить только активные пункты меню
     *
     * @return HasMany
     */
    public function activeItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)
            ->where('is_active', true)
            ->orderBy('order');
    }

    /**
     * Получить корневые пункты меню (без родителя)
     *
     * @return HasMany
     */
    public function rootItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)
            ->whereNull('parent_id')
            ->orderBy('order');
    }

    /**
     * Получить количество пунктов в меню
     *
     * @return int
     */
    public function getItemsCountAttribute(): int
    {
        return $this->items()->count();
    }

    /**
     * Получить название типа меню
     *
     * @return string
     */
    public function getMenuTypeNameAttribute(): string
    {
        return $this->menuType ? $this->menuType->name : 'Не указан';
    }
}