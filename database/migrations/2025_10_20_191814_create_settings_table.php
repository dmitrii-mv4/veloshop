<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('name_site');
            $table->string('url_site');
            $table->string('description_site');
            $table->timestamps();
        });

        // Добавление в БД
        DB::table('settings')->insert(
        [
            [
                'id' => '1',
                'name_site' => 'Мой сайт',
                'url_site' => '/',
                'description_site' => '',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
