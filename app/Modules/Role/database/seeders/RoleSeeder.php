<?php

namespace App\Modules\Role\Database\Seeders;

use App\Modules\Role\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'id' => 1, 
                'name' => 'Администратор',
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2, 
                'name' => 'Модератор',
                'is_system' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3, 
                'name' => 
                'Пользователь',
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Вставляем или обновляем роли
        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['id' => $role['id']], // Ищем по ID
                $role // Обновляем или создаем с этими данными
            );
        }

        // Синхронизируем последовательность для PostgreSQL
        if (config('database.default') === 'pgsql') {
            $maxId = DB::table('roles')->max('id'); // Теперь DB будет распознан
            DB::statement("SELECT setval('roles_id_seq', ?, false)", [$maxId + 1]);
        }
    }
}