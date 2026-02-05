<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Создание таблицы пунктов меню
     * Поддерживает древовидную структуру через parent_id
     * Каскадное удаление при удалении родительского меню
     */
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade')->comment('ID родительского меню');
            $table->string('title', 255)->comment('Название пункта меню');
            $table->string('url', 500)->default('/')->comment('URL адрес пункта');
            $table->string('icon', 100)->nullable()->comment('Иконка Bootstrap');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('ID родительского пункта меню');
            $table->integer('order')->default(0)->comment('Порядок сортировки');
            $table->boolean('is_active')->default(true)->comment('Активность пункта');
            $table->string('seo_title', 255)->nullable()->comment('SEO заголовок');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('ID пользователя, создавшего пункт');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->comment('ID пользователя, обновившего пункт');
            $table->timestamps();
            
            // Внешний ключ для parent_id (самореференс)
            $table->foreign('parent_id')->references('id')->on('menu_items')->onDelete('cascade');
            
            // Индексы для оптимизации
            $table->index('menu_id');
            $table->index('parent_id');
            $table->index('order');
            $table->index('is_active');
            $table->index(['menu_id', 'parent_id', 'order']);
        });
        
        Log::info('Создана таблица menu_items');

        // Добавление в БД
        DB::table('menu_items')->insert(
        [
            // Для меню с ID 1 (Верхнее оновное меню)
            [
                'id' => '1',
                'menu_id' => '1',
                'title' => 'Акции',
                'url' => '/sale/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '1',
                'is_active' => true,
                'seo_title' => 'Акции',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '2',
                'menu_id' => '1',
                'title' => 'Распродажа',
                'url' => '/catalog/rasprodazha/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '2',
                'is_active' => true,
                'seo_title' => 'Распродажа',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '3',
                'menu_id' => '1',
                'title' => 'Покупателям',
                'url' => '/info/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '3',
                'is_active' => true,
                'seo_title' => 'Покупателям',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '4',
                'menu_id' => '1',
                'title' => 'Оплата',
                'url' => '/info/payment/',
                'icon' => NULL,
                'parent_id' => 3,
                'order' => '4',
                'is_active' => true,
                'seo_title' => 'Оплата',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '5',
                'menu_id' => '1',
                'title' => 'Доставка',
                'url' => '/info/delivery/',
                'icon' => NULL,
                'parent_id' => 3,
                'order' => '5',
                'is_active' => true,
                'seo_title' => 'Доставка',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '6',
                'menu_id' => '1',
                'title' => 'Магазины',
                'url' => '/contacts/stores/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '6',
                'is_active' => true,
                'seo_title' => 'Магазины',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '7',
                'menu_id' => '1',
                'title' => 'Мастерская',
                'url' => '/services/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '7',
                'is_active' => true,
                'seo_title' => 'Мастерская',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '8',
                'menu_id' => '1',
                'title' => 'Новости',
                'url' => '/company/news/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '8',
                'is_active' => true,
                'seo_title' => 'Новости',
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Для меню с ID 2 (Нижнее меню Каталог)
            [
                'id' => '9',
                'menu_id' => '2',
                'title' => 'Горные велосипеды',
                'url' => '/catalog/gornye/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '1',
                'is_active' => true,
                'seo_title' => 'Горные велосипеды',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '10',
                'menu_id' => '2',
                'title' => 'Детские велосипеды',
                'url' => '/catalog/detskie/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '2',
                'is_active' => true,
                'seo_title' => 'Детские велосипеды',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '11',
                'menu_id' => '2',
                'title' => 'Городские велосипеды',
                'url' => '/catalog/detskie/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '3',
                'is_active' => true,
                'seo_title' => 'Городские велосипеды',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '12',
                'menu_id' => '2',
                'title' => 'Двухподвесные велосипеды',
                'url' => '/catalog/dvukhpodvesy/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '4',
                'is_active' => true,
                'seo_title' => 'Двухподвесные велосипеды',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '13',
                'menu_id' => '2',
                'title' => 'Женские велосипеды',
                'url' => '/catalog/zhenskie/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '5',
                'is_active' => true,
                'seo_title' => 'Женские велосипеды',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '14',
                'menu_id' => '2',
                'title' => 'Складные велосипеды',
                'url' => '/catalog/skladnye/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '6',
                'is_active' => true,
                'seo_title' => 'Складные велосипеды',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '15',
                'menu_id' => '2',
                'title' => 'Гоночные велосипеды',
                'url' => '/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '7',
                'is_active' => true,
                'seo_title' => 'Гоночные велосипеды',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '16',
                'menu_id' => '2',
                'title' => 'BMX велосипеды',
                'url' => '/catalog/bmx/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '8',
                'is_active' => true,
                'seo_title' => 'BMX велосипеды',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '17',
                'menu_id' => '2',
                'title' => 'Самокаты',
                'url' => '/catalog/samokaty/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '9',
                'is_active' => true,
                'seo_title' => 'Самокаты',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '18',
                'menu_id' => '2',
                'title' => 'Электровелосипеды',
                'url' => '/catalog/elektrotransport/elektrovelosipedy/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '10',
                'is_active' => true,
                'seo_title' => 'Электровелосипеды',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }

    /**
     * Удаление таблицы пунктов меню
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
        Log::info('Удалена таблица menu_items');
    }
};