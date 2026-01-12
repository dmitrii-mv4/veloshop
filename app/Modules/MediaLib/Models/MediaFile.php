<?php

namespace App\Modules\MediaLib\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель для работы с файлами медиабиблиотеки
 * Хранит информацию о загруженных файлах, метаданные и переводы
 */
class MediaFile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'filename',
        'original_name',
        'path',
        'mime_type',
        'size',
        'folder_id',
        'created_by',
        'title',
        'description',
        'alt',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'size' => 'integer',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Папка, в которой находится файл
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    /**
     * Создатель файла
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Переводы файла
     */
    public function translations(): HasMany
    {
        return $this->hasMany(MediaTranslation::class, 'media_file_id');
    }

    /**
     * Получает перевод для указанной локали
     */
    public function translate(string $locale = null): ?MediaTranslation
    {
        $locale = $locale ?: app()->getLocale();
        return $this->translations()->where('locale', $locale)->first();
    }

    /**
     * Полный URL к файлу
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    /**
     * Путь к файлу на диске
     */
    public function getDiskPath(): string
    {
        return config('medialib.upload.directory', 'medialib') . '/' . $this->path;
    }

    /**
     * Проверяет, является ли файл изображением
     */
    public function isImage(): bool
    {
        return strpos($this->mime_type, 'image/') === 0;
    }

    /**
     * Размер файла в читаемом формате
     */
    public function getFormattedSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = $this->size;
        
        for ($i = 0; $bytes >= 1024 && $i < 4; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Информация о файле для корзины
     */
    public function getTrashInfo(): array
    {
        $deletedAt = $this->deleted_at;
        $deleteDate = $deletedAt->addDays(config('medialib.trash.retention_days', 30));
        $daysLeft = now()->diffInDays($deleteDate, false);
        
        return [
            'deleted_at' => $deletedAt->format('d.m.Y H:i'),
            'delete_date' => $deleteDate->format('d.m.Y'),
            'days_left' => $daysLeft > 0 ? $daysLeft : 0,
        ];
    }
}