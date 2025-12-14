<?php

namespace App\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Core\Services\LocaleService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Контроллер для переключения языка админки
 * 
 */

class SetAdminLocale
{
    protected $localeService;
    
    public function __construct(LocaleService $localeService)
    {
        $this->localeService = $localeService;
    }
    
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Проверяем принудительную смену языка через GET-параметр
        if ($request->has('lang')) {
            $locale = $request->get('lang');
            if (in_array($locale, ['ru', 'en'])) { // Список поддерживаемых языков
                $this->localeService->setLocale($locale);
            }
        }
        
        // 2. Проверяем язык в сессии
        elseif ($this->localeService->hasLocale()) {
            App::setLocale($this->localeService->getLocale());
            App::setFallbackLocale($this->localeService->getLocale());
        }
        
        // 3. Проверяем язык в заголовках браузера (опционально)
        elseif ($request->header('Accept-Language')) {
            $browserLocale = substr($request->header('Accept-Language'), 0, 2);
            if (in_array($browserLocale, ['ru', 'en'])) {
                App::setLocale($browserLocale);
            }
        }
        
        return $next($request);
    }
}