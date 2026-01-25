<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Создание таблицы типов меню
     * Хранит типы меню (header, footer и т.д.)
     */
    public function up(): void
    {
        Schema::create('menu_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->comment('Название типа меню');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('ID пользователя, создавшего тип меню');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->comment('ID пользователя, обновившего тип меню');
            $table->timestamps();
            
            // Индексы для оптимизации
            $table->index('name');
        });
        
        Log::info('Создана таблица menu_types');
    }

    /**
     * Удаление таблицы типов меню
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_types');
        Log::info('Удалена таблица menu_types');
    }
};