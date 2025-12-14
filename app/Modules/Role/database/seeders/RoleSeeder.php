<?php

namespace App\Modules\Role\Database\Seeders;

use App\Modules\Role\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'id' => 1, 
                'name' => 'Администратор',
                'is_system' => true,
            ],
            [
                'id' => 2, 
                'name' => 'Модератор',
                'is_system' => false,
            ],
            [
                'id' => 3, 
                'name' => 
                'Пользователь',
                'is_system' => false,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['id' => $role['id']], // Ищем по ID
                $role // Обновляем или создаем с этими данными
            );
        }
    }
}