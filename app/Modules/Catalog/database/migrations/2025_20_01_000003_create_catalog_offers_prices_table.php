<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для создания таблицы цен предложений (catalog_offers_prices)
 * 
 * Таблица различных типов цен для каждого предложения товара
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
        Schema::create('catalog_offers_prices', function (Blueprint $table) {
            $table->id();
            $table->string('offer_id', 50)->comment('Ссылка на предложение');
            $table->string('price_type', 50)->comment('Тип цены (uprice, price_marketplace и т.д.)');
            $table->decimal('price', 12, 2)->comment('Значение цены');
            $table->timestamps();

            // Внешние ключи
            $table->foreign('offer_id')
                  ->references('offer_id')
                  ->on('catalog_product_offers')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            // Составной уникальный индекс для offer_id и price_type
            $table->unique(['offer_id', 'price_type']);

            // Индексы
            $table->index('offer_id');
            $table->index('price_type');
            $table->index('price');
        });
    }

    /**
     * Откат миграции
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalog_offers_prices');
    }
};