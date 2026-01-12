<?php

namespace App\Modules\Integrator\Services\Drivers\Test\Helpers;

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
        return <<<HTML
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
                       placeholder="http://1c-server.example.com/ws/example"
                       required>
                <div class="form-text">Адрес веб-сервиса для обмена данными</div>
            </div>

            <div class="col-md-6 mb-3">
                <label for="1c_sync_interval" class="form-label">Тип соединения</label>
                <select class="form-select" id="1c_sync_interval" name="config[1c_sync_interval]">
                    <option value="HTTP">HTTP</option>
                    <option value="HTTPS" selected>HTTPS</option>
                </select>
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
        </div>
    </div>
</div>
HTML;
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