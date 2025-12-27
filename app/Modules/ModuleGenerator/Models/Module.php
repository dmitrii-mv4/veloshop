<?php

namespace App\Modules\ModuleGenerator\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Module extends Model
{
    use HasFactory;

    protected $table = 'modules';
    protected $guarded = false;

    protected $fillable = [
        'code_module',
        'slug',
        'status',
        'section_seo',
        'section_categories',
        'section_tags',
        'section_comments',
        'description',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'meta_img_alt',
        'meta_img_title',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Проверяет, существует ли модуль с указанным кодом
     */
    public static function codeExists(string $code): bool
    {
        return static::where('code_module', $code)->exists();
    }

    /**
     * Получает модуль по коду
     */
    public static function getByCode(string $code): ?self
    {
        return static::where('code_module', $code)->first();
    }

    /**
     * Scope для поиска по названию модуля или описанию
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function($q) use ($search) {
            $q->where('code_module', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope для фильтрации по статусу
     */
    public function scopeByStatus(Builder $query, ?string $status): Builder
    {
        if (!$status) {
            return $query;
        }

        return $query->where('status', $status);
    }
}