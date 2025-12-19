<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Таблица пользователей
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            
            // Роль пользователя
            $table->unsignedBigInteger('role_id')->default(2);
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('restrict');
            
            // Аватар с более гибким значением по умолчанию
            $table->string('avatar')->nullable()->default(null);
            
            $table->string('password');
            
            $table->string('phone', 20)->nullable();
            $table->string('position', 100)->nullable();
            $table->text('bio')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_system')->default(false);
            $table->string('is_local')->default('ru');
            
            $table->rememberToken();
            $table->timestamps();
            
            // Индексы для оптимизации запросов
            $table->index('role_id');
            $table->index('is_active');
            $table->index('email');
        });

        // Таблица для сброса паролей
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
            
            // Добавляем индекс на токен для более быстрого поиска[citation:8]
            $table->index('token');
        });

        // Таблица сессий
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
            
            // Добавляем внешний ключ для целостности данных
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Удаляем в правильном порядке (учитывая внешние ключи)
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};