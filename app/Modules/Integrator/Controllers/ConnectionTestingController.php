<?php

namespace App\Modules\Integrator\Controllers;

use App\Core\Controllers\Controller;
use App\Modules\Integrator\Requests\TestConnectionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Validation\ValidationException;

class ConnectionTestingController extends Controller
{
    /**
     * Отображает страницу тестирования соединения
     */
    public function index()
    {
        return view('integrator.testing.index');
    }

    /**
     * Проверяет соединение с удаленным сервером
     * 
     * @param TestConnectionRequest $request Валидированные данные соединения
     * @return JsonResponse Результат проверки соединения
     */
    public function testConnection(TestConnectionRequest $request): JsonResponse
    {
        $logContext = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toDateTimeString()
        ];

        try {
            $validated = $request->validated();
            
            Log::info('Connection test started', array_merge($logContext, [
                'url' => $validated['url'] ?? 'not provided',
                'has_login' => !empty($validated['login']),
                'has_timeout' => !empty($validated['timeout'])
            ]));

            // Проверяем обязательные параметры
            if (empty($validated['url'])) {
                Log::warning('Connection test failed: URL is empty', $logContext);
                return $this->errorResponse('URL обязателен для проверки соединения');
            }

            // Формируем URL для проверки
            $testUrl = $this->buildTestUrl($validated);
            
            // Настраиваем параметры HTTP клиента
            $httpOptions = $this->prepareHttpOptions($validated);
            
            // Выполняем тестовый запрос
            $result = $this->performConnectionTest($testUrl, $httpOptions);
            
            Log::info('Connection test completed', array_merge($logContext, [
                'success' => $result['success'] ?? false,
                'url' => $testUrl,
                'response_time' => $result['data']['response_time_ms'] ?? null,
                'status_code' => $result['data']['status_code'] ?? null
            ]));
            
            return $this->successResponse($result['message'], $result['data'] ?? []);
            
        } catch (ValidationException $e) {
            $errors = $e->errors();
            Log::error('Connection test validation failed', array_merge($logContext, [
                'errors' => $errors,
                'input' => $request->all()
            ]));
            
            return $this->errorResponse(
                'Ошибка валидации: ' . implode(', ', array_merge(...array_values($errors)))
            );
        } catch (Exception $e) {
            Log::error('Connection test failed with exception', array_merge($logContext, [
                'error' => $e->getMessage(),
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'url' => $validated['url'] ?? 'not provided'
            ]));
            
            return $this->errorResponse(
                'Ошибка при проверке соединения: ' . $e->getMessage()
            );
        }
    }

    /**
     * Формирует URL для тестирования соединения
     * 
     * @param array $params Параметры соединения
     * @return string Сформированный URL
     */
    private function buildTestUrl(array $params): string
    {
        $url = trim($params['url']);
        
        // Убеждаемся, что URL начинается с протокола
        if (!preg_match('/^https?:\/\//i', $url)) {
            Log::warning('URL without protocol provided, adding http://', ['original_url' => $url]);
            $url = 'http://' . $url;
        }
        
        // Удаляем дублирование протокола (если кто-то ввел http://http://example.com)
        $url = preg_replace('/^(https?:\/\/)+/i', '$1', $url);
        
        // Убеждаемся, что URL правильно сформирован
        $parsed = parse_url($url);
        
        if ($parsed === false) {
            throw new \InvalidArgumentException('Некорректный URL: ' . $url);
        }
        
        return $url;
    }

    /**
     * Подготавливает параметры для HTTP клиента
     * 
     * @param array $params Параметры соединения
     * @return array Настройки HTTP клиента
     */
    private function prepareHttpOptions(array $params): array
    {
        $options = [
            'timeout' => $params['timeout'] ?? 10,
            'connect_timeout' => $params['timeout'] ?? 10,
            'verify' => false, // Отключаем проверку SSL для тестирования
            'http_errors' => false, // Не выбрасывать исключения при HTTP ошибках
        ];
        
        // Добавляем базовую аутентификацию, если указаны логин/пароль
        if (!empty($params['login']) && !empty($params['password'])) {
            $options['auth'] = [
                $params['login'],
                $params['password']
            ];
        } elseif (!empty($params['login'])) {
            $options['auth'] = [
                $params['login'],
                $params['password'] ?? ''
            ];
        }
        
        // Добавляем заголовки
        $options['headers'] = [
            'User-Agent' => 'Kotiks CMS Connection Tester/1.0',
            'Accept' => 'application/json, text/plain, */*',
            'Accept-Encoding' => 'gzip, deflate',
            'Connection' => 'close',
        ];
        
        return $options;
    }

    /**
     * Выполняет тестирование соединения
     * 
     * @param string $url URL для тестирования
     * @param array $options Параметры HTTP клиента
     * @return array Результат проверки
     */
    private function performConnectionTest(string $url, array $options): array
    {
        $startTime = microtime(true);
        
        try {
            Log::debug('Sending test request', [
                'url' => $url,
                'options' => $this->sanitizeOptionsForLog($options)
            ]);
            
            $response = Http::withOptions($options)->get($url);
            
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2);
            
            $statusCode = $response->status();
            
            Log::debug('Response received', [
                'url' => $url,
                'status_code' => $statusCode,
                'response_time_ms' => $responseTime,
                'headers' => $response->headers(),
                'body_preview' => substr($response->body(), 0, 500)
            ]);
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Соединение успешно установлено',
                    'data' => [
                        'response_time_ms' => $responseTime,
                        'status_code' => $statusCode,
                        'url' => $url,
                        'timestamp' => now()->toDateTimeString(),
                        'content_type' => $response->header('Content-Type'),
                        'server' => $response->header('Server'),
                        'body_preview' => substr($response->body(), 0, 200)
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $this->getStatusMessage($statusCode),
                    'data' => [
                        'response_time_ms' => $responseTime,
                        'status_code' => $statusCode,
                        'url' => $url,
                        'error_message' => $response->body() ?: 'Пустой ответ от сервера'
                    ]
                ];
            }
            
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Connection exception during test', [
                'url' => $url,
                'error' => $e->getMessage(),
                'connection_timeout' => $options['connect_timeout'] ?? 'not set'
            ]);
            
            return [
                'success' => false,
                'message' => 'Не удалось установить соединение: ' . $e->getMessage(),
                'data' => [
                    'url' => $url,
                    'error_type' => 'connection_exception',
                    'suggestion' => 'Проверьте доступность сервера, правильность URL и настройки сети'
                ]
            ];
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Request exception during test', [
                'url' => $url,
                'error' => $e->getMessage(),
                'response' => $e->response ? [
                    'status' => $e->response->status(),
                    'body_preview' => substr($e->response->body(), 0, 500)
                ] : null
            ]);
            
            return [
                'success' => false,
                'message' => 'Ошибка запроса: ' . $e->getMessage(),
                'data' => [
                    'url' => $url,
                    'error_type' => 'request_exception'
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Unexpected exception during connection test', [
                'url' => $url,
                'error' => $e->getMessage(),
                'exception_class' => get_class($e)
            ]);
            
            return [
                'success' => false,
                'message' => 'Неожиданная ошибка: ' . $e->getMessage(),
                'data' => [
                    'url' => $url,
                    'error_type' => 'unexpected_exception'
                ]
            ];
        }
    }

    /**
     * Очищает опции для логирования (удаляет пароли)
     */
    private function sanitizeOptionsForLog(array $options): array
    {
        $sanitized = $options;
        
        if (isset($sanitized['auth'])) {
            $sanitized['auth'] = [
                $sanitized['auth'][0],
                '***HIDDEN***'
            ];
        }
        
        return $sanitized;
    }

    /**
     * Получает понятное сообщение по статус коду
     */
    private function getStatusMessage(int $statusCode): string
    {
        $messages = [
            400 => 'Некорректный запрос',
            401 => 'Требуется аутентификация',
            403 => 'Доступ запрещен',
            404 => 'Страница не найдена',
            405 => 'Метод не разрешен',
            408 => 'Истекло время ожидания',
            500 => 'Внутренняя ошибка сервера',
            502 => 'Ошибка шлюза',
            503 => 'Сервис недоступен',
            504 => 'Истекло время ожидания шлюза',
        ];
        
        $baseMessage = $messages[$statusCode] ?? "Сервер ответил с кодом ошибки: {$statusCode}";
        
        if ($statusCode >= 400 && $statusCode < 500) {
            return "Ошибка клиента: {$baseMessage}";
        } elseif ($statusCode >= 500) {
            return "Ошибка сервера: {$baseMessage}";
        }
        
        return $baseMessage;
    }

    /**
     * Формирует успешный JSON ответ
     */
    private function successResponse(string $message, array $data = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * Формирует ошибочный JSON ответ
     */
    private function errorResponse(string $message, array $data = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data
        ], 422);
    }
}