@extends('admin::layouts.default')

@section('title', 'Управление меню | KotiksCMS')

@section('content')

    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [['title' => 'Меню']],
        ])
    </div>

    <!-- Действия с меню -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Управление меню</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Всего: {{ $menus->total() }} | Активных: {{ $activeMenus }} | Неактивных: {{ $inactiveMenus }}
            </p>
        </div>
        <div class="menu-block-actions">
            <a href="{{ route('admin.menu.types.index') }}" class="btn btn-primary menu-block-actions-type-btn">
                <i class="bi bi-plus-circle"></i> Типы меню
            </a>
            <a href="{{ route('admin.menu.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Создать меню
            </a>
        </div>
        
    </div>

    <!-- Карточка с фильтрами -->
    <div class="card fade-in mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.menu.index') }}" class="row g-2">
                <!-- Поиск -->
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                            placeholder="Поиск по названию, описанию или типу...">
                    </div>
                </div>

                <!-- Фильтр по типу -->
                <div class="col-md-2">
                    <select name="menu_type_id" class="form-select form-select-sm">
                        <option value="all" {{ $menuTypeId == 'all' ? 'selected' : '' }}>Все типы</option>
                        @foreach($menuTypes as $type)
                            <option value="{{ $type->id }}" {{ $menuTypeId == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Фильтр по статусу -->
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Все статусы</option>
                        <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Активные</option>
                        <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Неактивные</option>
                    </select>
                </div>

                <!-- Сортировка -->
                <div class="col-md-2">
                    <select name="sort_by" class="form-select form-select-sm">
                        <option value="created_at" {{ $sortBy == 'created_at' ? 'selected' : '' }}>Дата создания</option>
                        <option value="name" {{ $sortBy == 'name' ? 'selected' : '' }}>Название</option>
                        <option value="updated_at" {{ $sortBy == 'updated_at' ? 'selected' : '' }}>Дата обновления</option>
                    </select>
                </div>

                <!-- Количество на странице -->
                <div class="col-md-2">
                    <select name="per_page" class="form-select form-select-sm">
                        @foreach ([10, 25, 50, 100] as $count)
                            <option value="{{ $count }}" {{ $perPage == $count ? 'selected' : '' }}>
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
                    <a href="{{ route('admin.menu.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Список меню -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Список меню</h5>
                <div class="text-muted small">
                    Показано {{ $menus->count() }} из {{ $menus->total() }} меню
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="30%">
                                <a href="{{ route('admin.menu.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'name', 'sort_order' => $sortBy == 'name' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none d-flex align-items-center">
                                    Название меню
                                    @if ($sortBy == 'name')
                                        <i class="bi bi-chevron-{{ $sortOrder == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="15%">Тип</th>
                            <th width="25%">Описание</th>
                            <th width="15%">Статус</th>
                            <th width="15%">Обновлено</th>
                            <th width="15%" class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($menus as $menu)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3"
                                            style="width: 40px; height: 40px;">
                                            <i class="bi bi-menu-button-wide" style="font-size: 1rem;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $menu->name }}</div>
                                            <div class="text-muted small">
                                                ID: {{ $menu->id }} | Пунктов: {{ $menu->items_count ?? 0 }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($menu->menuType)
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                            {{ $menu->menuType->name }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                            Не указан
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($menu->description)
                                        <div class="text-muted small">
                                            {{ Str::limit($menu->description, 80) }}
                                        </div>
                                    @else
                                        <span class="text-muted small">Без описания</span>
                                    @endif
                                </td>
                                <td>
                                    @if($menu->is_active)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                            <i class="bi bi-check-circle me-1"></i> Активно
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                            <i class="bi bi-x-circle me-1"></i> Не активно
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-muted small">
                                        {{ $menu->updated_at->format('d.m.Y H:i') }}
                                        @if($menu->updater)
                                            <br>{{ $menu->updater->name }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="table-actions justify-content-end">
                                        <a href="{{ route('admin.menu.items.index', $menu) }}"
                                            class="btn btn-outline-success btn-sm me-1" title="Пункты меню">
                                            <i class="bi bi-list-ul"></i>
                                        </a>
                                        <a href="{{ route('admin.menu.edit', $menu) }}"
                                            class="btn btn-outline-primary btn-sm me-1" title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <!-- Форма удаления меню -->
                                        <form action="{{ route('admin.menu.destroy', $menu) }}" 
                                            method="POST" 
                                            class="d-inline"
                                            onsubmit="return confirm('Вы уверены, что хотите удалить меню «{{ $menu->name }}»?\n\nВнимание! При удалении меню будут удалены все его пункты (всего: {{ $menu->items_count ?? 0 }}). Это действие нельзя отменить.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Удалить">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-menu-button-wide fs-4"></i>
                                        <p class="mt-2">Меню не найдены</p>
                                        @if (request()->hasAny(['search', 'type', 'status']))
                                            <a href="{{ route('admin.menu.index') }}" class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-arrow-clockwise me-1"></i> Сбросить фильтры
                                            </a>
                                        @else
                                            <a href="{{ route('admin.menu.create') }}"
                                                class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-plus-circle me-1"></i> Создать первое меню
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
        @if ($menus->hasPages())
            <div class="card-footer border-0 bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Показано {{ $menus->firstItem() }} - {{ $menus->lastItem() }} из {{ $menus->total() }}
                    </div>
                    <div>
                        {{ $menus->links('admin::partials.pagination') }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Информационная панель -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> О меню</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2" style="font-size: 0.85rem;">
                        В этом разделе вы можете управлять типами меню вашего сайта.
                    </p>
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li>Создавайте меню для разных областей сайта (верхнее, нижнее, боковое)</li>
                        <li>Каждое меню может содержать древовидную структуру пунктов</li>
                        <li>Настраивайте активность меню для отображения на сайте</li>
                        <li>Удаление меню приведет к удалению всех его пунктов</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card api-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0"><i class="bi bi-code-slash me-2"></i> API</h6>
                    </div>
                </div>
                <div class="card-body">
                    <!-- API меню -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-grow-1">
                            <div class="fw-semibold mb-1" style="font-size: 0.85rem;">
                                <i class="bi bi-link-45deg me-1"></i> API меню
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <code class="p-2 bg-light rounded small api-endpoint flex-grow-1" title="{{ url('api/menus') }}">
                                    {{ url('api/menus') }}
                                </code>
                                <div class="d-flex gap-1">
                                    <a href="{{ url('api/menus') }}" target="_blank" 
                                       class="btn btn-outline-primary btn-sm copy-btn" 
                                       title="Открыть API в новой вкладке">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Документация API -->
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="fw-semibold mb-1" style="font-size: 0.85rem;">
                                <i class="bi bi-book me-1"></i> Документация
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="api-endpoint flex-grow-1" title="{{ url('api/documentation') }}">
                                    {{ url('api/documentation') }}
                                </span>
                                <div class="d-flex gap-1">
                                    <a href="{{ url('api/documentation') }}" target="_blank" 
                                       class="btn btn-outline-info btn-sm copy-btn" 
                                       title="Открыть документацию в новой вкладке">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modals')
    <!-- Модальное окно подтверждения удаления -->
    <div class="modal fade" id="deleteMenuModal" tabindex="-1" aria-labelledby="deleteMenuModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteMenuModalLabel">Удаление меню</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Вы уверены, что хотите удалить меню <strong id="menuNameToDelete"></strong>?</p>
                    <div class="alert alert-danger alert-sm mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Внимание! При удалении меню будут удалены все его пункты. Это действие нельзя отменить.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <form id="deleteMenuForm" method="POST" class="d-inline">
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
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Обработка кнопок удаления меню
            const deleteButtons = document.querySelectorAll('.delete-menu-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const menuId = this.getAttribute('data-menu-id');
                    const menuName = this.getAttribute('data-menu-name');
                    const deleteUrl = this.getAttribute('data-delete-url');
                    
                    document.getElementById('menuNameToDelete').textContent = menuName;
                    document.getElementById('deleteMenuForm').action = deleteUrl;
                });
            });
        });
    </script>
@endsection