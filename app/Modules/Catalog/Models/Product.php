<?php

namespace App\Modules\Catalog\Models;

use App\Core\Models\TableNameTrait;
use Exception;
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
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Product extends Model
{
    use ProductRelationsTrait, ProductScopesTrait, TableNameTrait;

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
     * @throws Exception
     */

    /**
     * Получение уникальных значений для фильтрации
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
                'field' => $field,
            ]);

            return [];
        }
    }

    /**
     * Получение товара по product_id
     */
    public static function findByProductId(string $productId): ?Product
    {
        return static::where('product_id', $productId)->first();
    }
}
