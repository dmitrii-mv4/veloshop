<?php

namespace App\Modules\Catalog\Models;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Модель Product
 *
 * Основная модель товаров в системе каталога.
 * Содержит информацию о товарах, их брендах, моделях и сезонах.
 *
 * @property int $id
 * @property string $product_id
 * @property int $category_id
 * @property string|null $brand
 * @property string|null $model
 * @property string|null $seazon
 * @property string $name
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property int|null $updated_by
 * @property int|null $created_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Product extends Model
{
    use ProductRelationsTrait, ProductScopesTrait;

    /**
     * Имя таблицы в базе данных
     *
     * @var string
     */
    protected $table = 'catalog_products';

    /**
     * Поля, разрешенные для массового заполнения
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'category_id',
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
     * Создание нового товара с логированием
     *
     * @param array $attributes
     * @return static
     * @throws Exception
     */
    public static function createWithLog(array $attributes): static
    {
        try {
            $product = static::create($attributes);
            Log::info('Product created', ['product_id' => $product->product_id, 'name' => $product->name]);
            return $product;
        } catch (Exception $e) {
            Log::error('Error creating product', ['error' => $e->getMessage(), 'attributes' => $attributes]);
            throw $e;
        }
    }

    /**
     * Обновление товара с логированием
     *
     * @param array $attributes
     * @return bool
     * @throws Exception
     */
    public function updateWithLog(array $attributes): bool
    {
        try {
            $result = $this->update($attributes);
            if ($result) {
                Log::info('Product updated', ['product_id' => $this->product_id, 'name' => $this->name]);
            }
            return $result;
        } catch (Exception $e) {
            Log::error('Error updating product', ['error' => $e->getMessage(), 'product_id' => $this->product_id]);
            throw $e;
        }
    }

    /**
     * Удаление товара с логированием
     *
     * @return bool|null
     */
    public function deleteWithLog(): ?bool
    {
        try {
            $result = $this->delete();
            if ($result) {
                Log::info('Product deleted', ['product_id' => $this->product_id, 'name' => $this->name]);
            }
            return $result;
        } catch (Exception $e) {
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
     * @return Collection
     */
    public static function searchByName(string $searchTerm): Collection
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
            Log::error('Error getting unique values', [
                'error' => $e->getMessage(),
                'field' => $field
            ]);
            return [];
        }
    }
}
