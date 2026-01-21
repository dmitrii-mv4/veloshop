<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Миграция для создания таблицы складов (catalog_warehouses)
 * 
 * Таблица физических складов товаров
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
        Schema::create('catalog_warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('address', 500)->comment('Адрес склада');
            $table->string('phone', 50)->nullable()->comment('Телефон склада');
            $table->string('email', 100)->nullable()->comment('Email склада');
            $table->text('operating_mode')->nullable()->comment('Режим работы склада');
            $table->text('description')->nullable()->comment('Описание склада');
            $table->timestamps();

            // Индексы
            $table->index('address');
            $table->index('email');
            $table->index('created_at');
        });

        // Для PostgreSQL: создаем GIN индекс для полнотекстового поиска
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX catalog_warehouses_search_idx ON catalog_warehouses USING GIN (to_tsvector(\'russian\', COALESCE(address, \'\') || \' \' || COALESCE(operating_mode, \'\') || \' \' || COALESCE(description, \'\')))');
        }
    }

    /**
     * Откат миграции
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalog_warehouses');
    }
};