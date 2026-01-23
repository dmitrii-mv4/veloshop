<?php declare(strict_types=1);

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Трейт скоупов для сущности разделов.
 *
 * @method static fullTextSearch(string $searchTerm)
 * @method static similaritySearch(string $searchTerm, float $similarityThreshold)
 */
trait ProductScopesTrait
{
    /**
     * Поиск товаров с использованием полнотекстового поиска PostgreSQL
     *
     * @param Builder $query
     * @param string $searchTerm
     * @return Builder
     */
    public function scopeFullTextSearch(Builder $query, string $searchTerm): Builder
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
     * @param Builder $query
     * @param string $searchTerm
     * @param float $similarityThreshold
     * @return Builder
     */
    public function scopeSimilaritySearch(Builder $query, string $searchTerm, float $similarityThreshold = 0.3): Builder
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
}
