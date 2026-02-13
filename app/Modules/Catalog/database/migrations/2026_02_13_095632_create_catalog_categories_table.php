<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('catalog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Наименование категории');
            $table->string('slug')->comment('Слаг категории');
            $table->string('external_id')->nullable()->comment('Внешний id категории');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('Идентификатор родительской категории');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_categories');
    }
};
