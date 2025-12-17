<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // 1) Подключаем стандартный routes/api.php если он существует
            $mainApiFile = base_path('routes/api.php');
            if (File::exists($mainApiFile)) {
                Route::middleware('api')->prefix('api')->group($mainApiFile);
            }

            // 2) Подключаем одиночный файл routes/api/app.php если он есть
            $singleApiFile = base_path('routes/api/app.php');
            if (File::exists($singleApiFile)) {
                Route::middleware('api')->prefix('api')->group($singleApiFile);
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $baseAliases = [
            'admin' => \App\Admin\Middleware\AdminPanelMiddleware::class,
            'locale' => \App\Admin\Middleware\SetAdminLocale::class,

            'users_index' => \App\Modules\User\Middleware\UsersIndexMiddleware::class,
            'users_create' => \App\Modules\User\Middleware\UsersCreateMiddleware::class,
            'users_update' => \App\Modules\User\Middleware\UsersUpdateMiddleware::class,
            'users_delete' => \App\Modules\User\Middleware\UsersDeleteMiddleware::class,

            'roles_index' => \App\Modules\Role\Middleware\RolesIndexMiddleware::class,
            'roles_create' => \App\Modules\Role\Middleware\RolesCreateMiddleware::class,
            'roles_update' => \App\Modules\Role\Middleware\RolesUpdateMiddleware::class,
            'roles_delete' => \App\Modules\Role\Middleware\RolesDeleteMiddleware::class,
        ];

        $middleware->alias($baseAliases);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
    