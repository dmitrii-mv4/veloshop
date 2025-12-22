<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Регистрируем провайдер для динамической загрузки маршрутов
            app()->register(\App\Core\Providers\RouterProvider::class);

            // Регистрируем провайдер для команд
            app()->register(\App\Core\Providers\CommandsServiceProvider::class);

            // Регистрируем AdminProvider
            app()->register(\App\Admin\Providers\AdminProvider::class);

            // Регистрируем универсальный ViewsProvider
            app()->register(\App\Core\Providers\ViewsProvider::class);
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Глобальные middleware
        $middleware->web(append: [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Псевдонимы middleware
        $middleware->alias([
            'admin' => \App\Admin\Middleware\AdminPanelMiddleware::class,
            'auth' => \App\Core\Middleware\EnsureUserIsAuthenticated::class,
            
            // User module middleware
            'users_index' => \App\Modules\User\Middleware\UsersIndexMiddleware::class,
            'users_create' => \App\Modules\User\Middleware\UsersCreateMiddleware::class,
            'users_update' => \App\Modules\User\Middleware\UsersUpdateMiddleware::class,
            'users_delete' => \App\Modules\User\Middleware\UsersDeleteMiddleware::class,
            
            // Role module middleware
            'roles_index' => \App\Modules\Role\Middleware\RolesIndexMiddleware::class,
            'roles_create' => \App\Modules\Role\Middleware\RolesCreateMiddleware::class,
            'roles_update' => \App\Modules\Role\Middleware\RolesUpdateMiddleware::class,
            'roles_delete' => \App\Modules\Role\Middleware\RolesDeleteMiddleware::class,
            
            // Остальные стандартные middleware Laravel
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            'signed' => \App\Http\Middleware\ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();