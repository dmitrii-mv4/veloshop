@extends('admin::layouts.default')

@section('title', 'Настройки сайта | KotiksCMS')

@section('content')

    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        <!-- Подключаем breadcrumb -->
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['url' => route('admin.dashboard'), 'title' => 'Главная'],
                ['title' => 'Настройки сайта']
            ],
        ])
    </div>

    <!-- Действия с настройками -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Настройки сайта</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Настройте основные параметры вашего сайта и админ-панели
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> На главную
            </a>
        </div>
    </div>

    <!-- Карточка с API-информацией -->
    <div class="row mb-4 fade-in">
        <div class="col-md-4">
            <a href="{{ $settings->full_url }}/api/app/site" target="_blank" class="card card-link">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3"
                            style="width: 48px; height: 48px;">
                            <i class="bi bi-code-slash" style="font-size: 1.25rem;"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">API сайта</div>
                            <div class="text-muted small">/api/app/site</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light py-2">
                    <div class="text-end">
                        <span class="badge bg-info">Открыть в новой вкладке</span>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center me-3"
                                    style="width: 36px; height: 36px;">
                                    <i class="bi bi-globe"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Сайт</div>
                                    <div class="fw-semibold">{{ $settings->name_site }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center me-3"
                                    style="width: 36px; height: 36px;">
                                    <i class="bi bi-translate"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Язык админки</div>
                                    <div class="fw-semibold">{{ app()->getLocale() == 'ru' ? 'Русский' : 'English' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Основная форма настроек -->
    <div class="row fade-in">
        <div class="col-lg-8">
            <div class="card">
                <form action="{{ route('admin.settings.update', $settings) }}" method="POST" id="settings-form">
                    @csrf
                    @method('PATCH')
                    
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Основные настройки</h5>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Сохранить изменения
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <!-- Название сайта -->
                        <div class="mb-4">
                            <label for="name_site" class="form-label">
                                Название сайта <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('name_site') is-invalid @enderror" 
                                   id="name_site" 
                                   name="name_site" 
                                   value="{{ old('name_site', $settings->name_site) }}" 
                                   placeholder="Введите название сайта" 
                                   required>
                            @error('name_site')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Название будет отображаться в заголовке страниц и в поисковых системах
                            </div>
                        </div>
                        
                        <!-- URL сайта -->
                        <div class="mb-4">
                            <label for="url_site" class="form-label">
                                URL сайта <span class="text-danger">*</span>
                            </label>
                            <input type="url" 
                                   class="form-control @error('url_site') is-invalid @enderror" 
                                   id="url_site" 
                                   name="url_site" 
                                   value="{{ old('url_site', $settings->url_site) }}" 
                                   placeholder="https://example.com" 
                                   required>
                            @error('url_site')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Основной домен вашего сайта (с протоколом http:// или https://)
                            </div>
                        </div>
                        
                        <!-- Описание сайта -->
                        <div class="mb-4">
                            <label for="description_site" class="form-label">Описание сайта</label>
                            <textarea class="form-control @error('description_site') is-invalid @enderror" 
                                      id="description_site" 
                                      name="description_site" 
                                      rows="3" 
                                      placeholder="Краткое описание вашего сайта">{{ old('description_site', $settings->description_site) }}</textarea>
                            @error('description_site')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Краткое описание, которое используется в мета-тегах и на страницах
                            </div>
                        </div>
                        
                        <!-- Язык админ-панели -->
                        <div class="mb-4">
                            <label for="lang_admin" class="form-label">
                                Язык админ-панели <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('lang_admin') is-invalid @enderror" 
                                    id="lang_admin" 
                                    name="lang_admin" 
                                    required>
                                <option value="ru" {{ app()->getLocale() == 'ru' ? 'selected' : '' }}>Русский</option>
                                <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>English</option>
                            </select>
                            @error('lang_admin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Язык интерфейса административной панели
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between">
                            <button type="reset" class="btn btn-outline-danger">
                                <i class="bi bi-arrow-clockwise"></i> Сбросить
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Сохранить изменения
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Боковая панель с информацией -->
        <div class="col-lg-4">
            <!-- Информация о системе -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i> Информация о системе
                    </h6>
                </div>

                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">PHP версия:</span>
                            <span class="fw-semibold">{{ $systemInfo['php_version'] }}</span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Laravel версия:</span>
                            <span class="fw-semibold">{{ $systemInfo['laravel_version'] }}</span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Сервер:</span>
                            <span class="fw-semibold">{{ $systemInfo['server_software'] }}</span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">База данных:</span>
                            <span class="fw-semibold">{{ $systemInfo['database_driver'] }}</span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Окружение:</span>
                            <span class="badge bg-{{ $systemInfo['environment'] == 'production' ? 'success' : 'warning' }}">
                                {{ $systemInfo['environment'] }}
                            </span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Режим отладки:</span>
                            <span class="badge bg-{{ $systemInfo['debug_mode'] == 'Включен' ? 'danger' : 'success' }}">
                                {{ $systemInfo['debug_mode'] }}
                            </span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Часовой пояс:</span>
                            <span class="fw-semibold">{{ $systemInfo['timezone'] }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Быстрые ссылки -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-lightning-charge me-2"></i> Быстрые действия
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.users') }}" class="btn btn-outline-primary">
                            <i class="bi bi-people me-1"></i> Управление пользователями
                        </a>
                        <a href="{{ route('admin.roles') }}" class="btn btn-outline-success">
                            <i class="bi bi-shield-check me-1"></i> Управление ролями
                        </a>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-info">
                            <i class="bi bi-speedometer2 me-1"></i> Панель управления
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Уведомления и подсказки -->
    <div class="row mt-4 fade-in">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-exclamation-triangle me-2"></i> Важно знать</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning alert-sm mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Внимание:</strong> Изменение языка админ-панели применяется немедленно
                    </div>
                    <div class="alert alert-info alert-sm mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        После сохранения настроек рекомендуется проверить отображение сайта
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-question-circle me-2"></i> Справка</h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <strong>Название сайта</strong> — используется в заголовках страниц и может влиять на SEO.
                    </p>
                    <p class="small mb-2">
                        <strong>URL сайта</strong> — основной адрес вашего сайта. Убедитесь, что он корректен.
                    </p>
                    <p class="small mb-0">
                        <strong>Язык админки</strong> — выбор языка интерфейса административной панели.
                    </p>
                </div>
            </div>
        </div>
    </div>

@endsection
