<?php

namespace App\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Admin\Services\LanguageService;

class SetAdminLocale
{
    /**
     * Handle an incoming request
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Применяем только для админских маршрутов
        if ($request->is('admin/*') || $request->routeIs('admin.*')) {
            $languageService = app(LanguageService::class);
            $locale = $languageService->getCurrentLocale();
            
            // Устанавливаем язык приложения
            app()->setLocale($locale);
            
            // Передаем переменные в шаблоны
            view()->share('currentLocale', $locale);
            view()->share('availableLocales', $languageService->getAvailableLanguages());
        }
        
        return $next($request);
    }
}