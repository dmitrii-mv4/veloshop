<?php

use App\Modules\Catalog\Models\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('catalog_baskets', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Customer::class)->comment('ID покупателя (из catalog_customers)');
            $table->decimal('total_price', 12)->default(0)->comment('Общая стоимость корзины');
            $table->integer('total_quantity')->default(0)->comment('Общее количество товаров в корзине');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_baskets');
    }
};
