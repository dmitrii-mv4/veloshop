<?php

namespace App\Modules\Menu\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MenuTypeSeeder extends Seeder
{
    /**
     * Массив типов меню по умолчанию.
     * 
     * @var array
     */
    protected array $defaultMenuTypes = [
        [
            'name' => 'header',
            'title' => 'Верхнее меню',
            'description' => 'Основное меню в шапке сайта',
        ],
        [
            'name' => 'footer',
            'title' => 'Нижнее меню',
            'description' => 'Меню в подвале сайта',
        ],
        [
            'name' => 'sidebar',
            'title' => 'Боковое меню',
            'description' => 'Боковое меню на страницах',
        ],
        [
            'name' => 'mobile',
            'title' => 'Мобильное меню',
            'description' => 'Меню для мобильных устройств',
        ],
    ];

    /**
     * Заполнение таблицы типов меню начальными данными.
     * Создает системные типы меню для работы с CMS.
     * 
     * @return void
     */
    public function run(): void
    {
        try {
            Log::info('Запуск сидера типов меню...');
            
            // Проверяем существование таблицы
            if (!Schema::hasTable('menu_types')) {
                Log::error('Таблица menu_types не существует!');
                throw new \Exception('Таблица menu_types не найдена в базе данных');
            }
            
            $createdCount = 0;
            $skippedCount = 0;
            
            foreach ($this->defaultMenuTypes as $menuType) {
                // Проверяем, существует ли уже тип меню с таким именем
                if (DB::table('menu_types')->where('name', $menuType['name'])->exists()) {
                    Log::warning("Тип меню '{$menuType['name']}' уже существует. Пропускаем.");
                    $skippedCount++;
                    continue;
                }
                
                // Вставляем запись
                DB::table('menu_types')->insert([
                    'name' => $menuType['name'],
                    'title' => $menuType['title'] ?? ucfirst($menuType['name']),
                    'description' => $menuType['description'] ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                Log::info("Создан тип меню: {$menuType['name']}");
                $createdCount++;
            }
            
            // Логируем итоги
            $totalMenuTypes = DB::table('menu_types')->count();
            Log::info("Сидер типов меню завершен.");
            Log::info("Создано новых типов меню: {$createdCount}");
            Log::info("Пропущено (уже существовали): {$skippedCount}");
            Log::info("Всего типов меню в системе: {$totalMenuTypes}");
            
        } catch (\Exception $e) {
            Log::error('Ошибка при выполнении сидера типов меню: ' . $e->getMessage());
            Log::error('Трассировка: ' . $e->getTraceAsString());
            
            // Не выбрасываем исключение дальше, чтобы не прерывать установку полностью
            // Просто логируем и продолжаем
            Log::warning('Продолжаем установку несмотря на ошибку в сидере меню');
        }
    }
}