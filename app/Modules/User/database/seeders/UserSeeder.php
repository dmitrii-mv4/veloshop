<?php
// app/Modules/User/database/seeders/UserSeeder.php

namespace App\Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Modules\User\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем администратора
        User::create([
            'name' => 'Администратор',
            'email' => 'admin@kotiks.local',
            'email_verified_at' => now(),
            'password' => Hash::make('kotiks2025'),
            'role_id' => 1, // ID роли администратора
            'is_system' => true,
            'is_lang' => 'ru',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Можно создать дополнительные тестовые пользователи
        // User::factory()->count(5)->create();
    }
}