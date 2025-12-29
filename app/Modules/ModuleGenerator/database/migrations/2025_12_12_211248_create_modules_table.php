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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('code_module', 100)->unique();
            $table->string('slug', 255)->unique();
            $table->boolean('status')->default(true);
            $table->boolean('option_seo')->default(false);
            $table->boolean('option_trash')->default(false);
            $table->text('description')->nullable();

            $table->text('meta_title')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_img_alt')->nullable();
            $table->text('meta_img_title')->nullable();

            // Связь с пользователем
            $table->foreignId('created_by')->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->foreignId('updated_by')->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};