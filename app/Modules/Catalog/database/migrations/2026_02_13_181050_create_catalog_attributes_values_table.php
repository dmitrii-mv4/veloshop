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
        Schema::create('catalog_attributes_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attribute_id')->comment('ID атрибута');
            $table->unsignedBigInteger('attributable_id')->comment('ID модели');
            $table->string('attributable_type')->comment('Класс модели');
            $table->string('value')->comment('Значение атрибута');
            $table->timestamps();

            $table->foreign('attribute_id')
                ->references('id')
                ->on('catalog_attributes')
                ->onDelete('cascade');

            $table->index(['attributable_id', 'attributable_type'], 'attributable_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_attributes_values');
    }
};
