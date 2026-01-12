<?php

/**
 * Конфигурация модуля MediaLib
 * 
 * Модуль управления файлами.
 */

return [
    /**
     * Базовые настройки модуля
     */
    'module' => [
        // Название модуля (обязательно)
        'name' => 'MediaLib',
        
        // Человеко-читаемое название модуля (обязательно)
        'title' => 'Управление файлами',
        
        // Описание модуля (обязательно)
        'description' => 'Модуль для управления файлами',
        
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
            'path' => 'app/Modules/MediaLib/routes/web.php',
            'prefix' => '',
            'middleware' => ['web', 'admin']
        ],
        'api' => [
            'path' => 'app/Modules/MediaLib/routes/api.php',
            'prefix' => 'api/medialib',
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
            'title' => admin_trans('app.media_library.name'),
            'icon' => 'bi-images nav-icon',
            'route' => 'admin.medialib.index',
            'order' => 5,
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