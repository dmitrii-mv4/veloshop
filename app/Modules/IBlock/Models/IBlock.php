<?php
/**
 * Модель информационного блока с поддержкой мягкого удаления (корзины).
 * Включает основные поля: заголовок, контент, автор.
 */
namespace App\Modules\IBlock\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use App\Modules\User\Models\User;

class IBlock extends Model
{
    use SoftDeletes;

    protected $table = 'iblocks';
    
    protected $fillable = [
        'title',
        'content',
        'author_id',
    ];

    protected $casts = [
        'author_id' => 'integer',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Связь с автором (пользователем)
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Получить информационные блоки без корзины.
     */
    public function scopeWithoutTrashed($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Получить информационные блоки в корзине.
     */
    public function scopeTrashedOnly($query)
    {
        return $query->onlyTrashed();
    }

    /**
     * Восстановить информационный блок из корзины.
     */
    public function restoreWithLog($userId)
    {
        try {
            $this->restore();
            
            Log::info('IBlock restored from trash', [
                'iblock_id' => $this->id,
                'iblock_title' => $this->title,
                'restored_by' => $userId,
                'restored_at' => now(),
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to restore iblock from trash', [
                'iblock_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Полное удаление информационного блока с логом.
     */
    public function forceDeleteWithLog($userId)
    {
        try {
            $iblockId = $this->id;
            $iblockTitle = $this->title;
            
            $this->forceDelete();
            
            Log::info('IBlock permanently deleted', [
                'iblock_id' => $iblockId,
                'iblock_title' => $iblockTitle,
                'deleted_by' => $userId,
                'deleted_at' => now(),
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to permanently delete iblock', [
                'iblock_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Получить краткое содержание (первые 150 символов текста)
     */
    public function getExcerptAttribute()
    {
        $content = strip_tags($this->content);
        return mb_strlen($content) > 150 
            ? mb_substr($content, 0, 150) . '...' 
            : $content;
    }
}