<?php
/**
 * Модель страницы с поддержкой мягкого удаления (корзины).
 * Включает все основные поля страницы и методы для работы с корзиной.
 */
namespace App\Modules\Page\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use App\Modules\User\Models\User;

class Page extends Model
{
    use SoftDeletes;

    protected $table = 'pages';
    
    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'published_at',
        'order',
        'created_by',
        'updated_by',
        'parent_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'order' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'parent_id' => 'integer',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Связь с автором (пользователем)
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Получить только опубликованные страницы.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    /**
     * Получить черновики.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Получить страницы в корзине.
     */
    public function scopeTrashedOnly($query)
    {
        return $query->onlyTrashed();
    }

    /**
     * Получить страницы без корзины.
     */
    public function scopeWithoutTrashed($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Восстановить страницу из корзины.
     */
    public function restoreWithLog($userId)
    {
        try {
            $this->restore();
            
            Log::info('Page restored from trash', [
                'page_id' => $this->id,
                'page_title' => $this->title,
                'restored_by' => $userId,
                'restored_at' => now(),
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to restore page from trash', [
                'page_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Полное удаление страницы с логом.
     */
    public function forceDeleteWithLog($userId)
    {
        try {
            $pageId = $this->id;
            $pageTitle = $this->title;
            
            $this->forceDelete();
            
            Log::info('Page permanently deleted', [
                'page_id' => $pageId,
                'page_title' => $pageTitle,
                'deleted_by' => $userId,
                'deleted_at' => now(),
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to permanently delete page', [
                'page_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}