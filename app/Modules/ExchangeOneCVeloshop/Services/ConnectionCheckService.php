<?php

namespace App\Modules\ExchangeOneCVeloshop\Services;

use Illuminate\Support\Facades\Log;

/**
 * Сервис проверки соединения с 1С сервером
 * 
 * Выполняет проверку доступности URL-адресов 1С сервера
 * с использованием cURL. Поддерживает настройку таймаута
 * и обработку различных ошибок соединения.
 * 
 * Основной функционал:
 * - Проверка доступности сервера 1С по URL
 * - Настройка таймаута соединения
 * - Детальное логирование процесса проверки
 * - Обработка ошибок сети и сервера
 */
class ConnectionCheckService
{
    /**
     * Константа по умолчанию для таймаута соединения (секунды)
     * 
     * @var int
     */
    const DEFAULT_TIMEOUT = 5;

    /**
     * Последний код ошибки cURL
     * 
     * @var int|null
     */
    protected ?int $lastCurlError = null;

    /**
     * Последнее сообщение об ошибке cURL
     * 
     * @var string|null
     */
    protected ?string $lastCurlErrorMessage = null;

    /**
     * Последний HTTP статус код
     * 
     * @var int|null
     */
    protected ?int $lastHttpStatusCode = null;

    /**
     * Последнее время выполнения запроса (миллисекунды)
     * 
     * @var float|null
     */
    protected ?float $lastRequestTime = null;

    /**
     * Проверяет соединение с сервером 1С
     * 
     * Выполняет HTTP-запрос к указанному URL для проверки
     * доступности сервера. Возвращает true, если сервер
     * отвечает без ошибок соединения.
     * 
     * @param string $url URL сервера 1С для проверки
     * @param int $timeout Таймаут соединения в секундах
     * @return bool True если соединение успешно, иначе false
     */
    public function check(string $url, int $timeout = self::DEFAULT_TIMEOUT): bool
    {
        Log::info('ConnectionCheckService: Начало проверки соединения с 1С', [
            'url' => $this->maskUrl($url), // Маскируем URL для безопасности в логах
            'timeout' => $timeout
        ]);

        // Валидация URL
        if (!$this->validateUrl($url)) {
            Log::error('ConnectionCheckService: Некорректный URL', ['url' => $url]);
            return false;
        }

        // Валидация таймаута
        if ($timeout <= 0 || $timeout > 60) {
            Log::warning('ConnectionCheckService: Некорректный таймаут, установлено значение по умолчанию', [
                'provided_timeout' => $timeout,
                'default_timeout' => self::DEFAULT_TIMEOUT
            ]);
            $timeout = self::DEFAULT_TIMEOUT;
        }

        $startTime = microtime(true);
        $connected = false;

        try {
            $connected = $this->performCurlCheck($url, $timeout);
            $this->lastRequestTime = (microtime(true) - $startTime) * 1000; // В миллисекундах

            Log::info('ConnectionCheckService: Проверка соединения завершена', [
                'url' => $this->maskUrl($url),
                'connected' => $connected,
                'curl_error' => $this->lastCurlError,
                'curl_error_message' => $this->lastCurlErrorMessage,
                'http_status' => $this->lastHttpStatusCode,
                'request_time_ms' => round($this->lastRequestTime, 2)
            ]);

        } catch (\Exception $e) {
            Log::error('ConnectionCheckService: Исключение при проверке соединения', [
                'url' => $this->maskUrl($url),
                'message' => $e->getMessage(),
                'exception' => get_class($e)
            ]);
        }

        return $connected;
    }

