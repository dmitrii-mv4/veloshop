<?php

/**
 * Конфигурация модуля Catalog
 * 
 * Модуль управления товарами и складами.
 */

return [
    /**
     * Базовые настройки модуля
     */
    'module' => [
        // Название модуля (обязательно)
        'name' => 'Catalog',
        
        // Человеко-читаемое название модуля (обязательно)
        'title' => 'Каталог товаров',
        
        // Описание модуля (обязательно)
        'description' => 'Модуль управления товарами и складами',
        
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
            'Core',
            'User'
        ],
    ],
    
    /**
     * Настройки маршрутизации
     */
    'routes' => [
        'admin' => [
            'path' => 'app/Modules/Catalog/routes/admin.php',
            'prefix' => '',
            'middleware' => ['web', 'admin']
        ],
        'api' => [
            'path' => 'app/Modules/Catalog/routes/api.php',
            'prefix' => 'catalog',
            'middleware' => ['api']
        ]
    ],
    
    /**
     * Настройки административной панели
     */
    'admin' => [
        // Раздел в меню админки
        'menu' => [
            [
                'section' => 'module',
                'title' => 'Каталог',
                'icon' => 'bi bi-collection nav-icon',
                'route' => 'catalog.index',
                'order' => 1,
                'permission' => ''
            ],
            [
                'section' => 'module',
                'title' => 'Склады',
                'icon' => 'bi bi-boxes nav-icon',
                'route' => 'catalog.warehouses.index',
                'order' => 2,
                'permission' => ''
            ],
            [
                'section' => 'module',
                'title' => 'Покупатели',
                'icon' => 'bi bi-people nav-icon',
                'route' => 'catalog.customers.index',
                'order' => 3,
                'permission' => ''
            ],
            [
                'section' => 'module',
                'title' => 'Корзины',
                'icon' => 'bi bi-cart nav-icon',
                'route' => 'catalog.basket.index',
                'order' => 4,
                'permission' => ''
            ],
            [
                'section' => 'module',
                'title' => 'Обмен с 1С',
                'icon' => 'bi bi-arrow-left-right nav-icon',
                'route' => 'exchange1c.index',
                'order' => 5,
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
    ]
];