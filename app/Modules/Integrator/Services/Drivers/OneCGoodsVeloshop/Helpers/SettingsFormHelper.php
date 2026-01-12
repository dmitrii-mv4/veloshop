<?php

namespace App\Modules\Integrator\Services\Drivers\OneCGoodsVeloshop\Helpers;

/**
 * Хелпер для генерации HTML-формы настроек подключения к 1С
 * 
 * Предоставляет форму для настройки параметров подключения
 * к веб-сервису 1С для синхронизации товаров
 */
class SettingsFormHelper
{
    /**
     * Генерирует HTML-форму настроек подключения
     * 
     * @return string HTML-код формы настроек
     */
    public static function getForm(): string
    {
        // Генерируем CSRF-токен для использования в JS
        $csrfToken = csrf_token();
        // Получаем URL для тестирования соединения
        $testUrl = route('admin.integration.testing.test-connection');
        
        $html = <<<HTML
<div class="card mt-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Настройки подключения</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="1c_url" class="form-label">URL веб-сервиса *</label>
                <input type="url" class="form-control" id="1c_url" 
                       name="config[1c_url]"
                       placeholder="http://176.62.189.27:62754/im/4371601201/?type=json"
                       required>
                <div class="form-text">Адрес веб-сервиса для обмена данными</div>
            </div>

            <div class="col-md-6 mb-3">
                <label for="1c_timeout" class="form-label">Таймаут соединения (секунды)</label>
                <input type="number" class="form-control" id="1c_timeout" 
                       name="config[1c_timeout]" 
                       value="10" min="3" max="300">
                <div class="form-text">Максимальное время ожидания ответа от сервера 1С</div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="1c_login" class="form-label">Логин</label>
                    <input type="text" class="form-control" id="1c_login" 
                           name="config[1c_login]"
                           placeholder="admin">
                    <div class="form-text">Логин для доступа к веб-сервису 1С</div>
                </div>
                <div class="col-md-6">
                    <label for="1c_password" class="form-label">Пароль</label>
                    <input type="password" class="form-control" id="1c_password" 
                           name="config[1c_password]">
                    <div class="form-text">Пароль для доступа к веб-сервису 1С</div>
                </div>
            </div>
            
            <!-- Кнопка проверки соединения -->
            <div class="col-12 mt-4">
                <button type="button" id="testConnectionBtn" class="btn btn-outline-primary">
                    <i class="fas fa-plug me-2"></i>Проверить соединение
                </button>
                <div id="connectionResult" class="mt-3" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Направление синхронизации -->
<div class="card mt-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Направление обмена данными</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="form-check card p-3 h-100" style="cursor: pointer;">
                    <input class="form-check-input" type="radio" name="sync_direction" 
                        id="direction_both" value="both">
                    <label class="form-check-label" for="direction_both">
                        <h6 class="mb-1">Двусторонняя</h6>
                        <p class="text-muted small mb-0">Полная синхронизация в обе стороны</p>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Вставьте этот код в вашу форму после кнопки или в отдельный JS файл -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const testConnectionBtn = document.getElementById('testConnectionBtn');
    const connectionResult = document.getElementById('connectionResult');
    
    if (testConnectionBtn) {
        testConnectionBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Собираем данные из формы - используем правильную структуру для контроллера
            const urlInput = document.querySelector('[name="config[1c_url]"]');
            const loginInput = document.querySelector('[name="config[1c_login]"]');
            const passwordInput = document.querySelector('[name="config[1c_password]"]');
            const timeoutInput = document.querySelector('[name="config[1c_timeout]"]');
            
            // Подготавливаем данные в формате, который ожидает TestConnectionRequest
            const formData = {
                config: {
                    '1c_url': urlInput ? urlInput.value : '',
                    '1c_login': loginInput ? loginInput.value : '',
                    '1c_password': passwordInput ? passwordInput.value : '',
                    '1c_timeout': timeoutInput ? timeoutInput.value : '10'
                }
            };
            
            console.log('Sending connection test data:', formData);
            
            // Показываем индикатор загрузки
            testConnectionBtn.disabled = true;
            testConnectionBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Проверяем соединение...';
            connectionResult.style.display = 'block';
            connectionResult.innerHTML = '<div class="alert alert-info">Выполняется проверка соединения...</div>';
            
            // Используем правильный URL из роутов Laravel
            const testUrl = '{$testUrl}';
            console.log('Test URL:', testUrl);
            
            // Отправляем AJAX запрос
            fetch(testUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{$csrfToken}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(formData)
            })
            .then(function(response) {
                console.log('Response status:', response.status);
                if (!response.ok && response.status !== 422) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                return response.json();
            })
            .then(function(data) {
                console.log('Response data:', data);
                
                // Восстанавливаем кнопку
                testConnectionBtn.disabled = false;
                testConnectionBtn.innerHTML = '<i class="fas fa-plug me-2"></i>Проверить соединение';
                
                // Показываем результат
                if (data.success) {
                    const responseTime = (data.data && data.data.response_time_ms) ? data.data.response_time_ms : 'N/A';
                    const statusCode = (data.data && data.data.status_code) ? data.data.status_code : 'N/A';
                    const url = (data.data && data.data.url) ? data.data.url : (urlInput ? urlInput.value : 'Не указан');
                    
                    let resultHtml = '<div class="alert alert-success">' +
                        '<h6 class="alert-heading"><i class="fas fa-check-circle me-2"></i>' + data.message + '</h6>' +
                        '<hr>' +
                        '<p class="mb-1"><strong>URL:</strong> ' + url + '</p>' +
                        '<p class="mb-1"><strong>Время ответа:</strong> ' + responseTime + ' мс</p>' +
                        '<p class="mb-1"><strong>Код статуса:</strong> ' + statusCode + '</p>';
                    
                    if (data.data && data.data.content_type) {
                        resultHtml += '<p class="mb-1"><strong>Тип контента:</strong> ' + data.data.content_type + '</p>';
                    }
                    
                    if (data.data && data.data.server) {
                        resultHtml += '<p class="mb-1"><strong>Сервер:</strong> ' + data.data.server + '</p>';
                    }
                    
                    // Исправленная строка: используем безопасный доступ к timestamp
                    const timestamp = (data.data && data.data.timestamp) ? data.data.timestamp : new Date().toLocaleString();
                    resultHtml += '<p class="mb-0"><strong>Время проверки:</strong> ' + timestamp + '</p>' +
                        '</div>';
                    
                    connectionResult.innerHTML = resultHtml;
                } else {
                    const url = (data.data && data.data.url) ? data.data.url : (urlInput ? urlInput.value : 'Не указан');
                    const suggestion = (data.data && data.data.suggestion) ? data.data.suggestion : '';
                    
                    let errorHtml = '<div class="alert alert-danger">' +
                        '<h6 class="alert-heading"><i class="fas fa-times-circle me-2"></i>' + data.message + '</h6>' +
                        '<hr>' +
                        '<p class="mb-1"><strong>URL:</strong> ' + url + '</p>';
                    
                    if (data.data && data.data.status_code) {
                        errorHtml += '<p class="mb-1"><strong>Код ошибки:</strong> ' + data.data.status_code + '</p>';
                    }
                    
                    if (data.data && data.data.response_time_ms) {
                        errorHtml += '<p class="mb-1"><strong>Время ответа:</strong> ' + data.data.response_time_ms + ' мс</p>';
                    }
                    
                    if (suggestion) {
                        errorHtml += '<p class="mb-2"><strong>Рекомендация:</strong> ' + suggestion + '</p>';
                    }
                    
                    errorHtml += '<p class="mb-0">Проверьте параметры соединения и попробуйте снова.</p>' +
                        '</div>';
                    
                    connectionResult.innerHTML = errorHtml;
                }
            })
            .catch(function(error) {
                console.error('Connection test error:', error);
                
                // Восстанавливаем кнопку
                testConnectionBtn.disabled = false;
                testConnectionBtn.innerHTML = '<i class="fas fa-plug me-2"></i>Проверить соединение';
                
                // Показываем ошибку
                connectionResult.innerHTML = 
                    '<div class="alert alert-danger">' +
                        '<h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Ошибка выполнения запроса</h6>' +
                        '<hr>' +
                        '<p class="mb-1"><strong>Сообщение:</strong> ' + (error.message || 'Неизвестная ошибка') + '</p>' +
                        '<p class="mb-0">Проверьте консоль браузера для подробной информации.</p>' +
                    '</div>';
            });
        });
    }
});
</script>
HTML;

        return $html;
    }

    /**
     * Генерирует HTML-форму с предзаполненными значениями
     * 
     * @param array $config Конфигурационные параметры
     * @return string HTML-код формы с заполненными значениями
     */
    public static function getFormWithValues(array $config = []): string
    {
        $form = self::getForm();
        
        // Заменяем значения в форме на переданные из конфига
        foreach ($config as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'checked' : '';
                $form = preg_replace(
                    '/name="config\[' . preg_quote($key) . '\]"[^>]*>/',
                    'name="config[' . $key . ']" ' . $value . '>',
                    $form
                );
            } else {
                $form = preg_replace(
                    '/(name="config\[' . preg_quote($key) . '\][^>]*value=")[^"]*"/',
                    '$1' . htmlspecialchars($value) . '"',
                    $form
                );
                
                // Для select элементов
                $form = preg_replace(
                    '/(<option[^>]*value="' . preg_quote($value) . '"[^>]*)>/',
                    '$1 selected>',
                    $form
                );
            }
        }
        
        return $form;
    }
}