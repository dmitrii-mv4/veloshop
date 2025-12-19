@extends('admin::layouts.default')

@section('title', 'Создание интеграции | KotiksCMS')

@push('styles')
<style>
/* Стили для интеграции */
.service-card {
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
    height: 100%;
}

.service-card:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.service-card.selected {
    border-color: #007bff;
    background-color: rgba(0, 123, 255, 0.05);
}

.step-indicator {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2rem;
    position: relative;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    z-index: 2;
    position: relative;
    flex: 1;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
}

.step.active .step-number {
    background: #007bff;
    color: white;
}

.step.completed .step-number {
    background: #28a745;
    color: white;
}

.step-label {
    font-size: 0.875rem;
    text-align: center;
    color: #6c757d;
}

.step.active .step-label {
    color: #007bff;
    font-weight: 500;
}

.step-line {
    flex: 1;
    height: 2px;
    background: #e9ecef;
    margin: 0 10px;
    position: relative;
    top: -20px;
}

.step-content {
    display: none;
    animation: fadeIn 0.3s ease;
}

.step-content.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.field-mapping-row {
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
    background: white;
}

.field-mapping-row:hover {
    background-color: #f8f9fa;
    border-left-color: #007bff;
}

.field-type-info {
    font-size: 0.8rem;
    color: #6c757d;
}

.custom-field-name {
    font-size: 0.875rem;
}

/* Индикатор загрузки */
#moduleFieldsLoader {
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
    display: none;
}

