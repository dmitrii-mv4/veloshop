<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Создание таблицы пунктов меню
     * Поддерживает древовидную структуру через parent_id
     * Каскадное удаление при удалении родительского меню
     */
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade')->comment('ID родительского меню');
            $table->string('title', 255)->comment('Название пункта меню');
            $table->string('url', 500)->default('/')->comment('URL адрес пункта');
            $table->string('icon', 100)->nullable()->comment('Иконка Bootstrap');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('ID родительского пункта меню');
            $table->integer('order')->default(0)->comment('Порядок сортировки');
            $table->boolean('is_active')->default(true)->comment('Активность пункта');
            $table->string('seo_title', 255)->nullable()->comment('SEO заголовок');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('ID пользователя, создавшего пункт');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->comment('ID пользователя, обновившего пункт');
            $table->timestamps();
            
            // Внешний ключ для parent_id (самореференс)
            $table->foreign('parent_id')->references('id')->on('menu_items')->onDelete('cascade');
            
            // Индексы для оптимизации
            $table->index('menu_id');
            $table->index('parent_id');
            $table->index('order');
            $table->index('is_active');
            $table->index(['menu_id', 'parent_id', 'order']);
        });
        
        Log::info('Создана таблица menu_items');
    }

    /**
     * Удаление таблицы пунктов меню
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
        Log::info('Удалена таблица menu_items');
    }
};