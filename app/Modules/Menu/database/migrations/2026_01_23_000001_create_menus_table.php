<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Создание таблицы меню
     * Оригинальная версия с полем menu_type_id (будет изменена позже)
     */
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('Название меню');
            $table->text('description')->nullable()->comment('Описание меню');
            $table->unsignedBigInteger('menu_type_id')->nullable()->comment('ID типа меню');
            $table->boolean('is_active')->default(true)->comment('Активность меню');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('ID пользователя, создавшего меню');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->comment('ID пользователя, обновившего меню');
            $table->timestamps();

            // Внешний ключ для типа меню
            $table->foreign('menu_type_id')
                ->references('id')
                ->on('menu_types')
                ->onDelete('set null')
                ->onUpdate('cascade');
            
            // Индексы для оптимизации
            $table->index('menu_type_id');
            $table->index('is_active');
            $table->index(['menu_type_id', 'is_active']);
        });
        
        Log::info('Создана таблица menus');

        // Добавление в БД
        DB::table('menus')->insert(
        [
            [
                'id' => '1',
                'name' => 'Верхнее меню',
                'description' => 'Основное верхнее меню',
                'menu_type_id' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],

            [
                'id' => '2',
                'name' => 'Нижнее меню Каталог',
                'description' => 'Нижнее меню Каталог',
                'menu_type_id' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }

    /**
     * Удаление таблицы меню
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
        Log::info('Удалена таблица menus');
    }
};