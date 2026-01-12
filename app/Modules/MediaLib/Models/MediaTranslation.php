<?php

namespace App\Modules\MediaLib\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель для хранения переводов метаданных файлов
 * Поддерживает мультиязычность для SEO-атрибутов
 */
class MediaTranslation extends Model
{
    protected $fillable = [
        'media_file_id',
        'locale',
        'title',
        'description',
        'alt',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * Связанный файл
     */
    public function mediaFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'media_file_id');
    }
}