<?php

use App\Modules\Catalog\Models\Offer;
use App\Modules\Catalog\Models\PriceType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Миграция для создания соединительной таблицы цен и предложений
 */
return new class extends Migration
{
    /*
     * Запуск миграции
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('catalog_offers_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Offer::class, 'offer_id')->comment('ID предложения товара');
            $table->foreignIdFor(PriceType::class, 'price_type_id')->comment('ID типа цены');
            $table->decimal('price', 12)->default(0)->comment('Значение цены');
            $table->timestamps();

            // Составной уникальный индекс для предотвращения дублирования
            $table->unique(['offer_id', 'price_type_id'], 'offer_type_price_unique');

            // Индексы для оптимизации запросов
            $table->index('price');
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    /*
     * Откат миграции
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_offers_prices');
    }
};
