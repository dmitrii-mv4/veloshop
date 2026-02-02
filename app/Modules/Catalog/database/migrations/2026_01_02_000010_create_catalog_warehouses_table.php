<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            $table->string('title', 255)->comment('Название склада');
            $table->text('description')->nullable()->comment('Описание склада');
            $table->text('contacts')->nullable()->comment('Контактная информация склада');
            $table->boolean('is_active')->default(true)->comment('Статус активности склада');
            $table->unsignedInteger('sort_order')->default(100)->comment('Порядок сортировки');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('ID пользователя, обновившего запись');
            $table->unsignedBigInteger('created_by')->nullable()->comment('ID пользователя, создавшего запись');
            $table->timestamps();

            // Индексы для оптимизации запросов
            $table->index('is_active');
            $table->index('created_by');
            $table->index('updated_by');
            
            Log::info('Таблица catalog_warehouses создана успешно');
        });
    }

    /**
     * Откат миграции - удаление таблицы складов
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_warehouses');
        Log::info('Таблица catalog_warehouses удалена');
    }
};