@extends('admin::layouts.default')

@section('title', 'Интеграции с внешними сервисами | KotiksCMS')

@section('content')

    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        
        <!-- Подключаем breadcrumb -->
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Интеграции с внешними сервисами']
            ]
        ])
    </div>

    <!-- Действия с интеграциями -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Управление интеграциями</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Всего: {{ $totalIntegrations }} | Активных: {{ $activeIntegrations }} | Неактивных: {{ $inactiveIntegrations }}
            </p>
        </div>
        <a href="{{ route('admin.integration.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Добавить интеграцию
        </a>
    </div>

    <!-- Карточка с фильтрами -->
    <div class="card fade-in mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.integration.index') }}" class="row g-2">
                <!-- Поиск -->
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                            placeholder="Поиск по названию или описанию...">
                    </div>
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
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-funnel me-1"></i> Применить фильтры
                    </button>
                    <a href="{{ route('admin.integration.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Список интеграций -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Список интеграций</h5>
                <div class="text-muted small">
                    Показано {{ $integrations->count() }} из {{ $integrations->total() }} интеграций
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="25%">
                                <a href="{{ route('admin.integration.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'name', 'sort_order' => $sortBy == 'name' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none d-flex align-items-center">
                                    Название интеграции
                                    @if ($sortBy == 'name')
                                        <i class="bi bi-chevron-{{ $sortOrder == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="10%">Модуль</th>
                            <th width="10%">Тип сервиса</th>
                            <th width="10%">Статус</th>
                            <th width="15%">Последнее обновление</th>
                            <th width="10%">Дата создания</th>
                            <th width="20%" class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($integrations as $integration)
                            <tr>
                                <td>
                                    <div class="integration-name">{{ $integration->name }}</div>
                                    <div class="integration-description text-muted small">
                                        {{ \Illuminate\Support\Str::limit($integration->integration_description, 50) }}
                                    </div>
                                </td>
                                <td>
                                    <!-- Заглушка для модуля -->
                                    <span class="badge badge-violet" style="font-size: 0.85rem;">
                                        Каталог
                                    </span>
                                </td>
                                <td>
                                    <!-- Заглушка для типа сервиса -->
                                    <span class="badge badge-blue">
                                        Telegram
                                    </span>
                                </td>
                                <td>
                                    @if ($integration->is_active)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                            <i class="bi bi-check-circle me-1"></i> Активна
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                            <i class="bi bi-x-circle me-1"></i> Неактивна
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted" style="font-size: 0.85rem;">
                                        {{ $integration->updated_at->format('d.m.Y H:i') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted" style="font-size: 0.85rem;">
                                        {{ $integration->created_at->format('d.m.Y H:i') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions justify-content-end">
                                        <!-- Кнопка перехода (заглушка) -->
                                        <a href="#"
                                            class="btn btn-outline-primary btn-sm me-1"
                                            title="Перейти">
                                            <i class="bi bi-box-arrow-in-right"></i>
                                        </a>
                                        <!-- Кнопка удаления (заглушка) -->
                                        <button type="button" class="btn btn-outline-danger btn-sm" title="Удалить">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox fs-4"></i>
                                        <p class="mt-2">Интеграции не найдены</p>
                                        @if (request()->hasAny(['search', 'status']))
                                            <a href="{{ route('admin.integration.index') }}" class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-arrow-clockwise me-1"></i> Сбросить фильтры
                                            </a>
                                        @else
                                            <a href="{{ route('admin.integration.create') }}" class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-plus-circle me-1"></i> Добавить первую интеграцию
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
        @if ($integrations->hasPages())
            <div class="card-footer border-0 bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Показано {{ $integrations->firstItem() }} - {{ $integrations->lastItem() }} из {{ $integrations->total() }} интеграций
                    </div>
                    <div>
                        {{ $integrations->links('admin::partials.pagination') }}
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
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i>О интеграциях</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2" style="font-size: 0.85rem;">Интеграции позволяют подключить ваш сайт к внешним сервисам через API. Вы можете настроить автоматический обмен данными, синхронизацию и другие взаимодействия. Каждая интеграция имеет уникальные настройки и может быть активирована или деактивирована в любое время.</p>
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li>Создавайте новые интеграции</li>
                        <li>Управляйте существующими интеграциями</li>
                        <li>Активируйте и деактивируйте по необходимости</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно подтверждения удаления (пример для интеграции 1) -->
    <div class="modal fade" id="deleteModal1" tabindex="-1" aria-labelledby="deleteModalLabel1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Подтверждение удаления
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body py-4">
                    <p>Вы уверены, что хотите удалить интеграцию <strong>"Telegram Bot API"</strong>?</p>
                    <p class="text-muted small mb-0">Это действие нельзя будет отменить. Все настройки и данные этой интеграции будут удалены.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete1">Удалить</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно подтверждения удаления (пример для интеграции 2) -->
    <div class="modal fade" id="deleteModal2" tabindex="-1" aria-labelledby="deleteModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Подтверждение удаления
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body py-4">
                    <p>Вы уверены, что хотите удалить интеграцию <strong>"Email рассылка"</strong>?</p>
                    <p class="text-muted small mb-0">Это действие нельзя будет отменить. Все настройки и данные этой интеграции будут удалены.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete2">Удалить</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно подтверждения удаления (пример для интеграции 3) -->
    <div class="modal fade" id="deleteModal3" tabindex="-1" aria-labelledby="deleteModalLabel3" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Подтверждение удаления
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body py-4">
                    <p>Вы уверены, что хотите удалить интеграцию <strong>"Платежная система"</strong>?</p>
                    <p class="text-muted small mb-0">Это действие нельзя будет отменить. Все настройки и данные этой интеграции будут удалены.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete3">Удалить</button>
                </div>
            </div>
        </div>
    </div>

@endsection