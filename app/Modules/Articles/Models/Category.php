<?php

namespace App\Modules\Articles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Category extends Model
{
    protected $table = 'articles_categories';

    protected $fillable = [
        'title',
        'slug',
        'description',
    ];

    protected static function booted()
    {
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->title);
            }
            Log::info('Articles Category: создание категории', ['title' => $category->title, 'slug' => $category->slug]);
        });

        static::updating(function ($category) {
            Log::info('Articles Category: обновление категории', ['id' => $category->id, 'title' => $category->title]);
        });

        static::deleted(function ($category) {
            Log::info('Articles Category: удаление категории', ['id' => $category->id, 'title' => $category->title]);
        });
    }

    public function articles()
    {
        return $this->belongsToMany(Articles::class, 'articles_category_articles', 'category_id', 'articles_id');
    }
}