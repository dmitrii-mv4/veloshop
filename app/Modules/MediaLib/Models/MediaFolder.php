<?php

namespace App\Modules\MediaLib\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель для работы с папками медиабиблиотеки
 * Хранит информацию о папках, включая иерархическую структуру
 */
class MediaFolder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'created_by',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Родительская папка
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'parent_id');
    }

    /**
     * Дочерние папки
     */
    public function children(): HasMany
    {
        return $this->hasMany(MediaFolder::class, 'parent_id');
    }

    /**
     * Файлы в папке
     */
    public function files(): HasMany
    {
        return $this->hasMany(MediaFile::class, 'folder_id');
    }

    /**
     * Создатель папки
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Полный путь папки
     */
    public function getFullPath(): string
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        return implode('/', $path);
    }

    /**
     * Проверяет, есть ли в папке файлы
     */
    public function hasFiles(): bool
    {
        return $this->files()->count() > 0;
    }

    /**
     * Проверяет, есть ли в папке подпапки
     */
    public function hasSubfolders(): bool
    {
        return $this->children()->count() > 0;
    }
}