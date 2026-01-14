<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\User\Models\User;
use App\Modules\Catalog\Models\Goods;

/**
 * Модель раздела каталога
 * 
 * @property int $id Идентификатор раздела
 * @property string $name Название раздела
 * @property string $slug URL-адрес раздела
 * @property string|null $description Описание раздела
 * @property int|null $parent_id Идентификатор родительского раздела
 * @property int $sort_order Порядок сортировки
 * @property string|null $meta_title Мета-заголовок для SEO
 * @property string|null $meta_keywords Мета-ключевые слова для SEO
 * @property string|null $meta_description Мета-описание для SEO
 * @property bool $is_active Статус активности раздела
 * @property int|null $author_id Идентификатор автора
 * @property \Illuminate\Support\Carbon $created_at Дата создания
 * @property \Illuminate\Support\Carbon $updated_at Дата обновления
 * @property \Illuminate\Support\Carbon|null $deleted_at Дата удаления
 * 
 * @property-read Section|null $parent Родительский раздел
 * @property-read \Illuminate\Database\Eloquent\Collection|Section[] $children Дочерние разделы
 * @property-read \Illuminate\Database\Eloquent\Collection|Goods[] $goods Товары раздела
 * @property-read User|null $author Автор раздела
 */
class Section extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * Таблица, связанная с моделью
     *
     * @var string
     */
    protected $table = 'catalog_sections';

    /**
     * Поля, разрешенные для массового заполнения
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'sort_order',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'is_active',
        'author_id',
    ];

    /**
     * Типы атрибутов
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Связь с родительским разделом
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'parent_id');
    }

    /**
     * Связь с дочерними разделами
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(Section::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Связь с товарами в разделе
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function goods(): HasMany
    {
        return $this->hasMany(Goods::class, 'section_id');
    }

    /**
     * Связь с автором раздела
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Получить только активные разделы
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Получить только корневые разделы (без родителя)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Получить дерево разделов с вложенностью
     *
     * @param int|null $parentId Идентификатор родительского раздела
     * @param array $exclude Исключаемые разделы (для редактирования)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getTree(?int $parentId = null, array $exclude = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = self::with('children')
            ->where('parent_id', $parentId)
            ->orderBy('sort_order')
            ->orderBy('name');
            
        if (!empty($exclude)) {
            $query->whereNotIn('id', $exclude);
        }
        
        return $query->get();
    }

    /**
     * Проверить, является ли раздел родительским для другого раздела
     *
     * @param int $sectionId Идентификатор проверяемого раздела
     * @return bool
     */
    public function isParentOf(int $sectionId): bool
    {
        $child = self::find($sectionId);
        if (!$child) {
            return false;
        }
        
        // Проверяем всю цепочку родителей
        while ($child->parent_id) {
            if ($child->parent_id == $this->id) {
                return true;
            }
            $child = self::find($child->parent_id);
        }
        
        return false;
    }

    /**
     * Получить полный путь к разделу
     *
     * @param string $separator Разделитель
     * @return string
     */
    public function getPath(string $separator = ' → '): string
    {
        $path = [];
        $current = $this;
        
        while ($current) {
            array_unshift($path, $current->name);
            $current = $current->parent;
        }
        
        return implode($separator, $path);
    }

    /**
     * Получить количество товаров в разделе (включая подразделы)
     *
     * @return int
     */
    public function getTotalGoodsCount(): int
    {
        $count = $this->goods()->count();
        
        foreach ($this->children as $child) {
            $count += $child->getTotalGoodsCount();
        }
        
        return $count;
    }
}