<?php

namespace App\Admin\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use App\Modules\User\Models\User;

/**
 * Сервис управления языком интерфейса административной панели
 * Обеспечивает переключение, кэширование и управление языковыми настройками
 */
class LanguageService
{
    /**
     * Ключ для кэша языка текущего пользователя
     * @var string
     */
    protected const CACHE_KEY_PREFIX = 'admin_lang_';
    
    /**
     * Ключ для кэша доступных языков
     * @var string
     */
    protected const LANGUAGES_CACHE_KEY = 'available_languages';
    
    /**
     * Имя куки для хранения языка
     * @var string
     */
    protected const COOKIE_NAME = 'admin_lang';
    
    /**
     * Время жизни кэша языков (в секундах)
     * @var int
     */
    protected const CACHE_TTL = 3600; // 1 час
    
    /**
     * Время жизни куки языка (в днях)
     * @var int
     */
    protected const COOKIE_LIFETIME = 30;
    
    /**
     * Массив поддерживаемых языков с метаданными
     * @var array
     */
    protected $availableLanguages = [];
    
    /**
     * Инициализация сервиса
     */
    public function __construct()
    {
        $this->loadAvailableLanguages();
    }
    
    /**
     * Загрузка доступных языков из конфигурации переводов
     * @return void
     */
    protected function loadAvailableLanguages(): void
    {
        // Проверяем кэш
        $cached = Cache::get(self::LANGUAGES_CACHE_KEY);
        
        if ($cached) {
            $this->availableLanguages = $cached;
            return;
        }
        
        // Определяем доступные языки
        $languages = [
            'ru' => [
                'code' => 'ru',
                'name' => trans('app.lang.russian', [], 'ru'),
                'native_name' => 'Русский',
                'flag' => '🇷🇺',
                'direction' => 'ltr',
                'enabled' => true,
            ],
            'en' => [
                'code' => 'en',
                'name' => trans('app.lang.english', [], 'en'),
                'native_name' => 'English',
                'flag' => '🇬🇧',
                'direction' => 'ltr',
                'enabled' => true,
            ],
        ];
        
        // Сохраняем в кэш
        Cache::put(self::LANGUAGES_CACHE_KEY, $languages, self::CACHE_TTL);
        $this->availableLanguages = $languages;
    }
    
    /**
     * Получить все доступные языки
     * @return array
     */
    public function getAvailableLanguages(): array
    {
        return $this->availableLanguages;
    }
    
    /**
     * Получить код текущего языка
     * Приоритет: 1) Пользователь в БД, 2) Куки, 3) Язык браузера, 4) По умолчанию (ru)
     * @return string
     */
    public function getCurrentLocale(): string
    {
        $userId = Auth::id();
        $cacheKey = self::CACHE_KEY_PREFIX . $userId;
        
        // Проверяем кэш
        $cachedLocale = Cache::get($cacheKey);
        if ($cachedLocale) {
            return $cachedLocale;
        }
        
        $locale = $this->determineLocale();
        
        // Сохраняем в кэш
        Cache::put($cacheKey, $locale, self::CACHE_TTL);
        
        return $locale;
    }
    
    /**
     * Определение языка по приоритету
     * @return string
     */
    protected function determineLocale(): string
    {
        $user = Auth::user();
        
        // 1. Язык из профиля пользователя
        if ($user && $user->is_lang) {
            if (isset($this->availableLanguages[$user->is_lang])) {
                return $user->is_lang;
            }
        }
        
        // 2. Язык из куки
        $cookieLocale = request()->cookie(self::COOKIE_NAME);
        if ($cookieLocale && isset($this->availableLanguages[$cookieLocale])) {
            return $cookieLocale;
        }
        
        // 3. Язык браузера
        $browserLocale = substr(request()->server('HTTP_ACCEPT_LANGUAGE', ''), 0, 2);
        if ($browserLocale && isset($this->availableLanguages[$browserLocale])) {
            return $browserLocale;
        }
        
        // 4. Язык по умолчанию
        return 'ru';
    }
    
    /**
     * Установить язык для текущего пользователя
     * @param string $locale Код языка
     * @return bool
     */
    public function setLocale(string $locale): bool
    {
        if (!isset($this->availableLanguages[$locale])) {
            return false;
        }
        
        $user = Auth::user();
        
        // Для аутентифицированных пользователей сохраняем в БД
        if ($user) {
            $user->is_lang = $locale;
            $user->save();
            
            // Очищаем кэш пользователя
            $this->clearUserCache($user->id);
        }
        
        // Устанавливаем куку
        $this->setLanguageCookie($locale);
        
        // Устанавливаем язык приложения для текущего запроса
        app()->setLocale($locale);
        
        return true;
    }
    
    /**
     * Установить куку языка
     * @param string $locale Код языка
     * @return void
     */
    protected function setLanguageCookie(string $locale): void
    {
        Cookie::queue(
            self::COOKIE_NAME,
            $locale,
            self::COOKIE_LIFETIME * 24 * 60 // В минутах
        );
    }
    
    /**
     * Очистить кэш языка для пользователя
     * @param int|null $userId ID пользователя (null - текущий)
     * @return bool
     */
    public function clearUserCache(?int $userId = null): bool
    {
        if (!$userId) {
            $userId = Auth::id();
        }
        
        if ($userId) {
            Cache::forget(self::CACHE_KEY_PREFIX . $userId);
            return true;
        }
        
        return false;
    }
    
    /**
     * Очистить куку языка
     * @return void
     */
    public function clearLanguageCookie(): void
    {
        Cookie::queue(Cookie::forget(self::COOKIE_NAME));
    }
    
    /**
     * Очистить кэш доступных языков
     * @return bool
     */
    public function clearLanguagesCache(): bool
    {
        Cache::forget(self::LANGUAGES_CACHE_KEY);
        $this->loadAvailableLanguages(); // Перезагружаем языки
        return true;
    }
    
    /**
     * Полная очистка всех данных языка (кэш + куки)
     * @param int|null $userId ID пользователя
     * @return bool
     */
    public function clearAllLanguageData(?int $userId = null): bool
    {
        $this->clearUserCache($userId);
        $this->clearLanguageCookie();
        return true;
    }
    
    /**
     * Получить информацию о текущем языке
     * @return array
     */
    public function getCurrentLanguageInfo(): array
    {
        $locale = $this->getCurrentLocale();
        return $this->availableLanguages[$locale] ?? $this->availableLanguages['ru'];
    }
    
    /**
     * Получить перевод для текущего языка
     * @param string $key Ключ перевода
     * @param array $parameters Параметры
     * @return string
     */
    public function trans(string $key, array $parameters = []): string
    {
        return trans($key, $parameters, $this->getCurrentLocale());
    }
    
    /**
     * Проверить, является ли язык текущим
     * @param string $locale Код языка
     * @return bool
     */
    public function isCurrent(string $locale): bool
    {
        return $this->getCurrentLocale() === $locale;
    }
}