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

            // Для меню с ID 3 (Нижнее меню Информация)
            [
                'id' => '19',
                'menu_id' => '3',
                'title' => 'Условия оплаты',
                'url' => '/info/payment/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '1',
                'is_active' => true,
                'seo_title' => 'Условия оплаты',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '20',
                'menu_id' => '3',
                'title' => 'Условия рассрочки',
                'url' => '/info/rassrochka/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '2',
                'is_active' => true,
                'seo_title' => 'Условия рассрочки',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '21',
                'menu_id' => '3',
                'title' => 'Условия доставки',
                'url' => '/info/delivery/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '3',
                'is_active' => true,
                'seo_title' => 'Условия доставки',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '22',
                'menu_id' => '3',
                'title' => 'Возврат / Обмен',
                'url' => '/info/return/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '4',
                'is_active' => true,
                'seo_title' => 'Возврат / Обмен',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '23',
                'menu_id' => '3',
                'title' => 'Гарантия на товар',
                'url' => '/info/warranty/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '5',
                'is_active' => true,
                'seo_title' => 'Гарантия на товар',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '24',
                'menu_id' => '3',
                'title' => 'Расширенная гарантия',
                'url' => '/info/warranty/extended_warranty/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '6',
                'is_active' => true,
                'seo_title' => 'Расширенная гарантия',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '25',
                'menu_id' => '3',
                'title' => 'Акции',
                'url' => '/sale/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '7',
                'is_active' => true,
                'seo_title' => 'Акции',
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Для меню с ID 4 (Нижнее меню Компания)
            [
                'id' => '26',
                'menu_id' => '4',
                'title' => 'О компании',
                'url' => '/company/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '1',
                'is_active' => true,
                'seo_title' => 'О компании',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '27',
                'menu_id' => '4',
                'title' => 'Реквизиты',
                'url' => '/company/rekvizity/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '2',
                'is_active' => true,
                'seo_title' => 'Реквизиты',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '28',
                'menu_id' => '4',
                'title' => 'Оферта',
                'url' => '/company/oferta/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '3',
                'is_active' => true,
                'seo_title' => 'Оферта',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '29',
                'menu_id' => '4',
                'title' => 'Новости',
                'url' => '/company/news/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '4',
                'is_active' => true,
                'seo_title' => 'Новости',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '30',
                'menu_id' => '4',
                'title' => 'Вакансии',
                'url' => '/company/jobs/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '5',
                'is_active' => true,
                'seo_title' => 'Вакансии',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '31',
                'menu_id' => '4',
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
                'id' => '32',
                'menu_id' => '4',
                'title' => 'Соглашение',
                'url' => '/include/licenses_detail/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '7',
                'is_active' => true,
                'seo_title' => 'Соглашение',
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Для меню с ID 5 (Нижнее меню Помощь)
            [
                'id' => '33',
                'menu_id' => '5',
                'title' => 'Статьи',
                'url' => '/help/articles/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '1',
                'is_active' => true,
                'seo_title' => 'Статьи',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '34',
                'menu_id' => '5',
                'title' => 'Вопрос-ответ',
                'url' => '/help/faq/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '2',
                'is_active' => true,
                'seo_title' => 'Вопрос-ответ',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '35',
                'menu_id' => '5',
                'title' => 'Производители',
                'url' => '/brands/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '3',
                'is_active' => true,
                'seo_title' => 'Производители',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '36',
                'menu_id' => '5',
                'title' => 'Видео обзоры',
                'url' => '/help/video/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '4',
                'is_active' => true,
                'seo_title' => 'Видео обзоры',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => '37',
                'menu_id' => '5',
                'title' => 'Карта сайта',
                'url' => '/sitemap/',
                'icon' => NULL,
                'parent_id' => NULL,
                'order' => '5',
                'is_active' => true,
                'seo_title' => 'Карта сайта',
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