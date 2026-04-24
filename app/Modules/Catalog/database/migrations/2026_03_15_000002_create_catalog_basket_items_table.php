<?php

use App\Modules\Catalog\Models\Basket;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_basket_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Basket::class, 'basket_id')->comment('ID корзины');
            $table->foreignIdFor(Offer::class, 'offer_id')->comment('ID оффера');

            // Уникальность пары (корзина + оффер)
            $table->unique(['basket_id', 'offer_id'], 'basket_offer_unique');

            // TODO: пока непонятно нужна ли здесь цена
            // $table->decimal('price', 12)->default(0)->comment('Цена товара');
            $table->integer('quantity')->default(0)->comment('Количество товара');

            $table->timestamps();
        });

        Log::info('Migration created: catalog_basket_items table');
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_basket_items');
        Log::info('Migration rolled back: catalog_basket_items table');
    }
};
