<?php

namespace App\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Modules\User\Models\User;

class AdminPanelMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Проверка роли администратора (ID 1)
        // if ($user->role_id == 1) {
        //     return $next($request);
        // }

        // Проверка разрешения show_admin
        $hasPermission = $user->permissions()
            ->where('name', 'show_admin')
            ->exists();

        if ($hasPermission == true) {
            return $next($request);
        }

        // Все остальные случаи - доступ запрещен
        return redirect('/login');
    }
}
