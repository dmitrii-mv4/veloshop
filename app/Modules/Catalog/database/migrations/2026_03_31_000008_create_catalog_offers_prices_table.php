<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Миграция для создания соединительной таблицы цен и предложений
 */
return new class extends Migration
{
    /*
     * Запуск миграции
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('catalog_offers_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_id')->comment('ID предложения товара');
            $table->unsignedBigInteger('price_type_id')->comment('ID типа цены');
            $table->decimal('price', 15, 2)->default(0)->comment('Значение цены');
            $table->timestamps();

            // Составной уникальный индекс для предотвращения дублирования
            $table->unique(['offer_id', 'price_type_id'], 'offer_type_price_unique');

            // Внешние ключи
            $table->foreign('offer_id')
                  ->references('id')
                  ->on('catalog_product_offers')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('price_type_id')
                  ->references('id')
                  ->on('catalog_price_types')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            // Индексы для оптимизации запросов
            $table->index('offer_id');
            $table->index('price_type_id');
            $table->index('price');
            $table->index('created_at');
            $table->index('updated_at');
        });

        // Логирование создания таблицы
        Log::info('Catalog offers prices table created');
    }

    /*
     * Откат миграции
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_offers_prices');
    }
};
