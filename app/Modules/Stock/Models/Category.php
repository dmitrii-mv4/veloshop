<?php

namespace App\Modules\Stock\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Category extends Model
{
    protected $table = 'stock_categories';

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
            Log::info('Stock Category: создание категории', ['title' => $category->title, 'slug' => $category->slug]);
        });

        static::updating(function ($category) {
            Log::info('Stock Category: обновление категории', ['id' => $category->id, 'title' => $category->title]);
        });

        static::deleted(function ($category) {
            Log::info('Stock Category: удаление категории', ['id' => $category->id, 'title' => $category->title]);
        });
    }

    public function stock()
    {
        return $this->belongsToMany(Stock::class, 'stock_category_stock', 'category_id', 'stock_id');
    }
}