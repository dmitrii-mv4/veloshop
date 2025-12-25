<?php

use App\Admin\Services\LanguageService;

if (!function_exists('admin_lang')) {
    /**
     * Хелпер для доступа к LanguageService
     * @return LanguageService
     */
    function admin_lang(): LanguageService
    {
        return app(LanguageService::class);
    }
}

if (!function_exists('admin_trans')) {
    /**
     * Хелпер для переводов в админке
     * @param string $key Ключ перевода
     * @param array $parameters Параметры
     * @return string
     */
    function admin_trans(string $key, array $parameters = []): string
    {
        return admin_lang()->trans($key, $parameters);
    }
}