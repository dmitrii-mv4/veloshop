<?php

/**
 * Конфигурация модуля User
 * 
 * Модуль управления пользоватей сайта. Предоставляет функционал
 * создания, редактирования пользователей.
 */

return [
    /**
     * Базовые настройки модуля
     */
    'module' => [
        // Название модуля (обязательно)
        'name' => 'User',
        
        // Человеко-читаемое название модуля (обязательно)
        'title' => 'Управление пользователями',
        
        // Описание модуля (обязательно)
        'description' => 'Модуль для управления пользователей',
        
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
            'Role'
        ],
    ],
    
    /**
     * Настройки маршрутизации
     */
    'routes' => [
        'web' => [
            [
                'path' => 'app/Modules/User/routes/web.php',
                'prefix' => '',
                'middleware' => ['web', 'admin']
            ],
            [
                'path' => 'app/Modules/User/routes/auth.php',
                'prefix' => '',
                'middleware' => ['web']
            ]
        ],
        'api' => [
            'path' => 'app/Modules/User/routes/api.php',
            'prefix' => 'api/users',
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
            'title' => admin_trans('app.user.users'),
            'icon' => 'bi bi-people nav-icon',
            'route' => 'admin.users',
            'order' => 3,
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