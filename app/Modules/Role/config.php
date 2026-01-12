<?php

/**
 * Конфигурация модуля Role
 * 
 * Модуль управления ролями пользоватей сайта. Предоставляет функционал
 * создания, редактирования ролей.
 */

return [
    /**
     * Базовые настройки модуля
     */
    'module' => [
        // Название модуля (обязательно)
        'name' => 'Role',
        
        // Человеко-читаемое название модуля (обязательно)
        'title' => 'Управление ролями пользователями',
        
        // Описание модуля (обязательно)
        'description' => 'Модуль для управления ролями пользователей',
        
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
            'path' => 'app/Modules/Role/routes/web.php',
            'prefix' => '',
            'middleware' => ['web', 'admin']
        ],
        'api' => [
            'path' => 'app/Modules/Role/routes/api.php',
            'prefix' => 'api/roles',
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
            'title' => admin_trans('app.role.roles'),
            'icon' => 'bi-shield-check nav-icon',
            'route' => 'admin.roles',
            'order' => 4,
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