<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/*
 * Миграция для создания таблицы типов цен (catalog_type_price)
 * с одновременным заполнением начальными данными
 *
 * Таблица для хранения различных типов цен (основная, оптовая, специальная и т.д.)
 * Управляется администратором, типы цен не должны редактироваться в рамках этой задачи
 */
return new class extends Migration
{
    /**
     * Запуск миграции
     *
     * @return void
     */
    public function up(): void
    {
        try {
            Log::info('Запуск миграции создания таблицы catalog_type_price');
            
            Schema::create('catalog_type_price', function (Blueprint $table) {
                $table->id();
                $table->string('title', 100)->comment('Название типа цены');
                $table->string('type', 50)->unique()->comment('Технический идентификатор типа (price1c, price_loyal_card)');
                $table->string('currency', 10)->default('RUB')->comment('Валюта цены ($, RUB)');
                $table->boolean('is_active')->default(true)->comment('Активен ли тип цены');
                $table->unsignedInteger('sort_order')->default(100)->comment('Порядок сортировки');
                $table->unsignedBigInteger('updated_by')->nullable()->comment('ID пользователя, обновившего запись');
                $table->unsignedBigInteger('created_by')->nullable()->comment('ID пользователя, создавшего запись');
                $table->timestamps();

                // Индексы
                $table->index('type');
                $table->index('is_active');
                $table->index('sort_order');
            });

            Log::info('Таблица catalog_type_price создана успешно');

            // Наполняем таблицу начальными данными
            $this->seedTypePriceData();

        } catch (\Exception $e) {
            Log::error('Ошибка при выполнении миграции catalog_type_price', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Заполнение таблицы начальными данными
     *
     * @return void
     */
    private function seedTypePriceData(): void
    {
        try {
            Log::info('Начало заполнения таблицы catalog_type_price данными');
            
            // Данные для начального заполнения таблицы типов цен
            $priceTypes = [
                [
                    'title' => 'Основная цена',
                    'type' => 'price',
                    'currency' => 'RUB',
                    'is_active' => true,
                    'sort_order' => 10,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Цена 1С',
                    'type' => 'price1c',
                    'currency' => 'RUB',
                    'is_active' => true,
                    'sort_order' => 20,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Цена маркетплейс',
                    'type' => 'price_marketplace',
                    'currency' => 'RUB',
                    'is_active' => true,
                    'sort_order' => 30,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Цена лояльности',
                    'type' => 'price_loyal_card',
                    'currency' => 'RUB',
                    'is_active' => true,
                    'sort_order' => 40,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Яндекс DBS',
                    'type' => 'price_yandex_dbs',
                    'currency' => 'RUB',
                    'is_active' => true,
                    'sort_order' => 50,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Ozon DBS',
                    'type' => 'price_ozon_dbs',
                    'currency' => 'RUB',
                    'is_active' => true,
                    'sort_order' => 60,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Ozon FBS',
                    'type' => 'price_ozon_fbs',
                    'currency' => 'RUB',
                    'is_active' => true,
                    'sort_order' => 70,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Uprice',
                    'type' => 'uprice',
                    'currency' => 'RUB',
                    'is_active' => true,
                    'sort_order' => 5,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Основная цена 2',
                    'type' => 'price2',
                    'currency' => 'RUB',
                    'is_active' => true,
                    'sort_order' => 15,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            $processed = 0;
            foreach ($priceTypes as $priceType) {
                // Используем insertOrIgnore для избежания ошибок дублирования
                // при повторном запуске миграции
                DB::table('catalog_type_price')->insertOrIgnore($priceType);
                $processed++;
                Log::debug('Добавлен тип цены', ['type' => $priceType['type']]);
            }

            $count = DB::table('catalog_type_price')->count();
            Log::info('Таблица catalog_type_price заполнена начальными данными', [
                'added_count' => $processed,
                'total_in_table' => $count
            ]);

        } catch (\Exception $e) {
            Log::error('Ошибка при заполнении таблицы catalog_type_price', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Откат миграции
     *
     * @return void
     */
    public function down(): void
    {
        try {
            Log::info('Откат миграции catalog_type_price');
            
            // Удаляем таблицу (данные будут удалены автоматически)
            Schema::dropIfExists('catalog_type_price');
            
            Log::info('Таблица catalog_type_price удалена');
            
        } catch (\Exception $e) {
            Log::error('Ошибка при откате миграции catalog_type_price', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
};