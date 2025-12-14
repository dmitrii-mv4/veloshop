<?php

namespace App\Modules\Page\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory;

    protected $table = 'pages';
    protected $guarded = false;

    protected $fillable = [
        'title',
        'content',
        'meta_slug',
        'meta_title',
        'meta_description',
        'meta_keys',
    ];
}
