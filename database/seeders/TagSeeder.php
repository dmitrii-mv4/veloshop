<?php

namespace Database\Seeders;

use App\Modules\Catalog\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Сидер для заполнения таблицы тегов
 */
class TagSeeder extends Seeder
{
    /**
     * Запуск сидера
     *
     * @return void
     */
    public function run(): void
    {
        $tags = [
            [
                'name' => 'Хит',
                'slug' => 'hit',
            ],
            [
                'name' => 'Распродажа',
                'slug' => 'rasprodazha',
            ],
            [
                'name' => 'Новинка',
                'slug' => 'novinka',
            ],
        ];

        foreach ($tags as $tagData) {
            Tag::firstOrCreate(
                ['slug' => $tagData['slug']],
                $tagData
            );
        }

        $this->command->info('Теги успешно добавлены!');
    }
}
