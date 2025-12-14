<?php

namespace App\Modules\Role\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Modules\User\Models\User;
use App\Modules\Role\Models\Role;

class RolesDeleteMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем разрешение через Gate
        if (Gate::allows('delete', Role::class)) {
            return $next($request);
        }
        
        // Если доступ запрещен
        abort(403, 'Доступ запрещен');
    }
}
