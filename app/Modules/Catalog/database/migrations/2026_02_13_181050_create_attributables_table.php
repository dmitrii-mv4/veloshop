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
        Schema::create('attributables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('catalog_attribute_id')->comment('ID атрибута');
            $table->morphs('attributable');
            $table->string('value')->comment('Значение атрибута');
            $table->timestamps();

            $table->foreign('catalog_attribute_id')
                ->references('id')
                ->on('catalog_attributes')
                ->onDelete('cascade');
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