    /**
     * Выполняет проверку соединения с использованием cURL
     * 
     * @param string $url URL для проверки
     * @param int $timeout Таймаут соединения
     * @return bool Результат проверки соединения
     */
    protected function performCurlCheck(string $url, int $timeout): bool
    {
        $ch = curl_init($url);

        // Настройка параметров cURL
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,  // Возвращать результат, а не выводить
            CURLOPT_TIMEOUT => $timeout,     // Таймаут соединения
            CURLOPT_CONNECTTIMEOUT => $timeout, // Таймаут подключения
            CURLOPT_SSL_VERIFYPEER => false, // Отключение проверки SSL (для dev)
            CURLOPT_SSL_VERIFYHOST => false, // Отключение проверки хоста SSL
            CURLOPT_FOLLOWLOCATION => true,  // Следовать редиректам
            CURLOPT_MAXREDIRS => 5,          // Максимальное количество редиректов
            CURLOPT_USERAGENT => 'Kotiks CMS 1C Exchange/1.0', // User-Agent
            CURLOPT_NOBODY => true,          // Используем HEAD запрос (не загружаем тело)
            CURLOPT_HEADER => true,          // Получать заголовки ответа
        ]);

        // Выполнение запроса
        $response = curl_exec($ch);

        // Получение информации о запросе
        $this->lastCurlError = curl_errno($ch);
        $this->lastCurlErrorMessage = curl_error($ch);
        $this->lastHttpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Закрытие соединения
        curl_close($ch);

        // Проверка на успешность соединения
        return $this->lastCurlError === 0;
    }

    /**
     * Валидирует URL перед проверкой соединения
     * 
     * @param string $url URL для валидации
     * @return bool True если URL валиден, иначе false
     */
    protected function validateUrl(string $url): bool
    {
        if (empty($url)) {
            Log::warning('ConnectionCheckService: URL не может быть пустым');
            return false;
        }

        // Проверка формата URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            Log::warning('ConnectionCheckService: Некорректный формат URL', ['url' => $url]);
            return false;
        }

        // Проверка на разрешенные протоколы
        $parsedUrl = parse_url($url);
        $allowedProtocols = ['http', 'https'];

        if (!isset($parsedUrl['scheme']) || !in_array($parsedUrl['scheme'], $allowedProtocols)) {
            Log::warning('ConnectionCheckService: Неподдерживаемый протокол', [
                'url' => $url,
                'protocol' => $parsedUrl['scheme'] ?? 'none',
                'allowed_protocols' => $allowedProtocols
            ]);
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

        $maskedUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
        
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

    /**
     * Получает последний код ошибки cURL
     * 
     * @return int|null Код ошибки или null если не было ошибок
     */
    public function getLastCurlError(): ?int
    {
        return $this->lastCurlError;
    }

    /**
     * Получает последнее сообщение об ошибке cURL
     * 
     * @return string|null Сообщение об ошибке или null
     */
    public function getLastCurlErrorMessage(): ?string
    {
        return $this->lastCurlErrorMessage;
    }

    /**
     * Получает последний HTTP статус код
     * 
     * @return int|null HTTP статус код или null
     */
    public function getLastHttpStatusCode(): ?int
    {
        return $this->lastHttpStatusCode;
    }

    /**
     * Получает последнее время выполнения запроса
     * 
     * @return float|null Время выполнения в миллисекундах или null
     */
    public function getLastRequestTime(): ?float
    {
        return $this->lastRequestTime;
    }

    /**
     * Получает расшифровку кода ошибки cURL
     * 
     * @param int $errorCode Код ошибки cURL
     * @return string Расшифровка ошибки
     */
    public function getCurlErrorDescription(int $errorCode): string
    {
        $errorDescriptions = [
            CURLE_OK => 'Операция успешно завершена',
            CURLE_UNSUPPORTED_PROTOCOL => 'Неподдерживаемый протокол',
            CURLE_FAILED_INIT => 'Ошибка инициализации cURL',
            CURLE_URL_MALFORMAT => 'Некорректный URL',
            CURLE_COULDNT_RESOLVE_HOST => 'Не удалось разрешить хост',
            CURLE_COULDNT_CONNECT => 'Не удалось подключиться к хосту',
            CURLE_OPERATION_TIMEDOUT => 'Таймаут операции',
            CURLE_SSL_CONNECT_ERROR => 'Ошибка SSL соединения',
        ];

        return $errorDescriptions[$errorCode] ?? 'Неизвестная ошибка cURL (код: ' . $errorCode . ')';
    }

    /**
     * Проверяет доступность сервера с подробной диагностикой
     * 
     * @param string $url URL сервера 1С
     * @param int $timeout Таймаут соединения
     * @return array Массив с результатами диагностики
     */
    public function diagnosticCheck(string $url, int $timeout = self::DEFAULT_TIMEOUT): array
    {
        $connected = $this->check($url, $timeout);

        return [
            'connected' => $connected,
            'url' => $this->maskUrl($url),
            'curl_error_code' => $this->lastCurlError,
            'curl_error_message' => $this->lastCurlErrorMessage,
            'curl_error_description' => $this->lastCurlError ? 
                $this->getCurlErrorDescription($this->lastCurlError) : 'Нет ошибок',
            'http_status_code' => $this->lastHttpStatusCode,
            'request_time_ms' => $this->lastRequestTime,
            'timestamp' => now()->toISOString()
        ];
    }
}