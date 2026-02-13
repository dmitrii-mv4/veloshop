<?php

use App\Modules\Catalog\Models\CatalogCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Миграция для создания таблицы товаров (catalog_products)
 *
 * Основная таблица товаров в системе каталога.
 */
return new class extends Migration
{
    /**
     * Запуск миграции
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalog_products', function (Blueprint $table) {
            $table->id();
            $table->string('product_id', 50)->unique()->comment('Уникальный идентификатор товара');
            $table->foreignIdFor(CatalogCategory::class, 'category_id')->comment('Связь с категорией товаров');
            $table->string('brand', 100)->nullable()->comment('Бренд');
            $table->string('model', 100)->nullable()->comment('Модель');
            $table->string('seazon', 50)->nullable()->comment('Сезон');
            $table->string('name', 255)->comment('Название товара');
            $table->string('meta_title', 255)->nullable()->comment('Мета-заголовок');
            $table->text('meta_description')->nullable()->comment('Мета-описание');
            $table->string('meta_keywords', 500)->nullable()->comment('Ключевые слова');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('ID пользователя, обновившего запись');
            $table->unsignedBigInteger('created_by')->nullable()->comment('ID пользователя, создавшего запись');
            $table->timestamps();

            // Индексы
            $table->index('product_id');
            $table->index('brand');
            $table->index('model');
            $table->index('seazon');
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('name');
            $table->index('group_name');
        });

        // Для PostgreSQL: создаем GIN индекс для полнотекстового поиска
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX catalog_products_search_idx ON catalog_products USING GIN (to_tsvector(\'russian\', COALESCE(name, \'\') || \' \' || COALESCE(group_name, \'\') || \' \' || COALESCE(brand, \'\') || \' \' || COALESCE(model, \'\')))');
        }
    }

    /**
     * Откат миграции
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalog_products');
    }
};
