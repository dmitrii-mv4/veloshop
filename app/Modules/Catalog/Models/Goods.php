<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Modules\User\Models\User;

/**
 * Модель товара в каталоге
 * 
 * @property int $id
 * @property string $title
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property int|null $section_id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Modules\User\Models\User|null $author
 * @property-read \App\Modules\User\Models\User|null $editor
 * @property-read \App\Modules\Catalog\Models\Section|null $section
 */
class Goods extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * Таблица, связанная с моделью
     *
     * @var string
     */
    protected $table = 'catalog_goods';

    /**
     * Поля, разрешенные для массового заполнения
     *
     * @var array<string>
     */
    protected $fillable = [
        'title',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'section_id',
        'created_by',
        'updated_by',
    ];

    /**
     * Связь с пользователем, создавшим товар
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Связь с пользователем, обновившим товар
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Связь с разделом каталога
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }
}