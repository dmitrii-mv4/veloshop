@extends('admin::layouts.default')

@section('title', 'Управление пользователями | KotiksCMS')

@section('content')

    <!-- Заголовок страницы -->
    <div class="page-header fade-in">

        <!-- Подключаем breadcrumb -->
        @include('admin::partials.breadcrumb', [
            'items' => [['title' => 'Пользователи']],
        ])
    </div>

    <!-- Действия с пользователями -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Управление пользователями</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Всего: {{ $totalUsers }} | Активных: {{ $activeUsers }} | Неактивных: {{ $inactiveUsers }}
            </p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Добавить пользователя
        </a>
    </div>

    <!-- Карточка с фильтрами -->
    <div class="card fade-in mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.users') }}" class="row g-2">
                <!-- Поиск -->
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                            placeholder="Поиск по имени, email или телефону...">
                    </div>
                </div>

                <!-- Фильтр по роли -->
                <div class="col-md-2">
                    <select name="role_id" class="form-select form-select-sm">
                        <option value="all" {{ $roleId == 'all' ? 'selected' : '' }}>Все роли</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" {{ $roleId == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Фильтр по статусу -->
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Все статусы</option>
                        <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Только активные</option>
                        <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Только неактивные</option>
                    </select>
                </div>

                <!-- Количество на странице -->
                <div class="col-md-2">
                    <select name="per_page" class="form-select form-select-sm">
                        @foreach ([5, 10, 25, 50] as $count)
                            <option value="{{ $count }}" {{ $perPage == $count ? 'selected' : '' }}>
                                {{ $count }} на странице
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Кнопки фильтрации -->
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-funnel me-1"></i> Применить фильтры
                    </button>
                    <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Список пользователей -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Список пользователей</h5>
                <div class="text-muted small">
                    Показано {{ $users->count() }} из {{ $users->total() }} пользователей
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="30%">
                                <a href="{{ route('admin.users', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'name', 'sort_order' => $sortBy == 'name' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none d-flex align-items-center">
                                    Имя пользователя
                                    @if ($sortBy == 'name')
                                        <i class="bi bi-chevron-{{ $sortOrder == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="25%">
                                <a href="{{ route('admin.users', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'email', 'sort_order' => $sortBy == 'email' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none d-flex align-items-center">
                                    Email
                                    @if ($sortBy == 'email')
                                        <i class="bi bi-chevron-{{ $sortOrder == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="15%">Роль</th>
                            <th width="15%">Статус</th>
                            <th width="15%" class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if ($user->avatar)
                                            <img src="{{ url(Storage::url($user->avatar)) }}" alt="{{ $user->name }}"
                                                class="rounded-circle me-3"
                                                style="width: 36px; height: 36px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center me-3"
                                                style="width: 36px; height: 36px;">
                                                <i class="bi bi-person" style="font-size: 1rem;"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $user->name }}</div>
                                            <div class="text-muted small">
                                                @if ($user->position)
                                                    {{ $user->position }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="mb-1">
                                        <a href="mailto:{{ $user->email }}" class="text-decoration-none">
                                            {{ $user->email }}
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    @if ($user->role)
                                        <span
                                            class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                            {{ $user->role->name }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Не назначена</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($user->is_active)
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                            <i class="bi bi-check-circle me-1"></i> Активен
                                        </span>
                                    @else
                                        <span
                                            class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                            <i class="bi bi-x-circle me-1"></i> Неактивен
                                        </span>
                                    @endif

                                    @if ($user->is_system)
                                        <div class="mt-1">
                                            <span
                                                class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                                                <i class="bi bi-gear me-1"></i> Системный
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="table-actions justify-content-end">
                                        @if (auth()->user()->hasPermission('users_update'))
                                            <a href="{{ route('admin.users.edit', $user) }}"
                                                class="btn btn-outline-primary btn-sm me-1" title="Редактировать">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif

                                        @if (auth()->user()->hasPermission('users_delete') && !$user->is_system && $user->id !== auth()->id())
                                            <button type="button" class="btn btn-outline-danger btn-sm delete-user-btn"
                                                title="Удалить" data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->name }}"
                                                data-delete-url="{{ route('admin.users.destroy', $user) }}"
                                                data-bs-toggle="modal" data-bs-target="#deleteUserModal">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @elseif($user->is_system)
                                            <button type="button" class="btn btn-outline-danger btn-sm disabled"
                                                title="Системного пользователя нельзя удалить">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @elseif($user->id === auth()->id())
                                            <button type="button" class="btn btn-outline-danger btn-sm disabled"
                                                title="Нельзя удалить свой аккаунт">
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
                                        <i class="bi bi-people fs-4"></i>
                                        <p class="mt-2">Пользователи не найдены</p>
                                        @if (request()->hasAny(['search', 'role_id', 'status']))
                                            <a href="{{ route('admin.users') }}" class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-arrow-clockwise me-1"></i> Сбросить фильтры
                                            </a>
                                        @else
                                            <a href="{{ route('admin.users.create') }}"
                                                class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-plus-circle me-1"></i> Добавить первого пользователя
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
        @if ($users->hasPages())
            <div class="card-footer border-0 bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Показано {{ $users->firstItem() }} - {{ $users->lastItem() }} из {{ $users->total() }}
                        пользователей
                    </div>
                    <div>
                        {{ $users->links('admin::partials.pagination') }}
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
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> О пользователях системы</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2" style="font-size: 0.85rem;">
                        В этом разделе вы можете управлять всеми пользователями системы, их ролями и правами доступа.
                    </p>
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li>Добавляйте новых пользователей и назначайте им роли</li>
                        <li>Редактируйте существующие учетные записи</li>
                        <li>Управляйте активностью пользователей (активируйте/деактивируйте)</li>
                        <li>Фильтруйте пользователей по ролям и статусу</li>
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
                        <strong>Системные пользователи</strong> не могут быть удалены
                    </div>
                    <div class="alert alert-info alert-sm mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Вы не можете удалить свой собственный аккаунт
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

<!-- Модальное окно подтверждения удаления -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteUserModalLabel">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить пользователя <strong id="userNameToDelete"></strong>?</p>
                <div class="alert alert-danger alert-sm mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Это действие нельзя отменить. Все данные пользователя будут удалены.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form id="deleteUserForm" method="POST" class="d-inline">
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
