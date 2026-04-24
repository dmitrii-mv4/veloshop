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
        Schema::create('catalog_taggables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tag_id')->comment('ID тега');
            $table->morphs('taggable');
            $table->timestamps();

            $table->foreign('tag_id')
                ->references('id')
                ->on('catalog_tags')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_taggables');
    }
};
