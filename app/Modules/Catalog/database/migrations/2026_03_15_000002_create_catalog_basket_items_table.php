<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_basket_items', function (Blueprint $table) {
            $table->id();
            
            // ID корзины (bigint)
            $table->unsignedBigInteger('catalog_basket_id');
            
            // ID оффера — строка, т.к. в catalog_product_offers.offer_id имеет тип varchar
            $table->string('offer_id', 100)->comment('ID офера (внешний ключ к catalog_product_offers.offer_id)');
            
            // Уникальность пары (корзина + оффер)
            $table->unique(['catalog_basket_id', 'offer_id'], 'basket_offer_unique');
            
            $table->timestamps();
            
            // Индексы
            $table->index('catalog_basket_id');
            $table->index('offer_id');
            
            // Внешние ключи
            $table->foreign('catalog_basket_id')
                  ->references('id')
                  ->on('catalog_baskets')
                  ->onDelete('cascade');
                  
            // Внешний ключ на offer_id (должен быть уникальным в catalog_product_offers)
            $table->foreign('offer_id')
                  ->references('offer_id')
                  ->on('catalog_product_offers')
                  ->onDelete('cascade');
        });
        
        Log::info('Migration created: catalog_basket_items table');
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_basket_items');
        Log::info('Migration rolled back: catalog_basket_items table');
    }
};