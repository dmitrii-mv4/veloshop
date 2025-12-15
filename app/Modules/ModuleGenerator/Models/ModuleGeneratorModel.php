<?php

namespace App\Modules\ModuleGenerator\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModuleGeneratorModel extends Model
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
}
