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
        'description_site',
    ];

    /**
     * Преобразование атрибутов при сохранении
     * Превращает null в пустую строку
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (is_null($model->description_site)) {
                $model->description_site = '';
            }
        });
    }

    /**
     * Мутатор для description_site
     * Автоматически преобразует null в пустую строку
     */
    public function setDescriptionSiteAttribute($value)
    {
        $this->attributes['description_site'] = is_null($value) ? '' : $value;
    }
}