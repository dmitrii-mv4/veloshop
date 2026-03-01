<?php

namespace App\Modules\News\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Category extends Model
{
    protected $table = 'news_categories';

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
            Log::info('News Category: создание категории', ['title' => $category->title, 'slug' => $category->slug]);
        });

        static::updating(function ($category) {
            Log::info('News Category: обновление категории', ['id' => $category->id, 'title' => $category->title]);
        });

        static::deleted(function ($category) {
            Log::info('News Category: удаление категории', ['id' => $category->id, 'title' => $category->title]);
        });
    }

    public function news()
    {
        return $this->belongsToMany(News::class, 'news_category_news', 'category_id', 'news_id');
    }
}