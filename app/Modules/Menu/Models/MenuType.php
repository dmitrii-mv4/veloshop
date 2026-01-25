<?php

namespace App\Modules\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

/**
 * Модель типов меню
 * Хранит типы меню для классификации меню
 * 
 * @property int $id
 * @property string $name
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class MenuType extends Model
{
    /**
     * Поля, доступные для массового заполнения
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'created_by',
        'updated_by'
    ];

    /**
     * Boot the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        
        // Событие при удалении типа меню
        static::deleting(function ($menuType) {
            Log::info('Начало удаления типа меню', [
                'type_id' => $menuType->id,
                'menu_type_name' => $menuType->name
            ]);
            
            // Устанавливаем type_id в NULL у всех связанных меню
            $affectedMenus = $menuType->menus()->update(['type_id' => null]);
            
            Log::info('Тип меню удален, меню обновлены', [
                'type_id' => $menuType->id,
                'affected_menus' => $affectedMenus
            ]);
        });
    }

    /**
     * Отношение: тип меню имеет много меню
     *
     * @return HasMany
     */
    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    /**
     * Отношение: создатель типа меню
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'created_by');
    }

    /**
     * Отношение: обновивший тип меню
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'updated_by');
    }

    /**
     * Проверить, используется ли тип меню
     *
     * @return bool
     */
    public function isUsed(): bool
    {
        return $this->menus()->exists();
    }

    /**
     * Получить количество меню с этим типом
     *
     * @return int
     */
    public function getMenusCountAttribute(): int
    {
        return $this->menus()->count();
    }
}