/* Адаптивность для мобильных */
@media (max-width: 768px) {
    .step-indicator {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .step {
        flex-direction: row;
        width: 100%;
        margin-bottom: 1rem;
        align-items: center;
    }
    
    .step-number {
        margin-bottom: 0;
        margin-right: 1rem;
        min-width: 40px;
    }
    
    .step-line {
        display: none;
    }
    
    .step-label {
        text-align: left;
    }
    
    .field-mapping-header {
        display: none;
    }
    
    .field-mapping-row {
        padding: 1rem !important;
    }
    
    .field-mapping-row .row {
        flex-direction: column;
    }
    
    .field-mapping-row .col-md-2 {
        text-align: center;
        margin: 0.5rem 0;
    }
}

/* Для выравнивания полей в правой колонке */
.align-right-content {
    display: flex;
    flex-direction: column;
}

/* Примеры интеграций */
.integration-example {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-top: 1rem;
    color: white;
}

.integration-example h5 {
    color: white;
}

/* Toast уведомления */
.toast-container {
    z-index: 9999;
}
</style>
@endpush

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        <!-- Подключаем breadcrumb -->
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['url' => route('admin.integration.index'), 'title' => 'Интеграции с внешними сервисами'],
                ['title' => 'Создание интеграции']
            ]
        ])
    </div>

    <!-- Действия с модулями -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Создание интеграции</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">Настройте подключение между внешним сервисом и внутренним модулем</p>
        </div>
        <a href="{{ route('admin.integration.create') }}" class="btn btn-primary">
            <i class="fas fa-save me-2"></i> Сохранить черновик
        </a>
    </div>

    <div class="container-fluid py-4">
        <!-- Индикатор шагов -->
        <div class="step-indicator">
            <div class="step" data-step="1">
                <div class="step-number">1</div>
                <div class="step-label">Выбор внешнего сервиса</div>
            </div>
            <div class="step-line"></div>
            <div class="step" data-step="2">
                <div class="step-number">2</div>
                <div class="step-label">Настройки подключения</div>
            </div>
            <div class="step-line"></div>
            <div class="step" data-step="3">
                <div class="step-number">3</div>
                <div class="step-label">Выбор модуля</div>
            </div>
            <div class="step-line"></div>
            <div class="step" data-step="4">
                <div class="step-number">4</div>
                <div class="step-label">Сопоставление полей</div>
            </div>
        </div>

        <!-- Форма создания интеграции -->
        <form action="{{ route('admin.integration.store') }}" method="POST" enctype="multipart/form-data" id="integrationForm">
            @csrf
            
            @if($errors->any())
                <div class="alert alert-danger">
                    <h5>Ошибки валидации:</h5>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <!-- Шаг 1: Выбор внешнего сервиса -->
            <div class="step-content" id="step1">
                <div class="card shadow border-0 mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-external-link-alt me-2 text-primary"></i>Выберите внешний сервис
                        </h5>
                        <p class="text-muted small mb-0 mt-1">Выберите сервис, с которым будет интегрироваться ваша система</p>
                    </div>
                    <div class="card-body">
                        <div class="row" id="externalServicesList">
                            <!-- Сервис 1: 1C -->
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="service-card"
                                    data-service="1c"
                                    data-service-name="1C:Предприятие"
                                    data-service-type="erp"
                                    data-service-icon="fas fa-database"
                                    data-service-category="ERP система">
                                    <div class="d-flex align-items-start mb-2">
                                        <div class="bg-primary-soft rounded-circle p-3 me-3">
                                            <i class="fas fa-database fa-2x text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">1C:Предприятие</h6>
                                            <span class="badge bg-primary">ERP система</span>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        Интеграция с системами 1С для обмена товарами, ценами, заказами
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Выбранный сервис -->
                        <div class="alert alert-primary mt-3" id="selectedServiceAlert" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Выбран сервис:</strong>
                                    <span id="selectedServiceName" class="ms-2"></span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="changeServiceBtn">
                                    Отменить выбор
                                </button>
                            </div>
                        </div>

                        <!-- Кнопки навигации -->
                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" class="btn btn-primary" id="nextToStep2">
                                Далее: Настройки подключения <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Примеры использования -->
                <div class="integration-example">
                    <h5 class="text-white mb-3"><i class="fas fa-lightbulb me-2"></i>Примеры интеграций</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="bg-white bg-opacity-25 p-3 rounded">
                                <h6 class="text-white mb-2">1С → Сайт</h6>
                                <p class="small mb-0">Товары, цены, остатки автоматически обновляются на сайте</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="bg-white bg-opacity-25 p-3 rounded">
                                <h6 class="text-white mb-2">Сайт → Telegram</h6>
                                <p class="small mb-0">Новые заказы приходят в Telegram-бот менеджера</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="bg-white bg-opacity-25 p-3 rounded">
                                <h6 class="text-white mb-2">Яндекс.Маркет → Сайт</h6>
                                <p class="small mb-0">Заказы с маркетплейса автоматически создаются в системе</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Скрытые поля для данных сервиса -->
                <input type="hidden" id="selected_service" name="service" value="">
                <input type="hidden" id="selected_service_name" name="service_name" value="">
                <input type="hidden" id="selected_service_type" name="service_type" value="">
                <input type="hidden" id="selected_service_icon" name="service_icon" value="">
                <input type="hidden" id="selected_service_category" name="service_category" value="">
            </div>

            <!-- Шаг 2: Настройки подключения к выбранному сервису -->
            <div class="step-content" id="step2">
                <div class="card shadow border-0 mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cog me-2 text-primary"></i>Настройки подключения
                            <small class="text-muted ms-2" id="currentServiceName"></small>
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Настройки для 1C -->
                        <div class="service-settings" id="settings-1c" style="display: none;">
                            <div class="alert alert-info mb-4">
                                <i class="fas fa-info-circle me-2"></i>
                                Для работы с 1С используйте веб-сервисы или обмен через файлы (xml, json)
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="1c_url" class="form-label">URL веб-сервиса 1С *</label>
                                    <input type="url" class="form-control" id="1c_url" 
                                           placeholder="http://1c-server.example.com/ws/example">
                                    <div class="form-text">Адрес веб-сервиса 1С для обмена данными</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="1c_login" class="form-label">Логин</label>
                                    <input type="text" class="form-control" id="1c_login" 
                                           placeholder="admin">
                                    <div class="form-text">Логин для доступа к веб-сервису 1С</div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="1c_password" class="form-label">Пароль</label>
                                    <input type="password" class="form-control" id="1c_password">
                                </div>
                                <div class="col-md-6">
                                    <label for="1c_sync_type" class="form-label">Тип файла *</label>
                                    <select class="form-select" id="1c_sync_type">
                                        <option value="web_service">json</option>
                                        <option value="file_exchange">xml</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="1c_sync_interval" class="form-label">Интервал синхронизации</label>
                                    <select class="form-select" id="1c_sync_interval">
                                        <option value="5">Каждые 5 минут</option>
                                        <option value="15" selected>Каждые 15 минут</option>
                                        <option value="30">Каждые 30 минут</option>
                                        <option value="60">Каждый час</option>
                                        <option value="manual">Вручную</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Общие настройки -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Дополнительные настройки</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="integration_name" class="form-label">Название интеграции *</label>
                                        <input type="text" class="form-control" id="integration_name" 
                                               name="name"
                                               placeholder="Например: Синхронизация с 1С">
                                        <div class="form-text">Уникальное название для вашей интеграции</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="integration_description" class="form-label">Описание</label>
                                        <textarea class="form-control" id="integration_description" 
                                                  rows="2" placeholder="Опишите назначение интеграции..."></textarea>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_active" checked>
                                            <label class="form-check-label" for="is_active">
                                                Интеграция активна
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="log_requests">
                                            <label class="form-check-label" for="log_requests">
                                                Логировать запросы
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="auto_retry">
                                            <label class="form-check-label" for="auto_retry">
                                                Автоповтор при ошибке
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Кнопки навигации -->
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary" id="backToStep1">
                                <i class="fas fa-arrow-left me-2"></i> Назад: Выбор сервиса
                            </button>
                            <button type="button" class="btn btn-primary" id="nextToStep3">
                                Далее: Выбор модуля <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Шаг 3: Выбор внутреннего модуля -->
            <div class="step-content" id="step3">
                <div class="card shadow border-0 mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cubes me-2 text-primary"></i>Выберите внутренний модуль
                        </h5>
                        <p class="text-muted small mb-0 mt-1">Выберите модуль, который будет взаимодействовать с внешним сервисом</p>
                    </div>
                    <div class="card-body">
                        <div class="row" id="internalModulesList">
                            @if(count($modules) > 0)
                                @foreach ($modules as $module)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="service-card" 
                                             data-module="{{$module['code_module']}}" 
                                             data-module-name="{{$module['code_module']}}">
                                            <div class="d-flex align-items-start mb-2">
                                                <div class="bg-success-soft rounded-circle p-3 me-3">
                                                    <i class="fas fa-cube fa-2x text-success"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">{{$module['code_module']}}</h6>
                                                    <span class="badge bg-success">Модуль</span>
                                                </div>
                                            </div>
                                            <p class="text-muted small mb-0">
                                                Модуль для интеграции данных
                                            </p>
                                            <div class="mt-2">
                                                <small class="text-muted">Выберите для сопоставления полей</small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="col-12">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Нет доступных модулей для интеграции. Создайте модули через генератор модулей.
                                    </div>
                                </div>
                            @endif
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
                                                   id="direction_import" value="import" checked>
                                            <label class="form-check-label" for="direction_import">
                                                <h6 class="mb-1">Импорт</h6>
                                                <p class="text-muted small mb-0">Данные поступают из внешнего сервиса в модуль</p>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check card p-3 h-100" style="cursor: pointer;">
                                            <input class="form-check-input" type="radio" name="sync_direction" 
                                                   id="direction_export" value="export">
                                            <label class="form-check-label" for="direction_export">
                                                <h6 class="mb-1">Экспорт</h6>
                                                <p class="text-muted small mb-0">Данные отправляются из модуля во внешний сервис</p>
                                            </label>
                                        </div>
                                    </div>
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

                        <!-- Кнопки навигации -->
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary" id="backToStep2">
                                <i class="fas fa-arrow-left me-2"></i> Назад: Настройки
                            </button>
                            @if(count($modules) > 0)
                            <button type="button" class="btn btn-primary" id="nextToStep4">
                                Далее: Сопоставление полей <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                            @else
                            <button type="button" class="btn btn-primary" disabled>
                                Нет доступных модулей
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Шаг 4: Сопоставление полей -->
            <div class="step-content" id="step4">
                <div class="card shadow border-0 mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-project-diagram me-2 text-primary"></i>Сопоставление полей
                        </h5>
                        <p class="text-muted small mb-0 mt-1">
                            Сопоставьте поля внешнего сервиса с полями модуля <span id="selectedModuleLabel">(не выбран)</span>
                        </p>
                    </div>
                    <div class="card-body">
                        <!-- Информация о выбранных сервисе и модуле -->
                        <div class="alert alert-info mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Внешний сервис:</strong>
                                    <div id="currentServiceInfo" class="mt-1">(не выбран)</div>
                                </div>
                                <div class="col-md-6">
                                    <strong>Внутренний модуль:</strong>
                                    <div id="currentModuleInfo" class="mt-1">(не выбран)</div>
                                </div>
                            </div>
                        </div>

                        <!-- Индикатор загрузки -->
                        <div class="mb-4" id="moduleFieldsLoader">
                            <div class="d-flex align-items-center justify-content-center p-4">
                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                    <span class="visually-hidden">Загрузка...</span>
                                </div>
                                <span>Загрузка полей модуля...</span>
                            </div>
                        </div>

                        <!-- Сообщение об ошибке -->
                        <div class="alert alert-danger mb-4" id="fieldsError" style="display: none;"></div>

                        <!-- Сопоставление полей -->
                        <div class="field-mapping-container" id="fieldMappingContainer">
                            <div class="field-mapping-header bg-light p-3 rounded-top">
                                <div class="row align-items-center">
                                    <div class="col-md-5">
                                        <strong>Поле внешнего сервиса (1C)</strong>
                                        <small class="text-muted d-block">Введите название поля из 1С</small>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <strong>→</strong>
                                    </div>
                                    <div class="col-md-5 align-right-content">
                                        <strong>Поле внутреннего модуля</strong>
                                        <small class="text-muted d-block">Выберите поле из модуля</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Контейнер для полей модуля (будет заполнен динамически) -->
                            <div id="fieldMappingRows" class="rounded-bottom"></div>
                        </div>

                        <!-- Кнопки завершения -->
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary" id="backToStep3">
                                <i class="fas fa-arrow-left me-2"></i> Назад: Выбор модуля
                            </button>
                            
                            <div>
                                <button type="button" class="btn btn-outline-success me-2" id="testIntegrationBtn">
                                    <i class="fas fa-vial me-2"></i> Тестировать
                                </button>
                                <button type="submit" class="btn btn-success" id="createIntegrationBtn">
                                    <i class="fas fa-check me-2"></i> Создать интеграцию
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Скрытые поля для передачи данных -->
            <input type="hidden" name="selected_module" id="selectedModuleInput" value="">
            <input type="hidden" name="field_mapping" id="fieldMappingInput" value="">

            <!-- Шаблон для строки сопоставления полей -->
            <template id="fieldMappingTemplate">
                <div class="field-mapping-row border-bottom p-3">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <div class="mb-2">
                                <label class="form-label small mb-1">Поле 1C:</label>
                                <input type="text" 
                                    class="form-control field-1c-input" 
                                    placeholder="Например: Наименование"
                                    data-field-type="">
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <i class="fas fa-arrow-right fa-lg text-muted"></i>
                        </div>
                        <div class="col-md-5 align-right-content">
                            <div class="mb-2">
                                <label class="form-label small mb-1">Поле модуля:</label>
                                <select class="form-select field-module-select">
                                    <option value="">-- Выберите поле --</option>
                                </select>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-muted field-type-info"></small>
                                <div class="form-check">
                                    <input class="form-check-input field-required" type="checkbox">
                                    <label class="form-check-label small">Обязательное</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-field-btn" style="display: none;">
                            <i class="fas fa-times"></i> Удалить
                        </button>
                    </div>
                </div>
            </template>
        </form>
    </div>

    <!-- Модальное окно тестирования -->
    <div class="modal fade" id="testModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-vial me-2"></i>Тестирование интеграции</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="testResults"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-primary" id="runTestBtn">Запустить тест</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/integration-create.js') }}"></script>
@endpush