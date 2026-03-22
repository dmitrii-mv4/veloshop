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

        // Добавление в БД
        DB::table('menu_types')->insert(
        [
            [
                'id' => '1',
                'name' => 'top_header',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '2',
                'name' => 'footer',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
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