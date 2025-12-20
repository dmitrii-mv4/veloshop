@extends('admin::layouts.default')

@section('title', 'Управление ролями | KotiksCMS')

@section('content')

    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        <!-- Подключаем breadcrumb -->
        @include('admin::partials.breadcrumb', [
            'items' => [['title' => 'Роли']],
        ])
    </div>

    <!-- Действия с ролями -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Управление ролями</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Всего: {{ $totalRoles }} | Системных: {{ $systemRoles }} | Пользовательских: {{ $customRoles }}
            </p>
        </div>
        @if (auth()->user()->hasPermission('roles_create'))
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Добавить роль
            </a>
        @endif
    </div>

    <!-- Карточка с фильтрами -->
    <div class="card fade-in mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.roles') }}" class="row g-2">
                <!-- Поиск -->
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control"
                            placeholder="Поиск по названию роли...">
                    </div>
                </div>

                <!-- Фильтр по типу -->
                <div class="col-md-3">
                    <select name="type" class="form-select form-select-sm">
                        <option value="all" {{ ($type ?? 'all') == 'all' ? 'selected' : '' }}>Все типы</option>
                        <option value="system" {{ ($type ?? '') == 'system' ? 'selected' : '' }}>Системные</option>
                        <option value="custom" {{ ($type ?? '') == 'custom' ? 'selected' : '' }}>Пользовательские</option>
                    </select>
                </div>

                <!-- Количество на странице -->
                <div class="col-md-3">
                    <select name="per_page" class="form-select form-select-sm">
                        @foreach ([10, 25, 50, 100] as $count)
                            <option value="{{ $count }}" {{ ($perPage ?? 10) == $count ? 'selected' : '' }}>
                                {{ $count }} на странице
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Кнопки фильтрации -->
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-funnel me-1"></i> Применить
                    </button>
                    <a href="{{ route('admin.roles') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Список ролей -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Список ролей</h5>
                <div class="text-muted small">
                    @if($roles instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        Показано {{ $roles->count() }} из {{ $roles->total() }} ролей
                    @else
                        Показано {{ $roles->count() }} ролей
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="30%">
                                <a href="{{ route('admin.roles', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'name', 'sort_order' => ($sortBy ?? 'name') == 'name' && ($sortOrder ?? 'asc') == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none d-flex align-items-center">
                                    Название роли
                                    @if (($sortBy ?? 'name') == 'name')
                                        <i class="bi bi-chevron-{{ ($sortOrder ?? 'asc') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="20%">
                                <a href="{{ route('admin.roles', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'users_count', 'sort_order' => ($sortBy ?? '') == 'users_count' && ($sortOrder ?? 'asc') == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none d-flex align-items-center">
                                    Пользователей
                                    @if (($sortBy ?? '') == 'users_count')
                                        <i class="bi bi-chevron-{{ ($sortOrder ?? 'asc') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="20%">Разрешений</th>
                            <th width="15%">Тип</th>
                            <th width="15%" class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            @php
                                $userCount = $users->where('role_id', $role->id)->count();
                                $permissionCount = $role->permissions->count() ?? 0;
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3"
                                            style="width: 36px; height: 36px;">
                                            <i class="bi bi-person-badge" style="font-size: 1rem;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $role->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 me-2">
                                            {{ $userCount }}
                                        </span>
                                        @if($userCount > 0)
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" 
                                                data-bs-toggle="popover" 
                                                data-bs-html="true"
                                                data-bs-content="
                                                    <div class='small'>
                                                        @foreach($users->where('role_id', $role->id)->take(5) as $user)
                                                            <div class='d-flex align-items-center mb-1'>
                                                                <div class='me-2'>{{ $user->name }}</div>
                                                                <small class='text-muted'>{{ $user->email }}</small>
                                                            </div>
                                                        @endforeach
                                                        @if($userCount > 5)
                                                            <div class='text-muted'>... и ещё {{ $userCount - 5 }}</div>
                                                        @endif
                                                    </div>
                                                "
                                                data-bs-title="Пользователи с этой ролью">
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                        {{ $permissionCount }} разрешений
                                    </span>
                                    @if($permissionCount > 0)
                                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none ms-2" 
                                            data-bs-toggle="popover" 
                                            data-bs-html="true"
                                            data-bs-content="
                                                <div class='small'>
                                                    @foreach($role->permissions->take(5) as $permission)
                                                        <div class='mb-1'>• {{ $permission->title ?? $permission->name }}</div>
                                                    @endforeach
                                                    @if($permissionCount > 5)
                                                        <div class='text-muted'>... и ещё {{ $permissionCount - 5 }}</div>
                                                    @endif
                                                </div>
                                            "
                                            data-bs-title="Разрешения роли">
                                        </button>
                                    @endif
                                </td>
                                <td>
                                    @if($role->is_system)
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                                            <i class="bi bi-gear me-1"></i> Системная
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                            <i class="bi bi-person-plus me-1"></i> Пользовательская
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="table-actions justify-content-end">
                                        @if (auth()->user()->hasPermission('roles_update'))
                                            <a href="{{ route('admin.roles.edit', $role) }}"
                                                class="btn btn-outline-primary btn-sm me-1" title="Редактировать">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif

                                        @if (auth()->user()->hasPermission('roles_delete') && !$role->is_system)
                                            <button type="button" class="btn btn-outline-danger btn-sm delete-role-btn"
                                                title="Удалить" data-role-id="{{ $role->id }}"
                                                data-role-name="{{ $role->name }}"
                                                data-delete-url="{{ route('admin.roles.delete', $role) }}"
                                                data-bs-toggle="modal" data-bs-target="#deleteRoleModal">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @elseif($role->is_system)
                                            <button type="button" class="btn btn-outline-danger btn-sm disabled"
                                                title="Системную роль нельзя удалить">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-person-badge fs-4"></i>
                                        <p class="mt-2">Роли не найдены</p>
                                        @if (request()->hasAny(['search', 'type']))
                                            <a href="{{ route('admin.roles') }}" class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-arrow-clockwise me-1"></i> Сбросить фильтры
                                            </a>
                                        @else
                                            <a href="{{ route('admin.roles.create') }}"
                                                class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-plus-circle me-1"></i> Добавить первую роль
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Пагинация -->
        @if($roles instanceof \Illuminate\Pagination\LengthAwarePaginator && $roles->hasPages())
            <div class="card-footer border-0 bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Показано {{ $roles->firstItem() }} - {{ $roles->lastItem() }} из {{ $roles->total() }} ролей
                    </div>
                    <div>
                        {{ $roles->links('admin::partials.pagination') }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Информационная панель -->
    <div class="row mt-4 fade-in">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> О ролях в системе</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2" style="font-size: 0.85rem;">
                        Роли определяют уровень доступа пользователей к различным функциям системы.
                    </p>
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li><strong>Системные роли</strong> создаются автоматически и не могут быть удалены</li>
                        <li><strong>Пользовательские роли</strong> создаются администратором для конкретных задач</li>
                        <li>Каждой роли назначаются определённые разрешения (permissions)</li>
                        <li>Одному пользователю можно назначить только одну роль</li>
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
                    <div class="alert alert-warning alert-sm mb-2">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Системные роли</strong> защищены от удаления и изменений
                    </div>
                    <div class="alert alert-info alert-sm mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Удаление роли не затрагивает пользователей — им будет назначена роль по умолчанию
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

<!-- Модальное окно подтверждения удаления -->
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
                    <strong>Внимание:</strong> пользователи с этой ролью будут переведены на роль по умолчанию
                </div>
                <div class="alert alert-danger alert-sm mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Это действие нельзя отменить. Все разрешения роли будут удалены.
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
