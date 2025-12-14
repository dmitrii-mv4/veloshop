<?php

namespace App\Modules\InfoBlock\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class InfoBlock extends Model
{
    use HasFactory;

    protected $table = 'info_blocks';
    protected $guarded = false;

    protected $fillable = [
        'name',
        'content',
        'image'
    ];
}
