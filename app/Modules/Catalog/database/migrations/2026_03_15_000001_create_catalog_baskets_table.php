<?php

use App\Modules\Catalog\Models\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

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

            // Агрегированные данные
            $table->decimal('total_price', 12)->default(0)->comment('Общая стоимость корзины');
            $table->integer('total_quantity')->default(0)->comment('Общее количество товаров в корзине');

            // Служебные поля
            $table->unsignedBigInteger('created_by')->nullable()->comment('Кто создал');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Кто изменил');

            $table->timestamps();

            // Индексы
            $table->index('created_by');
            $table->index('updated_by');

            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->foreign('updated_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });

        Log::info('Migration created: catalog_baskets table');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_baskets');
        Log::info('Migration rolled back: catalog_baskets table');
    }
};
