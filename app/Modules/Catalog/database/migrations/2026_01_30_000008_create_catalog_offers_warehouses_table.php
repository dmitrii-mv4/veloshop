<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для создания таблицы связи товарных предложений со складами
 * Хранит информацию о количестве товаров на складах
 *
 * ПРИМЕЧАНИЕ: offer_id должен соответствовать типу id в таблице catalog_product_offers
 * Для корректной работы с строковыми идентификаторами используется varchar
 */
return new class extends Migration
{
    /**
     * Запуск миграции для создания таблицы связи
     */
    public function up(): void
    {
        Schema::create('catalog_offers_warehouses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_id')->comment('ID офера');
            $table->unsignedBigInteger('warehouse_id')->comment('ID склада');
            $table->integer('count')->default(0)->comment('Количество товара на складе');
            $table->integer('reserved')->default(0)->comment('Зарезервированное количество');
            $table->unsignedInteger('sort_order')->default(100)->comment('Порядок сортировки');
            $table->timestamps();

            // Уникальный индекс для предотвращения дублирования связей
            $table->unique(['offer_id', 'warehouse_id']);

            // Внешние ключи
            $table->foreign('offer_id')
                ->references('id')
                ->on('catalog_product_offers')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('catalog_warehouses')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Откат миграции - удаление таблицы связи
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_offers_warehouses');
    }
};
