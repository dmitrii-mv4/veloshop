<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для создания таблицы складов в модуле Каталог
 * Содержит основную информацию о складах
 */
return new class extends Migration
{
    /**
     * Запуск миграции для создания таблицы складов
     */
    public function up(): void
    {
        Schema::create('catalog_warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('warehouse_id', 50)->unique()->comment('Уникальный идентификатор склада');
            $table->string('title', 255)->comment('Название склада');
            $table->text('description')->nullable()->comment('Описание склада');
            $table->text('contacts')->nullable()->comment('Контактная информация склада');
            $table->boolean('is_active')->default(true)->comment('Статус активности склада');
            $table->unsignedInteger('sort_order')->default(100)->comment('Порядок сортировки');
            $table->timestamps();

            // Индексы для оптимизации запросов
            $table->index('is_active');
        });
    }

    /**
     * Откат миграции - удаление таблицы складов
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_warehouses');
    }
};
