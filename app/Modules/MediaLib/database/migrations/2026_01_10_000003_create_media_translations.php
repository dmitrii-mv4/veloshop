<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Указываем, что миграция не должна выполняться в транзакции
     */
    public function withinTransaction(): bool
    {
        return false;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Сначала создаем таблицу без внешнего ключа
        Schema::create('media_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('media_id'); // Изменили media_file_id на media_id
            $table->string('locale')->index(); // ru, en
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('alt')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['media_id', 'locale']); // Изменили здесь тоже
        });

        // Используем DB::statement для создания внешнего ключа
        // Этот метод не оборачивается в транзакцию
        DB::statement('
            ALTER TABLE media_translations 
            ADD CONSTRAINT media_translations_media_id_foreign 
            FOREIGN KEY (media_id) 
            REFERENCES media(id)  -- Изменили media_files на media
            ON DELETE CASCADE
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Удаляем внешний ключ с помощью DB::statement
        DB::statement('
            ALTER TABLE media_translations 
            DROP CONSTRAINT media_translations_media_id_foreign  -- Изменили здесь
        ');
        
        Schema::dropIfExists('media_translations');
    }
};