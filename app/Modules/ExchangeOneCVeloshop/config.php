<?php

/**
 * Конфигурация модуля Обмен с 1С Велошоп
 *
 * Модуль управления обменом с 1С.
 */

return [
    /**
     * Базовые настройки модуля
     */
    'module' => [
        // Название модуля (обязательно)
        'name' => 'ExchangeOneCVeloshop',

        // Человеко-читаемое название модуля (обязательно)
        'title' => 'Обмен с 1С',

        // Описание модуля (обязательно)
        'description' => 'Модуль управления обменом с 1С',

        // Версия модуля (обязательно)
        'version' => '1.0.0',

        // Автор модуля
        'author' => 'Kotiks CMS Team',

        // Активность модуля (обязательно)
        'enabled' => true,

        // Порядок загрузки модуля (меньше - раньше)
        'priority' => 100,

        // Системный модуль (нельзя деактивировать через админку)
        'system' => false,

        // Зависимости от других модулей
        'dependencies' => [
            'Core'
        ],
    ],

    /**
     * Настройки маршрутизации
     */
    'routes' => [
        'admin' => [
            'path' => 'app/Modules/ExchangeOneCVeloshop/routes/admin.php',
            'prefix' => '',
            'middleware' => ['web', 'admin']
        ],
        /* TODO: Не существующие пока роуты
         * 'api' => [
            'path' => 'app/Modules/ExchangeOneCVeloshop/routes/api.php',
            'prefix' => 'api/exchange1c',
            'middleware' => ['api']
        ]*/
    ],

    /**
     * Настройки административной панели
     */
    'admin' => [
        // Раздел в меню админки
        'menu' => [
            'section' => NULL,
            'title' => 'Обмен с 1С',
            'icon' => 'bi bi-arrow-left-right nav-icon',
            'route' => 'exchange1c.index',
            'order' => 6,
            'permission' => ''
            // [
            //     'section' => 'content',
            //     'title' => 'Обмен с 1С',
            //     'icon' => 'bi bi-arrow-left-right nav-icon',
            //     'location' => 'settings',
            //     'route' => 'exchange1c.index',
            //     'order' => 6,
            //     'permission' => ''
            // ],
            // [
            //     'section' => 'content',
            //     'title' => 'Товары из 1С',
            //     'icon' => 'bi bi-box-seam nav-icon',
            //     'location' => 'settings',
            //     'route' => 'exchange1c.exchange.products.view',
            //     'order' => 7,
            //     'permission' => ''
            // ],
        ],
    ],

    /**
     * Настройки системы
     */
    'system' => [
        // Минимальные требования
        'requirements' => [
            'php' => '8.2.0',
            'laravel' => '10.0.0'
        ],
    ],

];
