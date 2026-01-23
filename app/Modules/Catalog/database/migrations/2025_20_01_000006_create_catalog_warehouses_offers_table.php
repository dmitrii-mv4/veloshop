<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для создания таблицы наличия товаров на складах (catalog_warehouses_offers)
 * 
 * Таблица связывает предложения товаров со складами и указывает количество
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
        Schema::create('catalog_warehouses_offers', function (Blueprint $table) {
            $table->string('offer_id', 50)->comment('Ссылка на предложение');
            $table->unsignedBigInteger('warehouses_id')->comment('Ссылка на склад');
            $table->integer('quantity')->default(0)->comment('Количество товара на складе');
            $table->timestamps();

            // Составной первичный ключ
            $table->primary(['offer_id', 'warehouses_id']);

            // Внешние ключи
            $table->foreign('offer_id')
                  ->references('offer_id')
                  ->on('catalog_product_offers')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('warehouses_id')
                  ->references('id')
                  ->on('catalog_warehouses')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            // Индексы
            $table->index('offer_id');
            $table->index('warehouses_id');
            $table->index('quantity');
            $table->index('created_at');
        });
    }

    /**
     * Откат миграции
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalog_warehouses_offers');
    }
};