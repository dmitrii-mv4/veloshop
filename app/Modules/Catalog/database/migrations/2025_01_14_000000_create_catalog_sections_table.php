<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_sections', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Название раздела');
            $table->string('slug')->unique()->comment('URL-адрес раздела');
            $table->text('description')->nullable()->comment('Описание раздела');
            
            // Иерархия
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('catalog_sections')
                  ->nullOnDelete()
                  ->comment('Родительский раздел');
            
            $table->integer('sort_order')->default(0)->comment('Порядок сортировки');
            
            // SEO
            $table->string('meta_title')->nullable()->comment('Мета-заголовок (title)');
            $table->string('meta_keywords')->nullable()->comment('Мета-ключевые слова');
            $table->string('meta_description')->nullable()->comment('Мета-описание');
            
            // Статус и автор
            $table->boolean('is_active')->default(true)->comment('Активен раздел');
            $table->foreignId('author_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->comment('Автор раздела');
            
            // Временные метки
            $table->timestamps();
            $table->softDeletes();
            
            // Индексы
            $table->index('parent_id');
            $table->index('is_active');
            $table->index('sort_order');
            $table->index('slug');
        });
        
        // УБИРАЕМ добавление section_id из этой миграции,
        // так как теперь он добавляется в миграции catalog_goods
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_sections');
    }
};