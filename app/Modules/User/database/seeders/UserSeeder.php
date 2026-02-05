<?php
// app/Modules/User/database/seeders/UserSeeder.php

namespace App\Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Log;

class UserSeeder extends Seeder
{
    /**
     * Заполнение таблицы пользователей начальными данными.
     * Создает системных администраторов для работы с CMS.
     * 
     * @return void
     */
    public function run(): void
    {
        try {
            Log::info('Запуск сидера пользователей...');
            
            // Создаем основного администратора
            $admin = User::create([
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
            
            Log::info('Создан основной администратор: ' . $admin->email);
            
            // Создаем пользователя для 1C выгрузки
            $oneCUser = User::create([
                'name' => '1C выгрузка',
                'email' => 'admin1c@kotiks.local',
                'email_verified_at' => now(),
                'password' => Hash::make('kotiks2025'),
                'role_id' => 1, // ID роли администратора
                'is_system' => true,
                'is_lang' => 'ru',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            Log::info('Создан пользователь для 1C выгрузки: ' . $oneCUser->email);
            
            // Логируем общее количество созданных пользователей
            $userCount = User::count();
            Log::info("Сидер пользователей завершен. Всего пользователей в системе: {$userCount}");
            
        } catch (\Exception $e) {
            Log::error('Ошибка при выполнении сидера пользователей: ' . $e->getMessage());
            Log::error('Трассировка: ' . $e->getTraceAsString());
            throw $e;
        }
    }
}