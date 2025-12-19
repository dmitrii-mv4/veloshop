<?php

namespace App\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Core\Services\DynamicModulesService;

class LoadDynamicModules
{
    public function handle(Request $request, Closure $next)
    {
        // Загружаем динамические модули только если таблица существует
        if (DynamicModulesService::canLoadModules()) {
            DynamicModulesService::loadAll();
        }
        
        return $next($request);
    }
}