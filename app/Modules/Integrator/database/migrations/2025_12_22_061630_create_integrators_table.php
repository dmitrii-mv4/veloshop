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
        Schema::create('integrators', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();
            $table->text('integration_description')->nullable();
            $table->boolean('is_active')->default(false);

            // Для хранения конфигурации интеграции (JSON для гибкости)
            $table->json('config')->nullable();

            $table->string('version')->default('1.0.0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integrators');
    }
};
