<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Миграция для создания таблицы заказов модуля Catalog
 */
return new class extends Migration
{
    /**
     * Запуск миграции для создания таблицы заказов
     */
    public function up(): void
    {
        Schema::create('catalog_orders', function (Blueprint $table) {
            $table->id();
            
            // Основная информация о заказе
            $table->string('order_number')->unique()->comment('Номер заказа');
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete()->comment('Покупатель');
            $table->boolean('is_paid')->default(false)->comment('Оплачен');
            $table->boolean('is_cancelled')->default(false)->comment('Отменён');
            $table->text('cancellation_reason')->nullable()->comment('Причина отмены заказа');
            $table->boolean('has_problem')->default(false)->comment('Проблема с заказом');
            $table->text('problem_description')->nullable()->comment('Описание проблемы с заказом');
            $table->decimal('total_amount', 10, 2)->default(0)->comment('Сумма заказа');
            $table->foreignId('responsible_id')->nullable()->constrained('users')->nullOnDelete()->comment('Ответственный');
            $table->text('comment')->nullable()->comment('Комментарий');
            
            // Отслеживание пользователей
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->comment('Кто добавил');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->comment('Кто обновил');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete()->comment('Кто удалил');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes()->comment('Дата удаления');
            
            // Индексы
            $table->index('order_number');
            $table->index('customer_id');
            $table->index('is_paid');
            $table->index('is_cancelled');
            $table->index('has_problem');
            $table->index('created_at');
            $table->index('deleted_at');
            
            Log::info('Создана таблица catalog_orders для модуля заказов');
        });
    }

    /**
     * Откат миграции
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_orders');
        Log::info('Удалена таблица catalog_orders для модуля заказов');
    }
};