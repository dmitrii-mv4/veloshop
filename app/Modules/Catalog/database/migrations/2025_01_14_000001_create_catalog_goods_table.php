<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_goods', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            
            // Добавляем связь с разделами
            $table->foreignId('section_id')
                  ->nullable()
                  ->constrained('catalog_sections')
                  ->nullOnDelete()
                  ->comment('Раздел каталога');

            $table->string('meta_title', 255)->nullable()->comment('Meta Title для SEO');
            $table->text('meta_description')->nullable()->comment('Meta Description для SEO');
            $table->text('meta_keywords')->nullable()->comment('Meta Keywords для SEO');

            // Кто изменил
            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            // Кто добавил
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Индексы
            $table->index('updated_by');
            $table->index('created_by');
            $table->index('section_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_goods');
    }
};