<?php

namespace App\Admin\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Admin\Models\Settings;
use Illuminate\Support\Facades\Log;

class SettingsSeeder extends Seeder
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
            // Создаем основного администратора
            $site = Settings::create([
                'name_site' => 'Велошоп',
                'url_site' => 'https://test.velo-shop.ru',
                'description_site' => 'Велосипеды ✓ Купить в ВелоШопе ⭐ В наличии: 4121 шт.! ⚡ Рассрочка и кредит $ лучшие цены на велосипеды 🚀 Быстрая доставка в Москве.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при выполнении сидера настроек для сайта: ' . $e->getMessage());
            Log::error('Трассировка: ' . $e->getTraceAsString());
            throw $e;
        }
    }
}