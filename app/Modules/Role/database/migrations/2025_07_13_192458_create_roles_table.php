<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        // Laravel автоматически создаст последовательность для id
        // Дополнительно синхронизируем последовательность с существующими данными
        if (config('database.default') === 'pgsql') {
            $this->syncSequence();
        }
    }

    public function down(): void
    {
        // Для PostgreSQL: сначала удаляем таблицу, потом последовательность
        Schema::dropIfExists('roles');
        
        if (config('database.default') === 'pgsql') {
            // Удаляем последовательность после удаления таблицы
            DB::statement('DROP SEQUENCE IF EXISTS roles_id_seq CASCADE');
        }
    }
    
    /**
     * Синхронизация последовательности с данными в таблице
     */
    private function syncSequence(): void
    {
        try {
            // Ждем немного, чтобы убедиться что таблица создана
            sleep(1);
            
            // Проверяем, есть ли данные в таблице
            $hasData = DB::table('roles')->exists();
            
            if ($hasData) {
                // Получаем текущий максимальный ID
                $maxId = DB::table('roles')->max('id');
                
                // Получаем имя последовательности
                $sequence = DB::selectOne("
                    SELECT pg_get_serial_sequence('roles', 'id') as seq_name
                ");
                
                if ($sequence && $sequence->seq_name) {
                    // Синхронизируем последовательность с максимальным ID
                    DB::statement("SELECT setval(?, ?, false)", [
                        $sequence->seq_name,
                        $maxId + 1
                    ]);
                    
                    echo "Последовательность синхронизирована с максимальным ID: " . ($maxId + 1) . "\n";
                }
            }
        } catch (\Exception $e) {
            echo "Ошибка при синхронизации последовательности: " . $e->getMessage() . "\n";
        }
    }
};