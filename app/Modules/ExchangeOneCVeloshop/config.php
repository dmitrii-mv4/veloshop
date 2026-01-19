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

        // Зависимости от других модулей
        'dependencies' => [
            'Core'
        ],
    ],

    /**
     * Настройки маршрутизации
     */
    'routes' => [
        'web' => [
            'path' => 'app/Modules/ExchangeOneCVeloshop/routes/web.php',
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
            [
                'section' => 'content',
                'title' => 'Обмен с 1С',
                'icon' => 'bi bi-arrow-left-right nav-icon',
                'route' => 'exchange1c.index',
                'order' => 6,
                'permission' => ''
            ],
            [
                'section' => 'content',
                'title' => 'Товары из 1С',
                'icon' => 'bi bi-box-seam nav-icon',
                'route' => 'exchange1c.exchange.products.view',
                'order' => 7,
                'permission' => ''
            ],
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

    /**
     * Настройки обмена с 1С
     */
    'exchange' => [
        // URL API 1С по умолчанию
        'default_api_url' => 'http://176.62.189.27:62754/im/4371601201/?type=json&deep=2',

        // Таймаут запроса по умолчанию (секунды)
        'default_timeout' => 120,

        // Максимальный таймаут (секунды)
        'max_timeout' => 300,

        // Лимит товаров по умолчанию
        'default_limit' => 3,

        // Максимальный лимит товаров
        'max_limit' => 100,

        // Таймаут проверки соединения по умолчанию (секунды)
        'default_connection_timeout' => 5,

        // Максимальный таймаут проверки соединения (секунды)
        'max_connection_timeout' => 60,
    ]
];
