<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    use HasFactory;

    protected $table = 'settings';
    protected $guarded = false;

    protected $fillable = [
        'name_site',
        'url_site',
        'description_site'
    ];
}
