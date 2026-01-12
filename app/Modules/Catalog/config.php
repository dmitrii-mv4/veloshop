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
            'path' => 'app/Modules/Catalog/routes/web.php',
            'prefix' => '',
            'middleware' => ['web', 'admin']
        ],
        'api' => [
            'path' => 'app/Modules/Catalog/routes/api.php',
            'prefix' => 'api/catalog',
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
                'section' => 'content',
                'title' => 'Каталог',
                'icon' => 'bi bi-collection nav-icon',
                'route' => null,
                'order' => 6,
                'permission' => ''
            ],
            [
                'section' => 'content',
                'title' => 'Товары',
                'icon' => 'bi bi-collection nav-icon',
                'route' => 'catalog.index',
                'order' => 6,
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