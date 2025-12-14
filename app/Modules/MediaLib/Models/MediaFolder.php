<?php

namespace App\Modules\MediaLib\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\User\Models\User;

class MediaFolder extends Model
{
    use HasFactory;

    protected $table = 'media_folders';

    protected $fillable = [
        'name',
        'path',
        'parent_id',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MediaFolder::class, 'parent_id');
    }

    public function mediaFiles(): HasMany
    {
        return $this->hasMany(Media::class, 'folder_id');
    }

    // Рекурсивное получение всех подпапок
    public function getAllChildren()
    {
        return $this->children()->with('allChildren');
    }

    // Полный путь для отображения
    public function getFullPath(): string
    {
        return $this->path;
    }

    public static function isNameUnique(string $name, ?int $parentId = null): bool
    {
        return !static::where('parent_id', $parentId)
            ->where('name', $name)
            ->exists();
    }
}