<?php

namespace App\Modules\Stock\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\User\Models\User;
use App\Modules\Stock\Models\Category;
use Illuminate\Support\Facades\Log;

class Stock extends Model
{
    use SoftDeletes;

    // Константы статусов
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_PRIVATE = 'private';

    protected $table = 'stock';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'description',
        'image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Автор акции (создатель)
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Пользователь, последним обновивший акцию
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Сохраняем ID автора при создании, если не указан
     */
    protected static function booted()
    {
        static::creating(function ($stock) {
            if (empty($stock->created_by) && auth()->check()) {
                $stock->created_by = auth()->id();
                Log::info('Stock: автор автоматически назначен', ['stock_id' => $stock->id ?? 'new', 'user_id' => auth()->id()]);
            }
        });

        static::updating(function ($stock) {
            if (auth()->check()) {
                $stock->updated_by = auth()->id();
                Log::info('Stock: обновление записи', ['stock_id' => $stock->id, 'user_id' => auth()->id()]);
            }
        });

        static::deleted(function ($stock) {
            if (!$stock->isForceDeleting()) {
                Log::info('Stock: перемещено в корзину', ['stock_id' => $stock->id, 'user_id' => auth()->id() ?? 'system']);
            }
        });

        static::restored(function ($stock) {
            Log::info('Stock: восстановлено из корзины', ['stock_id' => $stock->id, 'user_id' => auth()->id() ?? 'system']);
        });

        static::forceDeleted(function ($stock) {
            Log::info('Stock: полностью удалено', ['stock_id' => $stock->id, 'user_id' => auth()->id() ?? 'system']);
        });
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'stock_category_stock', 'stock_id', 'category_id');
    }
}