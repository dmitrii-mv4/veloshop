<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_customers_type', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('Название типа покупателя (физлицо, юрлицо)');
            $table->boolean('is_active')->default(true)->comment('Флаг активности (true - активен, false - неактивен)');
            $table->unsignedBigInteger('created_by')->nullable()->comment('ID создавшего пользователя');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('ID обновившего пользователя');
            $table->unsignedBigInteger('deleted_by')->nullable()->comment('ID удалившего пользователя');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
        });

        DB::table('catalog_customers_type')->insert([
            [
                'title' => 'Физ. лицо',
                'is_active' => true,
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Юр. лицо',
                'is_active' => true,
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_customers_type');
    }
};