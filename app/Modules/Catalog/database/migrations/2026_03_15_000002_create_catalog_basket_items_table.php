<?php

use App\Modules\Catalog\Models\Basket;
use App\Modules\Catalog\Models\Offer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_basket_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Basket::class, 'basket_id')->comment('ID корзины');
            $table->foreignIdFor(Offer::class, 'offer_id')->comment('ID оффера');
            $table->integer('quantity')->default(0)->comment('Количество товара');
            $table->timestamps();

            // Уникальность пары (корзина + оффер)
            $table->unique(['basket_id', 'offer_id'], 'basket_offer_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_basket_items');
    }
};
