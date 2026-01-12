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
 * @property string $articul
 * @property int|null $author_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
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
        'articul',
        'author_id',
    ];

    /**
     * Связь с пользователем, создавшим товар
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}