@extends('admin::layouts.default')

@section('title', 'Создание новой роли | KotiksCMS')

@section('content')

    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        <!-- Подключаем breadcrumb -->
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['url' => route('admin.roles'), 'title' => 'Роли'],
                ['title' => 'Создание роли']
            ],
        ])
    </div>

    <!-- Действия с ролями -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Создание новой роли</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Настройте разрешения для новой роли пользователей
            </p>
        </div>
        <div>
            <a href="{{ route('admin.roles') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад к списку
            </a>
        </div>
    </div>

    <!-- Форма создания роли -->
    <div class="row fade-in">
        <div class="col-12">
            <div class="card">
                <form action="{{ route('admin.roles.store') }}" method="POST">
                    @csrf
                    
                    <div class="card-header">
                        <h5 class="card-title mb-0">Основная информация</h5>
                    </div>
                    
                    <div class="card-body">
                        <!-- Название роли -->
                        <div class="mb-4">
                            <label for="name" class="form-label">Название роли <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   placeholder="Введите название роли" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Уникальное название роли, которое будет отображаться в системе
                            </div>
                        </div>
                    </div>
                    
                    <!-- Разрешения -->
                    <div class="card-header border-top">
                        <h5 class="card-title mb-0">Разрешения роли</h5>
                        <p class="text-muted mb-0" style="font-size: 0.85rem;">
                            Выберите разрешения, которые будут доступны пользователям с этой ролью
                        </p>
                    </div>
                    
                    <div class="card-body">
                        <!-- Навигация по разделам разрешений -->
                        <div class="mb-4">
                            <ul class="nav nav-tabs" id="permissionsTab" role="tablist">
                                @php
                                    $moduleIndex = 0;
                                    $firstModule = null;
                                @endphp
                                
                                @foreach($groupedPermissions as $module => $moduleData)
                                    @if($moduleIndex === 0)
                                        @php $firstModule = $module; @endphp
                                    @endif
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link @if($moduleIndex === 0) active @endif" 
                                                id="{{ $module }}-tab" 
                                                data-bs-toggle="tab" 
                                                data-bs-target="#{{ $module }}-pane" 
                                                type="button" 
                                                role="tab">
                                            {{ $moduleData['title'] }}
                                        </button>
                                    </li>
                                    @php $moduleIndex++; @endphp
                                @endforeach
                            </ul>
                        </div>
                        
                        <!-- Содержимое вкладок разрешений -->
                        <div class="tab-content" id="permissionsTabContent">
                            @php $moduleIndex = 0; @endphp
                            
                            @foreach($groupedPermissions as $module => $moduleData)
                                <div class="tab-pane fade @if($moduleIndex === 0) show active @endif" 
                                     id="{{ $module }}-pane" 
                                     role="tabpanel" 
                                     tabindex="0">
                                    
                                    <!-- Быстрый выбор для модуля -->
                                    @if(count($moduleData['permissions']) > 1)
                                        <div class="mb-3">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-primary select-all-module" 
                                                        data-module="{{ $module }}">
                                                    Выбрать все
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary deselect-all-module" 
                                                        data-module="{{ $module }}">
                                                    Снять все
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Список разрешений модуля -->
                                    <div class="row">
                                        @foreach($moduleData['permissions'] as $permission)
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="form-check card card-border">
                                                    <div class="card-body p-3">
                                                        <input class="form-check-input permission-checkbox" 
                                                               type="checkbox" 
                                                               name="{{ $permission->name }}" 
                                                               value="1" 
                                                               id="perm-{{ $permission->id }}"
                                                               data-module="{{ $module }}">
                                                        <label class="form-check-label w-100" for="perm-{{ $permission->id }}">
                                                            <div class="fw-semibold">{{ $permission->title }}</div>
                                                            <div class="text-muted small mt-1">
                                                                <code class="text-xs">{{ $permission->name }}</code>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @php $moduleIndex++; @endphp
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.roles') }}" class="btn btn-outline-secondary">
                                Отмена
                            </a>
                            <div class="d-flex gap-2">
                                <button type="reset" class="btn btn-outline-danger">
                                    <i class="bi bi-arrow-clockwise"></i> Сбросить
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Создать роль
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Информационная панель -->
    <div class="row mt-4 fade-in">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> О создании ролей</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li class="mb-2">Роли определяют уровень доступа пользователей к функциям системы</li>
                        <li class="mb-2">Каждой роли можно назначить набор разрешений (permissions)</li>
                        <li class="mb-2">Пользователи получают доступ к функциям в зависимости от своей роли</li>
                        <li>Рекомендуется создавать роли в соответствии с должностными обязанностями</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-shield-check me-2"></i> Рекомендации по безопасности</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info alert-sm mb-2">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Принцип минимальных привилегий:</strong> назначайте только необходимые разрешения
                    </div>
                    <div class="alert alert-warning alert-sm mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Административные права</strong> выдавайте только доверенным пользователям
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
.card-border {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    transition: border-color 0.15s ease-in-out;
}

.card-border:hover {
    border-color: #0d6efd;
}

.form-check-input:checked + .form-check-label .card-border {
    border-color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.05);
}

.nav-tabs .nav-link {
    font-weight: 500;
    color: #6c757d;
    border-bottom: 2px solid transparent;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    border-bottom-color: #0d6efd;
}

.permission-checkbox {
    width: 1.2em;
    height: 1.2em;
    margin-top: 0.2em;
}

.form-check-label {
    cursor: pointer;
}

/* Адаптивность */
@media (max-width: 768px) {
    .page-actions {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .page-actions .btn {
        width: 100%;
    }
    
    .card-footer .d-flex {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .card-footer .btn {
        width: 100%;
    }
}

@media (max-width: 576px) {
    .nav-tabs {
        flex-wrap: nowrap;
        overflow-x: auto;
        overflow-y: hidden;
    }
    
    .nav-tabs .nav-item {
        white-space: nowrap;
    }
    
    .row .col-md-6 {
        margin-bottom: 0.5rem;
    }
}
</style>
@endpush