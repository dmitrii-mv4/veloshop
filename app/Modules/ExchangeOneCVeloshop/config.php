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
        'title' => 'Обмне с 1С',
        
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
        'api' => [
            'path' => 'app/Modules/ExchangeOneCVeloshop/routes/api.php',
            'prefix' => 'api/exchange1c',
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