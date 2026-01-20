<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_goods_variations_size', function (Blueprint $table) {
            $table->id();
            
            $table->string('id_variation')->nullable()->comment('Артикул вариации товара');
            $table->string('value')->nullable()->comment('Значение размера');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_goods_variations_size');
    }
};