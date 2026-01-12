<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('filename')->nullable();
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('disk')->default('public');
            $table->enum('type', ['file', 'folder'])->default('file');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            // Внешний ключ для иерархии папок
            $table->foreign('parent_id')->references('id')->on('media')->onDelete('cascade');
            
            $table->index('user_id');
            $table->index('mime_type');
            $table->index('type');
            $table->index('parent_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};