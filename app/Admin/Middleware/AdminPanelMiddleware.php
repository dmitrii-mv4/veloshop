<?php

namespace App\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminPanelMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Проверяем авторизацию
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors([
                'auth' => 'Требуется авторизация'
            ]);
        }

        // 2. Проверяем права администратора
        $user = Auth::user();
        if (!$user->role('admin') && !$user->hasPermission('show_admin')) {
            abort(403, 'Доступ запрещен');
        }

        // 3. Проверяем активность пользователя
        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'auth' => 'Ваш аккаунт деактивирован'
            ]);
        }

        // 4. Устанавливаем локаль для админки
        app()->setLocale(config('app.admin_locale', 'ru'));

        return $next($request);
    }
}