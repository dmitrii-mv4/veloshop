<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Модель Product
 * 
 * Основная модель товаров в системе каталога.
 * Содержит информацию о товарах, их брендах, моделях и сезонах.
 * 
 * @property int $id
 * @property string $product_id
 * @property string|null $group_name
 * @property string|null $brand
 * @property string|null $model
 * @property string|null $seazon
 * @property string $name
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property int|null $updated_by
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Product extends Model
{
    /**
     * Имя таблицы в базе данных
     *
     * @var string
     */
    protected $table = 'catalog_products';

    /**
     * Первичный ключ таблицы
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Тип первичного ключа
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Инкрементирование первичного ключа
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Поля, разрешенные для массового заполнения
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'group_name',
        'brand',
        'model',
        'seazon',
        'name',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'updated_by',
        'created_by'
    ];

    /**
     * Атрибуты, которые должны быть приведены к определенным типам
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Отношение с предложениями товара
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function offers(): HasMany
    {
        return $this->hasMany(CatalogProductOffers::class, 'product_id', 'product_id');
    }

    /**
     * Отношение с пользователем-создателем
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'created_by');
    }

    /**
     * Отношение с пользователем-редактором
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'updated_by');
    }

    /**
     * Поиск товаров с использованием полнотекстового поиска PostgreSQL
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $searchTerm
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFullTextSearch($query, $searchTerm)
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'pgsql') {
            // Используем полнотекстовый поиск PostgreSQL
            return $query->whereRaw("
                to_tsvector('russian', 
                    COALESCE(name, '') || ' ' || 
                    COALESCE(group_name, '') || ' ' || 
                    COALESCE(brand, '') || ' ' || 
                    COALESCE(model, '')
                ) @@ plainto_tsquery('russian', ?)
            ", [$searchTerm]);
        } else {
            // Для MySQL используем LIKE поиск (или можно добавить FULLTEXT)
            return $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('group_name', 'LIKE', "%{$searchTerm}%") // ИСПРАВЛЕНО
                  ->orWhere('brand', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('model', 'LIKE', "%{$searchTerm}%");
            });
        }
    }

    /**
     * Поиск товаров с использованием триграммного поиска PostgreSQL
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $searchTerm
     * @param float $similarityThreshold
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSimilaritySearch($query, $searchTerm, $similarityThreshold = 0.3)
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'pgsql') {
            return $query->whereRaw("
                SIMILARITY(
                    COALESCE(name, '') || ' ' || 
                    COALESCE(group_name, '') || ' ' || 
                    COALESCE(brand, '') || ' ' || 
                    COALESCE(model, ''),
                    ?
                ) > ?
            ", [$searchTerm, $similarityThreshold])
            ->orderByRaw("
                SIMILARITY(
                    COALESCE(name, '') || ' ' || 
                    COALESCE(group_name, '') || ' ' || 
                    COALESCE(brand, '') || ' ' || 
                    COALESCE(model, ''),
                    ?
                ) DESC
            ", [$searchTerm]);
        } else {
            // Для MySQL используем обычный LIKE
            return $this->scopeFullTextSearch($query, $searchTerm);
        }
    }

    /**
     * Создание нового товара с логированием
     *
     * @param array $attributes
     * @return static
     */
    public static function createWithLog(array $attributes)
    {
        try {
            $product = static::create($attributes);
            Log::info('Product created', ['product_id' => $product->product_id, 'name' => $product->name]);
            return $product;
        } catch (\Exception $e) {
            Log::error('Error creating product', ['error' => $e->getMessage(), 'attributes' => $attributes]);
            throw $e;
        }
    }

    /**
     * Обновление товара с логированием
     *
     * @param array $attributes
     * @return bool
     */
    public function updateWithLog(array $attributes)
    {
        try {
            $result = $this->update($attributes);
            if ($result) {
                Log::info('Product updated', ['product_id' => $this->product_id, 'name' => $this->name]);
            }
            return $result;
        } catch (\Exception $e) {
            Log::error('Error updating product', ['error' => $e->getMessage(), 'product_id' => $this->product_id]);
            throw $e;
        }
    }

    /**
     * Удаление товара с логированием
     *
     * @return bool|null
     */
    public function deleteWithLog()
    {
        try {
            $result = $this->delete();
            if ($result) {
                Log::info('Product deleted', ['product_id' => $this->product_id, 'name' => $this->name]);
            }
            return $result;
        } catch (\Exception $e) {
            Log::error('Error deleting product', ['error' => $e->getMessage(), 'product_id' => $this->product_id]);
            throw $e;
        }
    }

    /**
     * Получение товара по product_id
     *
     * @param string $productId
     * @return Product|null
     */
    public static function findByProductId(string $productId): ?Product
    {
        return static::where('product_id', $productId)->first();
    }

    /**
     * Поиск товаров по названию (кросс-платформенный метод)
     *
     * @param string $searchTerm
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function searchByName(string $searchTerm)
    {
        return static::fullTextSearch($searchTerm)->get();
    }

    /**
     * Получение статистики по товарам
     *
     * @return array
     */
    public static function getStatistics(): array
    {
        try {
            $totalProducts = self::count();
            $todayProducts = self::whereDate('created_at', today())->count();
            
            return [
                'totalProducts' => $totalProducts,
                'todayProducts' => $todayProducts,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting product statistics', ['error' => $e->getMessage()]);
            return ['totalProducts' => 0, 'todayProducts' => 0];
        }
    }

    /**
     * Получение уникальных значений для фильтрации
     *
     * @param string $field
     * @return array
     */
    public static function getUniqueValues(string $field): array
    {
        try {
            return self::whereNotNull($field)
                ->distinct()
                ->pluck($field)
                ->sort()
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error getting unique values', [
                'error' => $e->getMessage(),
                'field' => $field
            ]);
            return [];
        }
    }
}