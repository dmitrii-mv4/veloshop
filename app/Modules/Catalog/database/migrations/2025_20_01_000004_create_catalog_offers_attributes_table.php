<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для создания таблицы атрибутов предложений (catalog_offers_attributes)
 * 
 * Таблица характеристик предложений (цвет, размер и т.д.)
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
        Schema::create('catalog_offers_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('offers_id', 50)->comment('Ссылка на предложение');
            $table->string('attributes_type', 100)->comment('Тип атрибута (color, size и т.д.)');
            $table->string('attributes_value', 255)->comment('Значение атрибута');
            $table->timestamps();

            // Внешние ключи
            $table->foreign('offers_id')
                  ->references('offers_id')
                  ->on('catalog_product_offers')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            // Индексы
            $table->index('offers_id');
            $table->index('attributes_type');
            $table->index('attributes_value');
            
            // Составной индекс для быстрого поиска по типу и значению
            $table->index(['attributes_type', 'attributes_value']);
        });
    }

    /**
     * Откат миграции
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalog_offers_attributes');
    }
};