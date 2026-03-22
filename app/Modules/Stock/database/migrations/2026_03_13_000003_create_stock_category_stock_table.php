<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_category_stock', function (Blueprint $table) {
            $table->foreignId('stock_id')->constrained('stock')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('stock_categories')->onDelete('cascade');
            $table->primary(['stock_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_category_stock');
    }
};