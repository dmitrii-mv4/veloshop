<?php

namespace App\Modules\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель пунктов меню
 * Поддерживает древовидную структуру через parent_id
 * 
 * @property int $id
 * @property int $menu_id
 * @property string $title
 * @property string $url
 * @property string|null $icon
 * @property int|null $parent_id
 * @property int $order
 * @property bool $is_active
 * @property string|null $seo_title
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class MenuItem extends Model
{
    /**
     * Поля, доступные для массового заполнения
     *
     * @var array
     */
    protected $fillable = [
        'menu_id',
        'title',
        'url',
        'icon',
        'parent_id',
        'order',
        'is_active',
        'seo_title',
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
        
        // Рекурсивное удаление дочерних элементов перед удалением родителя
        static::deleting(function ($menuItem) {
            // Получаем и удаляем всех дочерних элементов рекурсивно
            $deleteChildren = function ($item) use (&$deleteChildren) {
                $children = $item->children()->get();
                
                foreach ($children as $child) {
                    $deleteChildren($child);
                    $child->delete();
                }
            };
            
            $deleteChildren($menuItem);
        });
    }

    /**
     * Отношение: пункт меню принадлежит меню
     *
     * @return BelongsTo
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Отношение: родительский пункт меню
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Отношение: дочерние пункты меню
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    /**
     * Отношение: создатель пункта меню
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'created_by');
    }

    /**
     * Отношение: обновивший пункт меню
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'updated_by');
    }

    /**
     * Получить активные дочерние пункты
     *
     * @return HasMany
     */
    public function activeChildren(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('order');
    }

    /**
     * Проверить, имеет ли пункт дочерние элементы
     *
     * @return bool
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Проверить, является ли пункт корневым (без родителя)
     *
     * @return bool
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }
}