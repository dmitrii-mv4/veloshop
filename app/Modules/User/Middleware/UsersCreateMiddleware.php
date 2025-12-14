<?php

namespace App\Modules\User\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Modules\User\Models\User;
use App\Modules\User\Models\Role;

class UsersCreateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем разрешение через Gate
        if (Gate::allows('create', User::class)) {
            return $next($request);
        }
        
        // Если доступ запрещен
        abort(403, 'Доступ запрещен');
    }
}
