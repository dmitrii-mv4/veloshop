<?php

/**
 * Конфигурация модуля IBlock
 * 
 * Модуль для управления информационными блоками на сайте.
 */

return [
    /**
     * Базовые настройки модуля
     */
    'module' => [
        // Название модуля (обязательно)
        'name' => 'IBlock',
        
        // Человеко-читаемое название модуля (обязательно)
        'title' => 'Информационные блоки',
        
        // Описание модуля (обязательно)
        'description' => 'Модуль для управления информационными блоками на сайте',
        
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
            'Core',
            'User'
        ],
    ],
    
    /**
     * Настройки маршрутизации
     */
    'routes' => [
        'web' => [
            'path' => 'app/Modules/IBlock/routes/web.php',
            'prefix' => '',
            'middleware' => ['web', 'admin']
        ],
        'api' => [
            'path' => 'app/Modules/IBlock/routes/api.php',
            'prefix' => 'api/pages',
            'middleware' => ['api']
        ]
    ],
    
    /**
     * Настройки административной панели
     */
    'admin' => [
        // Раздел в меню админки
        'menu' => [
            'section' => 'content',
            'title' => 'Инфоблоки',
            'icon' => 'bi-input-cursor-text nav-icon',
            'route' => 'admin.iblock.index',
            'order' => 2,
            'permission' => ''
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