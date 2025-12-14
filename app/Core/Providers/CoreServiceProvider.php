<?php

namespace App\Core\Providers;

use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Регистрация сервисов ядра
     */
    public function register(): void
    {
        // Регистрируем другие сервисы ядра здесь
    }

    /**
     * Загрузка сервисов ядра
     */
    public function boot(): void
    {
        // Инициализация ядра системы
        $this->initializeCore();
    }

    /**
     * Инициализация ядра системы
     */
    protected function initializeCore(): void
    {
        // Настройка глобальных констант, проверка системы и т.д.
        if (!defined('KOTIKS_VERSION')) {
            define('KOTIKS_VERSION', '1.0.0');
            define('KOTIKS_PATH', base_path());
        }
    }
}