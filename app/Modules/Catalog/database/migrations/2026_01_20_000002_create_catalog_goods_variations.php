<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_goods_variations', function (Blueprint $table) {
            $table->id();
            
            // Уникальный идентификатор вариации
            $table->string('id_variation', 50)->unique()->nullable()->comment('Уникальный артикул вариации товара');
            
            // Магазин
            $table->integer('store')->default(0)->comment('Магазин');
            
            // Ценовые поля
            $table->decimal('uprice', 12, 2)->default(0)->comment('Закупочная цена');
            $table->decimal('price_marketplace', 12, 2)->default(0)->comment('Цена на маркетплейсе');
            $table->decimal('price_loyal_card', 12, 2)->default(0)->comment('Цена по карте лояльности');
            $table->decimal('price_yandex_dbs', 12, 2)->default(0)->comment('Цена Яндекс DBS');
            $table->decimal('price_ozon_dbs', 12, 2)->default(0)->comment('Цена Ozon DBS');
            $table->decimal('price_ozon_fbs', 12, 2)->default(0)->comment('Цена Ozon FBS');
            $table->decimal('price', 12, 2)->default(0)->comment('Основная цена продажи');
            $table->decimal('price1c', 12, 2)->default(0)->comment('Цена для 1С');
            
            // Название и мета-поля
            $table->string('name', 255)->nullable()->comment('Название вариации');
            $table->string('meta_title', 255)->nullable()->comment('Meta Title для SEO');
            $table->text('meta_description')->nullable()->comment('Meta Description для SEO');
            $table->text('meta_keywords')->nullable()->comment('Meta Keywords для SEO');

            // Кто изменил
            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            // Кто добавил
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Индексы для оптимизации запросов
            $table->index('id_variation');
            $table->index('store');
            $table->index(['price', 'store']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_goods_variations');
    }
};