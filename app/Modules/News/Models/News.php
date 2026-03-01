<?php

namespace App\Modules\News\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\User\Models\User;
use App\Modules\News\Models\Category;
use Illuminate\Support\Facades\Log;

class News extends Model
{
    use SoftDeletes;

    // Константы статусов
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_PRIVATE = 'private';

    protected $table = 'news';

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
     * Автор новости (создатель)
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Пользователь, последним обновивший новость
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
        static::creating(function ($news) {
            if (empty($news->created_by) && auth()->check()) {
                $news->created_by = auth()->id();
                Log::info('News: автор автоматически назначен', ['news_id' => $news->id ?? 'new', 'user_id' => auth()->id()]);
            }
        });

        static::updating(function ($news) {
            if (auth()->check()) {
                $news->updated_by = auth()->id();
                Log::info('News: обновление записи', ['news_id' => $news->id, 'user_id' => auth()->id()]);
            }
        });

        static::deleted(function ($news) {
            if (!$news->isForceDeleting()) {
                Log::info('News: перемещено в корзину', ['news_id' => $news->id, 'user_id' => auth()->id() ?? 'system']);
            }
        });

        static::restored(function ($news) {
            Log::info('News: восстановлено из корзины', ['news_id' => $news->id, 'user_id' => auth()->id() ?? 'system']);
        });

        static::forceDeleted(function ($news) {
            Log::info('News: полностью удалено', ['news_id' => $news->id, 'user_id' => auth()->id() ?? 'system']);
        });
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'news_category_news', 'news_id', 'category_id');
    }
}