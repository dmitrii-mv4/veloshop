<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Миграция для создания таблицы предложений товаров (catalog_product_offers)
 *
 * Таблица вариаций товаров (разные цвета, размеры и т.д.)
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
        Schema::create('catalog_product_offers', function (Blueprint $table) {
            $table->id();
            $table->string('offer_id', 50)->primary()->comment('Уникальный идентификатор предложения');
            $table->string('product_id', 50)->comment('Ссылка на товар');
            $table->string('articul_supplier', 100)->nullable()->comment('Артикул');
            $table->string('name', 255)->comment('Название предложения');
            $table->string('meta_title', 255)->nullable()->comment('Мета-заголовок');
            $table->text('meta_description')->nullable()->comment('Мета-описание');
            $table->string('meta_keywords', 500)->nullable()->comment('Ключевые слова');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('ID пользователя, обновившего запись');
            $table->unsignedBigInteger('created_by')->nullable()->comment('ID пользователя, создавшего запись');
            $table->timestamps();

            // Внешние ключи
            $table->foreign('product_id')
                  ->references('product_id')
                  ->on('catalog_products')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            // Индексы
            $table->index('product_id');
            $table->index('articul_supplier');
            $table->index('name');
            $table->index('created_at');
            $table->index('updated_at');
        });

        // Для PostgreSQL: создаем GIN индекс для полнотекстового поиска
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX catalog_product_offers_search_idx ON catalog_product_offers USING GIN (to_tsvector(\'russian\', COALESCE(name, \'\') || \' \' || COALESCE(articul_supplier, \'\')))');
        }
    }

    /**
     * Откат миграции
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalog_product_offers');
    }
};
