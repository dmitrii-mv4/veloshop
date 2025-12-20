@extends('admin::layouts.default')

@section('title', 'Редактирование роли | KotiksCMS')

@section('content')

    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        <!-- Подключаем breadcrumb -->
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['url' => route('admin.roles'), 'title' => 'Роли'],
                ['title' => 'Редактирование роли']
            ],
        ])
    </div>

    <!-- Действия с ролями -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Редактирование роли</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Настройте разрешения для роли "{{ $role->name }}"
            </p>
        </div>
        <div class="d-flex gap-2">
            @if(!$role->is_system && $role->id != 1)
                <button type="button" class="btn btn-outline-danger delete-role-btn"
                        data-role-id="{{ $role->id }}"
                        data-role-name="{{ $role->name }}"
                        data-delete-url="{{ route('admin.roles.delete', $role) }}"
                        data-bs-toggle="modal" 
                        data-bs-target="#deleteRoleModal">
                    <i class="bi bi-trash"></i> Удалить
                </button>
            @endif
            <a href="{{ route('admin.roles') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад к списку
            </a>
        </div>
    </div>

    <!-- Форма редактирования роли -->
    <div class="row fade-in">
        <div class="col-12">
            <div class="card">
                <form action="{{ route('admin.roles.update', $role) }}" method="POST" id="role-edit-form"
                      data-role-id="{{ $role->id }}" 
                      data-is-system="{{ $role->is_system ? 'true' : 'false' }}">
                    @csrf
                    @method('PATCH')
                    
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Основная информация</h5>
                            @if($role->is_system)
                                <span class="badge bg-warning">
                                    <i class="bi bi-gear me-1"></i> Системная роль
                                </span>
                            @endif
                            @if($role->id == 1)
                                <span class="badge bg-danger">
                                    <i class="bi bi-shield-lock me-1"></i> Защищённая роль
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <!-- Название роли -->
                        <div class="mb-4">
                            <label for="name" class="form-label">Название роли 
                                @if(!$role->is_system)<span class="text-danger">*</span>@endif
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $role->name) }}" 
                                   placeholder="Введите название роли" 
                                   @if($role->is_system || $role->id == 1) readonly @else required @endif>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                @if($role->id == 1)
                                    <span class="text-danger">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Роль "Администратор" защищена от изменений
                                    </span>
                                @elseif($role->is_system)
                                    <span class="text-warning">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Название системной роли нельзя изменить
                                    </span>
                                @else
                                    Уникальное название роли, которое будет отображаться в системе
                                @endif
                            </div>
                        </div>
                        
                        <!-- Статистика роли -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body py-2">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3"
                                                style="width: 36px; height: 36px;">
                                                <i class="bi bi-people"></i>
                                            </div>
                                            <div>
                                                <div class="text-muted small">Пользователей с этой ролью</div>
                                                <div class="fw-semibold">{{ $role->users_count ?? $role->users()->count() }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body py-2">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center me-3"
                                                style="width: 36px; height: 36px;">
                                                <i class="bi bi-shield-check"></i>
                                            </div>
                                            <div>
                                                <div class="text-muted small">Настроено разрешений</div>
                                                <div class="fw-semibold">{{ $role->permissions_count ?? $role->permissions()->count() }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Разрешения -->
                    <div class="card-header border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-0">Разрешения роли</h5>
                                <p class="text-muted mb-0" style="font-size: 0.85rem;">
                                    Выберите разрешения, которые будут доступны пользователям с этой ролью
                                </p>
                            </div>
                            @if($role->id == 1)
                                <span class="badge bg-danger">
                                    <i class="bi bi-eye me-1"></i> Только просмотр
                                </span>
                            @elseif($role->is_system && $role->id == 3)
                                <span class="badge bg-info">Все разрешения доступны для редактирования</span>
                            @endif
                        </div>
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
                                            @php
                                                $selectedCount = 0;
                                                foreach($moduleData['permissions'] as $permission) {
                                                    if($role->permissions->contains('id', $permission->id)) {
                                                        $selectedCount++;
                                                    }
                                                }
                                            @endphp
                                            @if($selectedCount > 0)
                                                <span class="badge bg-primary ms-1">{{ $selectedCount }}</span>
                                            @endif
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
                                    @if(count($moduleData['permissions']) > 1 && !$isAdminRole)
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
                                            @php
                                                // Для роли Администратор (id=1) все разрешения заблокированы
                                                // Для роли Пользователь (id=3) все разрешения доступны
                                                // Для других системных ролей некоторые разрешения обязательны
                                                if ($role->id == 1) {
                                                    $isDisabled = true;
                                                    $isRequired = true;
                                                } elseif ($role->id == 3) {
                                                    $isDisabled = false;
                                                    $isRequired = false;
                                                } else {
                                                    $isRequired = $role->is_system && in_array($permission->name, [
                                                        'show_admin', 'users_viewAny', 'roles_viewAny'
                                                    ]);
                                                    $isDisabled = $isRequired;
                                                }
                                            @endphp
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="form-check card card-border {{ $isDisabled ? 'border-warning' : '' }}">
                                                    <div class="card-body p-3">
                                                        <input class="form-check-input permission-checkbox" 
                                                               type="checkbox" 
                                                               name="{{ $permission->name }}" 
                                                               value="1" 
                                                               id="perm-{{ $permission->id }}"
                                                               data-module="{{ $module }}"
                                                               {{ $role->permissions->contains('id', $permission->id) ? 'checked' : '' }}
                                                               {{ $isDisabled ? 'disabled' : '' }}>
                                                        <label class="form-check-label w-100" for="perm-{{ $permission->id }}">
                                                            <div class="d-flex justify-content-between align-items-start">
                                                                <div class="fw-semibold">{{ $permission->title }}</div>
                                                                @if($isRequired)
                                                                    <span class="badge bg-warning ms-2">Обязательно</span>
                                                                @endif
                                                            </div>
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
                                Назад
                            </a>
                            @if($isAdminRole)
                                <div class="alert alert-warning mb-0 py-2">
                                    <i class="bi bi-shield-lock me-2"></i>
                                    Роль "Администратор" защищена от изменений
                                </div>
                            @else
                                <div class="d-flex gap-2">
                                    <button type="reset" class="btn btn-outline-danger">
                                        <i class="bi bi-arrow-clockwise"></i> Сбросить
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Сохранить изменения
                                    </button>
                                </div>
                            @endif
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
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> О редактировании ролей</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li class="mb-2">Изменения вступают в силу немедленно для всех пользователей с этой ролью</li>
                        <li class="mb-2">Системные роли имеют обязательные разрешения, которые нельзя отключить</li>
                        <li class="mb-2">Рекомендуется тестировать изменения на тестовом пользователе</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-shield-check me-2"></i> Безопасность</h6>
                </div>
                <div class="card-body">
                    @if($isAdminRole)
                        <div class="alert alert-danger alert-sm mb-2">
                            <i class="bi bi-shield-lock me-2"></i>
                            <strong>Защищённая роль:</strong> Роль "Администратор" защищена от любых изменений
                        </div>
                    @elseif($role->is_system && $role->id == 3)
                        <div class="alert alert-info alert-sm mb-2">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Роль "Пользователь":</strong> Все разрешения доступны для настройки
                        </div>
                    @elseif($role->is_system)
                        <div class="alert alert-warning alert-sm mb-2">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Системная роль:</strong> некоторые настройки защищены от изменений
                        </div>
                    @endif
                    <div class="alert alert-info alert-sm mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Убедитесь, что изменения соответствуют политике безопасности организации
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

<!-- Модальное окно удаления роли -->
@if(!$role->is_system && $role->id != 1)
<div class="modal fade" id="deleteRoleModal" tabindex="-1" aria-labelledby="deleteRoleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteRoleModalLabel">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить роль <strong id="roleNameToDelete"></strong>?</p>
                <div class="alert alert-warning alert-sm mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Внимание:</strong> это действие нельзя отменить
                </div>
                <div class="alert alert-danger alert-sm mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Все разрешения роли будут удалены. Убедитесь, что с этой ролью не связано ни одного пользователя.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form id="deleteRoleForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i> Удалить
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
