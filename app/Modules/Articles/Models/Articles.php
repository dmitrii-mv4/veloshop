<?php

namespace App\Modules\Articles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\User\Models\User;
use App\Modules\Articles\Models\Category;
use Illuminate\Support\Facades\Log;

class Articles extends Model
{
    use SoftDeletes;

    // Константы статусов
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_PRIVATE = 'private';

    protected $table = 'articles';

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
     * Автор статьи (создатель)
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Пользователь, последним обновивший статью
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
        static::creating(function ($articles) {
            if (empty($articles->created_by) && auth()->check()) {
                $articles->created_by = auth()->id();
                Log::info('Articles: автор автоматически назначен', ['articles_id' => $articles->id ?? 'new', 'user_id' => auth()->id()]);
            }
        });

        static::updating(function ($articles) {
            if (auth()->check()) {
                $articles->updated_by = auth()->id();
                Log::info('Articles: обновление записи', ['articles_id' => $articles->id, 'user_id' => auth()->id()]);
            }
        });

        static::deleted(function ($articles) {
            if (!$articles->isForceDeleting()) {
                Log::info('Articles: перемещено в корзину', ['articles_id' => $articles->id, 'user_id' => auth()->id() ?? 'system']);
            }
        });

        static::restored(function ($articles) {
            Log::info('Articles: восстановлено из корзины', ['articles_id' => $articles->id, 'user_id' => auth()->id() ?? 'system']);
        });

        static::forceDeleted(function ($articles) {
            Log::info('Articles: полностью удалено', ['articles_id' => $articles->id, 'user_id' => auth()->id() ?? 'system']);
        });
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'articles_category_articles', 'articles_id', 'category_id');
    }
}