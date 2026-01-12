<?php

namespace App\Modules\Integrator\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TestConnectionRequest extends FormRequest
{
    /**
     * Правила валидации для проверки параметров соединения
     */
    public function rules(): array
    {
        return [
            'url' => [
                'required',
                'string',
                'regex:/^(http|https):\/\/[^\s$.?#].[^\s]*$/i'
            ],
            'port' => [
                'nullable',
                'integer',
                'min:1',
                'max:65535'
            ],
            'login' => [
                'nullable',
                'string',
                'max:255'
            ],
            'password' => [
                'nullable',
                'string',
                'max:255'
            ],
            'timeout' => [
                'nullable',
                'integer',
                'min:1',
                'max:300'
            ],
            'connection_type' => [
                'nullable',
                'string',
                'in:HTTP,HTTPS'
            ]
        ];
    }

    /**
     * Кастомные сообщения об ошибках
     */
    public function messages(): array
    {
        return [
            'url.required' => 'URL обязателен для проверки соединения',
            'url.regex' => 'URL должен быть валидным веб-адресом',
            'port.integer' => 'Порт должен быть числовым значением',
            'port.min' => 'Порт не может быть меньше 1',
            'port.max' => 'Порт не может быть больше 65535',
            'timeout.min' => 'Таймаут не может быть меньше 1 секунды',
            'timeout.max' => 'Таймаут не может превышать 300 секунд',
            'connection_type.in' => 'Тип соединения должен быть HTTP или HTTPS'
        ];
    }

    /**
     * Подготовка данных для валидации
     */
    protected function prepareForValidation(): void
    {
        // Если пришли данные из формы с префиксом 1c_, преобразуем их
        $config = $this->input('config', []);
        
        if (!empty($config)) {
            $this->merge([
                'url' => $config['1c_url'] ?? $config['url'] ?? null,
                'port' => $config['1c_port'] ?? $config['port'] ?? null,
                'login' => $config['1c_login'] ?? $config['login'] ?? null,
                'password' => $config['1c_password'] ?? $config['password'] ?? null,
                'timeout' => $config['1c_timeout'] ?? $config['timeout'] ?? null,
                'connection_type' => $config['1c_sync_interval'] ?? $config['connection_type'] ?? 'HTTP'
            ]);
        }
    }
}