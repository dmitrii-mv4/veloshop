<?php

namespace App\Modules\ExchangeOneCVeloshop\Services\Traits;

use Illuminate\Support\Facades\Log;

/**
 * Трейт для работы с URL
 * 
 * Предоставляет общие методы для валидации и маскировки URL
 * Используется в сервисах, работающих с внешними API
 */
trait UrlHelperTrait
{
    /**
     * Валидирует URL
     * 
     * Проверяет корректность формата URL и поддерживаемые протоколы
     * 
     * @param string $url URL для валидации
     * @param bool $logErrors Логировать ли ошибки валидации (по умолчанию false)
     * @param string|null $context Контекст для логирования (имя класса/сервиса)
     * @return bool True если URL валиден, иначе false
     */
    protected function validateUrl(string $url, bool $logErrors = false, ?string $context = null): bool
    {
        if (empty($url)) {
            if ($logErrors && $context) {
                Log::warning("{$context}: URL не может быть пустым");
            }
            return false;
        }

        // Проверка формата URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            if ($logErrors && $context) {
                Log::warning("{$context}: Некорректный формат URL", ['url' => $url]);
            }
            return false;
        }

        // Проверка на разрешенные протоколы
        $parsedUrl = parse_url($url);
        $allowedProtocols = ['http', 'https'];

        if (!isset($parsedUrl['scheme']) || !in_array($parsedUrl['scheme'], $allowedProtocols)) {
            if ($logErrors && $context) {
                Log::warning("{$context}: Неподдерживаемый протокол", [
                    'url' => $url,
                    'protocol' => $parsedUrl['scheme'] ?? 'none',
                    'allowed_protocols' => $allowedProtocols
                ]);
            }
            return false;
        }

        return true;
    }

    /**
     * Маскирует URL для безопасного логирования
     * 
     * Скрывает параметры запроса, оставляя только базовый URL
     * для защиты чувствительной информации в логах
     * 
     * @param string $url Исходный URL
     * @return string Маскированный URL
     */
    protected function maskUrl(string $url): string
    {
        $parsedUrl = parse_url($url);
        
        if (!isset($parsedUrl['host'])) {
            return '[INVALID URL]';
        }

        // Используем null coalescing для безопасной обработки отсутствующего scheme
        $maskedUrl = ($parsedUrl['scheme'] ?? 'http') . '://' . $parsedUrl['host'];
        
        if (isset($parsedUrl['port'])) {
            $maskedUrl .= ':' . $parsedUrl['port'];
        }
        
        if (isset($parsedUrl['path'])) {
            $maskedUrl .= $parsedUrl['path'];
        }
        
        // Не показываем параметры запроса
        if (isset($parsedUrl['query'])) {
            $maskedUrl .= '?[PARAMS_HIDDEN]';
        }
        
        return $maskedUrl;
    }
}
