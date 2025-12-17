@extends('admin::layouts.default')

@section('title', 'Создание интеграции | KotiksCMS')

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
        <!-- Навигация -->
        {{-- <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="integration_index_static.html"><i class="fas fa-home"></i> Главная</a></li>
                <li class="breadcrumb-item"><a href="integration_index_static.html">Интеграции</a></li>
                <li class="breadcrumb-item active" aria-current="page">Создание интеграции</li>
            </ol>
        </nav> --}}

        <!-- Заголовок -->
        {{-- <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-plus-circle me-2"></i>Создание новой интеграции
                </h1>
                <p class="text-muted mb-0">Настройте подключение между внешним сервисом и внутренним модулем</p>
            </div>
            <div>
                <button type="button" class="btn btn-outline-secondary me-2" id="cancelBtn">
                    <i class="fas fa-times me-2"></i>Отмена
                </button>
                <button type="button" class="btn btn-primary" id="saveDraftBtn">
                    <i class="fas fa-save me-2"></i>Сохранить черновик
                </button>
            </div>
        </div> --}}

        <!-- Индикатор шагов -->
        <div class="step-indicator">
            <div class="step active" data-step="1">
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
            <div class="step-content active" id="step1">
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
                                            <input type="hidden" id="selected_service" name="1C" value="">
                                            <h6 class="mb-1">1C:Предприятие</h6>
                                            <span class="badge bg-primary">ERP система</span>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        Интеграция с системами 1С для обмена товарами, ценами, заказами
                                    </p>
                                </div>
                            </div>

                            <!-- Сервис 2: Telegram -->
                            {{-- <div class="col-md-6 col-lg-4 mb-3">
                                <div class="service-card" data-service="telegram" data-service-name="Telegram Bot API">
                                    <div class="d-flex align-items-start mb-2">
                                        <div class="bg-info-soft rounded-circle p-3 me-3">
                                            <i class="fab fa-telegram fa-2x text-info"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Telegram Bot API</h6>
                                            <span class="badge bg-info">Мессенджер</span>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        Отправка уведомлений, сообщений и управление через Telegram бота
                                    </p>
                                </div>
                            </div> --}}

                            <!-- Сервис 3: Email -->
                            {{-- <div class="col-md-6 col-lg-4 mb-3">
                                <div class="service-card" data-service="email" data-service-name="Email (SMTP)">
                                    <div class="d-flex align-items-start mb-2">
                                        <div class="bg-warning-soft rounded-circle p-3 me-3">
                                            <i class="fas fa-envelope fa-2x text-warning"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Email (SMTP)</h6>
                                            <span class="badge bg-warning">Почта</span>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        Отправка email через SMTP серверы (Gmail, Yandex, Mail.ru и др.)
                                    </p>
                                </div>
                            </div> --}}

                            <!-- Сервис 4: Яндекс.Маркет -->
                            {{-- <div class="col-md-6 col-lg-4 mb-3">
                                <div class="service-card" data-service="yandex_market" data-service-name="Яндекс.Маркет">
                                    <div class="d-flex align-items-start mb-2">
                                        <div class="bg-danger-soft rounded-circle p-3 me-3">
                                            <i class="fas fa-shopping-cart fa-2x text-danger"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Яндекс.Маркет</h6>
                                            <span class="badge bg-danger">Маркетплейс</span>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        Выгрузка товаров и получение заказов с Яндекс.Маркет
                                    </p>
                                </div>
                            </div> --}}

                            <!-- Сервис 5: Wildberries -->
                            {{-- <div class="col-md-6 col-lg-4 mb-3">
                                <div class="service-card" data-service="wildberries" data-service-name="Wildberries">
                                    <div class="d-flex align-items-start mb-2">
                                        <div class="bg-success-soft rounded-circle p-3 me-3">
                                            <i class="fas fa-tshirt fa-2x text-success"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Wildberries</h6>
                                            <span class="badge bg-success">Маркетплейс</span>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        Интеграция с маркетплейсом Wildberries для выгрузки товаров и заказов
                                    </p>
                                </div>
                            </div> --}}

                            <!-- Сервис 6: Google Sheets -->
                            {{-- <div class="col-md-6 col-lg-4 mb-3">
                                <div class="service-card" data-service="google_sheets" data-service-name="Google Таблицы">
                                    <div class="d-flex align-items-start mb-2">
                                        <div class="bg-info-soft rounded-circle p-3 me-3">
                                            <i class="fab fa-google fa-2x text-info"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Google Таблицы</h6>
                                            <span class="badge bg-info">Документы</span>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        Импорт/экспорт данных в Google Sheets через API
                                    </p>
                                </div>
                            </div> --}}
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

                        <!-- Настройки для Telegram -->
                        <div class="service-settings" id="settings-telegram" style="display: none;">
                            <div class="alert alert-info mb-4">
                                <i class="fas fa-info-circle me-2"></i>
                                Создайте бота через @BotFather и получите токен для настройки
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="telegram_token" class="form-label">Токен бота *</label>
                                    <input type="password" class="form-control" id="telegram_token" 
                                           placeholder="1234567890:ABCdefGHIjklMNOpqrsTUVwxyz">
                                    <div class="form-text">Токен, полученный от @BotFather</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="telegram_chat_id" class="form-label">ID чата/канала</label>
                                    <input type="text" class="form-control" id="telegram_chat_id" 
                                           placeholder="-1001234567890">
                                    <div class="form-text">Для отправки сообщений в конкретный чат</div>
                                </div>
                            </div>
                        </div>

                        <!-- Настройки для Яндекс.Маркет -->
                        <div class="service-settings" id="settings-yandex_market" style="display: none;">
                            <div class="alert alert-info mb-4">
                                <i class="fas fa-info-circle me-2"></i>
                                Для работы с API Яндекс.Маркет необходим OAuth-токен
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="yandex_client_id" class="form-label">Client ID *</label>
                                    <input type="text" class="form-control" id="yandex_client_id">
                                </div>
                                <div class="col-md-6">
                                    <label for="yandex_token" class="form-label">OAuth токен *</label>
                                    <input type="password" class="form-control" id="yandex_token">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="yandex_campaign_id" class="form-label">ID кампании *</label>
                                    <input type="text" class="form-control" id="yandex_campaign_id">
                                </div>
                                <div class="col-md-6">
                                    <label for="yandex_sync_type" class="form-label">Тип выгрузки</label>
                                    <select class="form-select" id="yandex_sync_type">
                                        <option value="products">Товары и цены</option>
                                        <option value="orders">Заказы</option>
                                        <option value="both">Товары и заказы</option>
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
                                               placeholder="Например: Синхронизация с 1С">
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

                            @foreach ($modules as $module)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="service-card" data-module="products" data-module-name="{{$module['code_module']}}">
                                        <div class="d-flex align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-1">{{$module['code_module']}}</h6>
                                            </div>
                                        </div>
                                        <p class="text-muted small mb-0">
                                            Управление товарами, ценами, остатками, категориями
                                        </p>
                                        <div class="mt-2">
                                            <small class="text-muted">Поля: название, цена, описание, артикул</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach



                            <!-- Модуль 1: Товары -->
                            {{-- <div class="col-md-6 col-lg-4 mb-3">
                                <div class="service-card" data-module="products" data-module-name="Товары">
                                    <div class="d-flex align-items-start mb-2">
                                        <div class="bg-primary-soft rounded-circle p-3 me-3">
                                            <i class="fas fa-box fa-2x text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Товары</h6>
                                            <span class="badge bg-primary">Каталог</span>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        Управление товарами, ценами, остатками, категориями
                                    </p>
                                    <div class="mt-2">
                                        <small class="text-muted">Поля: название, цена, описание, артикул</small>
                                    </div>
                                </div>
                            </div> --}}

                            <!-- Модуль 2: Заказы -->
                            {{-- <div class="col-md-6 col-lg-4 mb-3">
                                <div class="service-card" data-module="orders" data-module-name="Заказы">
                                    <div class="d-flex align-items-start mb-2">
                                        <div class="bg-success-soft rounded-circle p-3 me-3">
                                            <i class="fas fa-shopping-cart fa-2x text-success"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Заказы</h6>
                                            <span class="badge bg-success">Продажи</span>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        Обработка заказов, статусы, клиенты, доставка
                                    </p>
                                    <div class="mt-2">
                                        <small class="text-muted">Поля: номер, клиент, сумма, статус</small>
                                    </div>
                                </div>
                            </div> --}}

                            <!-- Модуль 3: Клиенты -->
                            {{-- <div class="col-md-6 col-lg-4 mb-3">
                                <div class="service-card" data-module="customers" data-module-name="Клиенты">
                                    <div class="d-flex align-items-start mb-2">
                                        <div class="bg-info-soft rounded-circle p-3 me-3">
                                            <i class="fas fa-users fa-2x text-info"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Клиенты</h6>
                                            <span class="badge bg-info">CRM</span>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        Управление клиентами, контактами, историями заказов
                                    </p>
                                    <div class="mt-2">
                                        <small class="text-muted">Поля: ФИО, email, телефон, адрес</small>
                                    </div>
                                </div>
                            </div> --}}

                            <!-- Модуль 4: Новости -->
                            {{-- <div class="col-md-6 col-lg-4 mb-3">
                                <div class="service-card" data-module="news" data-module-name="Новости">
                                    <div class="d-flex align-items-start mb-2">
                                        <div class="bg-warning-soft rounded-circle p-3 me-3">
                                            <i class="fas fa-newspaper fa-2x text-warning"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Новости</h6>
                                            <span class="badge bg-warning">Контент</span>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        Публикация новостей, статей, блога
                                    </p>
                                    <div class="mt-2">
                                        <small class="text-muted">Поля: заголовок, текст, дата, автор</small>
                                    </div>
                                </div>
                            </div> --}}

                            <!-- Модуль 5: Обратная связь -->
                            {{-- <div class="col-md-6 col-lg-4 mb-3">
                                <div class="service-card" data-module="feedback" data-module-name="Обратная связь">
                                    <div class="d-flex align-items-start mb-2">
                                        <div class="bg-danger-soft rounded-circle p-3 me-3">
                                            <i class="fas fa-comments fa-2x text-danger"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Обратная связь</h6>
                                            <span class="badge bg-danger">Формы</span>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        Формы обратной связи, заявки, сообщения
                                    </p>
                                    <div class="mt-2">
                                        <small class="text-muted">Поля: имя, сообщение, email, тема</small>
                                    </div>
                                </div>
                            </div> --}}
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
                            <button type="button" class="btn btn-primary" id="nextToStep4">
                                Далее: Сопоставление полей <i class="fas fa-arrow-right ms-2"></i>
                            </button>
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
                        <p class="text-muted small mb-0 mt-1">Сопоставьте поля внешнего сервиса с полями модуля</p>
                    </div>
                    <div class="card-body">
                        <!-- Информация о выбранных сервисе и модуле -->
                        <div class="alert alert-info mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Внешний сервис:</strong>
                                    <div id="currentServiceInfo" class="mt-1"></div>
                                </div>
                                <div class="col-md-6">
                                    <strong>Внутренний модуль:</strong>
                                    <div id="currentModuleInfo" class="mt-1"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Сопоставление полей -->
                        <div class="field-mapping-container">
                            <div class="field-mapping-header bg-light p-3 rounded-top">
                                <div class="row">
                                    <div class="col-md-5">
                                        <strong>Поле внешнего сервиса</strong>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <strong>→</strong>
                                    </div>
                                    <div class="col-md-5">
                                        <strong>Поле внутреннего модуля</strong>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="fieldMappingRows" class="rounded-bottom">
                                <!-- Поля будут динамически добавляться -->
                            </div>
                        </div>

                        <!-- Дополнительные настройки -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-tools me-2"></i>Дополнительные настройки</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="ignore_missing" checked>
                                    <label class="form-check-label" for="ignore_missing">
                                        Игнорировать отсутствующие поля
                                    </label>
                                </div>
                            </div>
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
        </form>
    </div>

    <!-- Модальное окно тестирования -->
    <div class="modal fade" id="testModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-vial me-2"></i>Тестирование интеграции</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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