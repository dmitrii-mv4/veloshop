<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles_category_articles', function (Blueprint $table) {
            $table->foreignId('articles_id')->constrained('articles')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('articles_categories')->onDelete('cascade');
            $table->primary(['articles_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles_category_articles');
    }
};