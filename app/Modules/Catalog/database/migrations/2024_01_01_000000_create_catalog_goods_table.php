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
            $table->string('articul');
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Внешний ключ
            $table->index('author_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_goods');
    }
};