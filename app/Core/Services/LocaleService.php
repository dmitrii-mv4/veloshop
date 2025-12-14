<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

/**
 * Класс для смены языка админ панели
 * 
 */

class LocaleService
{
    /**
     * Устанавливает локаль и сохраняет в сессии
     */
    public function setLocale(string $locale): void
    {
        // Устанавливаем локаль для текущего приложения
        App::setLocale($locale);
        
        // Устанавливаем фолбэк-локаль
        App::setFallbackLocale($locale);
        
        // Сохраняем в сессии
        Session::put('admin_locale', $locale);
        
        // Можно также сохранить в куки для долгосрочного хранения
        cookie()->queue('admin_locale', $locale, 60 * 24 * 30); // 30 дней
    }
    
    /**
     * Получает текущую локаль из сессии или возвращает дефолтную
     */
    public function getLocale(): string
    {
        return Session::get('admin_locale', config('app.locale'));
    }
    
    /**
     * Проверяет, установлена ли локаль в сессии
     */
    public function hasLocale(): bool
    {
        return Session::has('admin_locale');
    }
    
    /**
     * Сбрасывает локаль в сессии
     */
    public function forgetLocale(): void
    {
        Session::forget('admin_locale');
        cookie()->queue(cookie()->forget('admin_locale'));
    }
}