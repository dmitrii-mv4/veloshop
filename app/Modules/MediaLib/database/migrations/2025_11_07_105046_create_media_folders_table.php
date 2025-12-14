<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('path');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            // Внешний ключ для иерархии папок
            $table->foreign('parent_id')->references('id')->on('media_folders')->onDelete('cascade');
            
            $table->index('parent_id');
            $table->index('user_id');
            $table->index('created_at');
        });

        // Обновляем таблицу media - ПРАВИЛЬНАЯ ПОСЛЕДОВАТЕЛЬНОСТЬ
        Schema::table('media', function (Blueprint $table) {
            // 1. Сначала удаляем внешний ключ
            $table->dropForeign(['parent_id']);
            
            // 2. Затем удаляем индекс
            $table->dropIndex(['parent_id']);
            $table->dropIndex(['type']);
            
            // 3. Теперь удаляем колонки
            $table->dropColumn(['type', 'parent_id']);
            
            // 4. Добавляем новую колонку
            $table->unsignedBigInteger('folder_id')->nullable()->after('user_id');
            
            // 5. Создаем новый внешний ключ
            $table->foreign('folder_id')->references('id')->on('media_folders')->onDelete('cascade');
            $table->index('folder_id');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            // Удаляем новое
            $table->dropForeign(['folder_id']);
            $table->dropIndex(['folder_id']);
            $table->dropColumn('folder_id');

            // Восстанавливаем старое
            $table->enum('type', ['file', 'folder'])->default('file');
            $table->unsignedBigInteger('parent_id')->nullable();
            
            // Восстанавливаем индексы и внешние ключи
            $table->index('parent_id');
            $table->index('type');
            $table->foreign('parent_id')->references('id')->on('media')->onDelete('cascade');
        });
        
        Schema::dropIfExists('media_folders');
    }
};