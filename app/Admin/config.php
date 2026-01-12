<?php

/**
 * Конфигурация модуля Admin
 * 
 * Модуль для интерфейса админ панели.
 */

return [
    /**
     * Базовые настройки модуля
     */
    'module' => [
        // Название модуля (обязательно)
        'name' => 'Exchange1C',
        
        // Человеко-читаемое название модуля (обязательно)
        'title' => 'Модуль для интерфейса админ панели',
        
        // Описание модуля (обязательно)
        'description' => 'Модуль для интерфейса админ панели',
        
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
            'path' => 'app/Admin/routes/web.php',
            'prefix' => '',
            'middleware' => ['web', 'admin']
        ],
        'api' => [
            'path' => 'app/Admin/routes/api.php',
            'prefix' => 'api/admin',
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
            'title' => 'Админ панель',
            'icon' => 'bi bi-people nav-icon',
            'route' => 'admin.dashboard',
            'order' => 1,
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