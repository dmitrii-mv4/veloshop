<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Миграция для создания дополнительных индексов для оптимизации производительности
 *
 * Создает индексы для часто используемых запросов
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
        // Создаем индекс для поиска по полю created_by в таблице catalog_products
        DB::statement('CREATE INDEX IF NOT EXISTS catalog_products_created_by_index ON catalog_products(created_by)');

        // Создаем индекс для поиска по полю updated_by в таблице catalog_products
        DB::statement('CREATE INDEX IF NOT EXISTS catalog_products_updated_by_index ON catalog_products(updated_by)');

        // Создаем индекс для поиска по полю created_by в таблице catalog_product_offers
        DB::statement('CREATE INDEX IF NOT EXISTS catalog_product_offers_created_by_index ON catalog_product_offers(created_by)');

        // Создаем индекс для поиска по полю updated_by в таблице catalog_product_offers
        DB::statement('CREATE INDEX IF NOT EXISTS catalog_product_offers_updated_by_index ON catalog_product_offers(updated_by)');

        // Создаем индекс для поиска предложений по артикулу
        DB::statement('CREATE INDEX IF NOT EXISTS catalog_product_offers_articul_supplier_index ON catalog_product_offers(articul_supplier)');

        // Логируем создание индексов
        \Illuminate\Support\Facades\Log::info('Catalog module indexes created');
    }

    /**
     * Откат миграции
     *
     * @return void
     */
    public function down()
    {
        // Удаляем созданные индексы
        DB::statement('DROP INDEX IF EXISTS catalog_products_created_by_index');
        DB::statement('DROP INDEX IF EXISTS catalog_products_updated_by_index');
        DB::statement('DROP INDEX IF EXISTS catalog_product_offers_created_by_index');
        DB::statement('DROP INDEX IF EXISTS catalog_product_offers_updated_by_index');
        DB::statement('DROP INDEX IF EXISTS catalog_product_offers_articul_supplierindex');

        \Illuminate\Support\Facades\Log::info('Catalog module indexes dropped');
    }
};
