@extends('admin::layouts.default')

@section('title', 'Управление складами | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [['title' => 'Каталог'], ['title' => 'Склады']],
        ])
    </div>

    <!-- Вкладки: Товары, Предложения, Склады -->
    <div class="d-flex mb-4 fade-in">
        <div class="btn-group" role="group">
            <a href="{{ route('catalog.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-box-seam me-1"></i> Товары
            </a>
            <a href="{{ route('catalog.warehouses.index') }}" class="btn btn-primary">
                <i class="bi bi-house-door me-1"></i> Склады
            </a>
        </div>
    </div>

    <!-- Действия со складами -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Управление складами</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Всего складов: {{ $totalWarehouses }}
            </p>
        </div>
        <a href="{{ route('catalog.warehouses.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Добавить склад
        </a>
    </div>

    <!-- Карточка с фильтрами -->
    <div class="card fade-in mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('catalog.warehouses.index') }}" class="row g-2">
                <!-- Поиск -->
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                            placeholder="Поиск по названию, описанию или контактам...">
                    </div>
                </div>

                <!-- Фильтр по статусу -->
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Все статусы</option>
                        <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Активные</option>
                        <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Неактивные</option>
                    </select>
                </div>

                <!-- Сортировка -->
                <div class="col-md-2">
                    <select name="sort_by" class="form-select form-select-sm">
                        <option value="created_at" {{ $sortBy == 'created_at' ? 'selected' : '' }}>Дата создания</option>
                        <option value="title" {{ $sortBy == 'title' ? 'selected' : '' }}>Название</option>
                        <option value="updated_at" {{ $sortBy == 'updated_at' ? 'selected' : '' }}>Дата обновления</option>
                    </select>
                </div>

                <!-- Порядок сортировки -->
                <div class="col-md-2">
                    <select name="sort_order" class="form-select form-select-sm">
                        <option value="desc" {{ $sortOrder == 'desc' ? 'selected' : '' }}>По убыванию</option>
                        <option value="asc" {{ $sortOrder == 'asc' ? 'selected' : '' }}>По возрастанию</option>
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
                    <a href="{{ route('catalog.warehouses.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Список складов -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Список складов</h5>
                <div class="text-muted small">
                    Показано {{ $warehouses->count() }} из {{ $warehouses->total() }} складов
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="30%">
                                <a href="{{ route('catalog.warehouses.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'title', 'sort_order' => $sortBy == 'title' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none d-flex align-items-center">
                                    Название склада
                                    @if ($sortBy == 'title')
                                        <i class="bi bi-chevron-{{ $sortOrder == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="15%">Статус</th>
                            <th width="20%">Контакты</th>
                            <th width="15%">Товаров на складе</th>
                            <th width="10%">Обновлено</th>
                            <th width="20%" class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($warehouses as $warehouse)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3"
                                            style="width: 40px; height: 40px;">
                                            <i class="bi bi-house-door" style="font-size: 1rem;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $warehouse->title }}</div>
                                            @if($warehouse->description)
                                                <div class="text-muted small text-truncate" style="max-width: 200px;">
                                                    {{ Str::limit($warehouse->description, 60) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <form action="{{ route('catalog.warehouses.toggle-status', $warehouse) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $warehouse->is_active ? 'btn-success' : 'btn-secondary' }}">
                                            <i class="bi bi-{{ $warehouse->is_active ? 'check-circle' : 'x-circle' }} me-1"></i>
                                            {{ $warehouse->is_active ? 'Активен' : 'Неактивен' }}
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    @if($warehouse->contacts)
                                        <div class="text-muted small">
                                            {{ Str::limit($warehouse->contacts, 40) }}
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold">{{ $warehouse->unique_offers_count }} позиций</span>
                                        <span class="text-muted small">{{ $warehouse->total_products_count }} единиц</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-muted small">
                                        {{ $warehouse->updated_at->format('d.m.Y H:i') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="table-actions justify-content-end">
                                        <a href="{{ route('catalog.warehouses.edit', $warehouse) }}"
                                            class="btn btn-outline-primary btn-sm me-1" title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm delete-warehouse-btn"
                                            title="Удалить" data-warehouse-id="{{ $warehouse->id }}"
                                            data-warehouse-title="{{ $warehouse->title }}"
                                            data-delete-url="{{ route('catalog.warehouses.destroy', $warehouse) }}"
                                            data-bs-toggle="modal" data-bs-target="#deleteWarehouseModal">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-house-door fs-4"></i>
                                        <p class="mt-2">Склады не найдены</p>
                                        @if (request()->hasAny(['search', 'status']))
                                            <a href="{{ route('catalog.warehouses.index') }}" class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-arrow-clockwise me-1"></i> Сбросить фильтры
                                            </a>
                                        @else
                                            <a href="{{ route('catalog.warehouses.create') }}"
                                                class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-plus-circle me-1"></i> Добавить первый склад
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
        @if ($warehouses->hasPages())
            <div class="card-footer border-0 bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Показано {{ $warehouses->firstItem() }} - {{ $warehouses->lastItem() }} из {{ $warehouses->total() }}
                    </div>
                    <div>
                        {{ $warehouses->links('admin::partials.pagination') }}
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
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> О складах</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2" style="font-size: 0.85rem;">
                        В этом разделе вы можете управлять всеми складами вашего магазина.
                    </p>
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li>Создавайте и редактируйте информацию о складах</li>
                        <li>Управляйте статусом активности складов</li>
                        <li>Отслеживайте количество товаров на каждом складе</li>
                        <li>Настраивайте контактную информацию для каждого склада</li>
                        <li>Быстро переключайте статус склада между активным и неактивным</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-bar-chart me-2"></i> Статистика по складам</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="fs-4 fw-bold text-primary">
                                    {{ $totalWarehouses }}
                                </div>
                                <div class="text-muted small">Всего складов</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="fs-4 fw-bold text-success">
                                    {{ $warehouses->where('is_active', true)->count() }}
                                </div>
                                <div class="text-muted small">Активных складов</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<!-- Модальное окно подтверждения удаления -->
<div class="modal fade" id="deleteWarehouseModal" tabindex="-1" aria-labelledby="deleteWarehouseModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteWarehouseModalLabel">Удаление склада</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить склад <strong id="warehouseTitleToDelete"></strong>?</p>
                <div class="alert alert-danger alert-sm mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Внимание! Это действие невозможно отменить. Все данные о количестве товаров на этом складе будут удалены.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form id="deleteWarehouseForm" method="POST" class="d-inline">
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Обработка удаления склада
        const deleteButtons = document.querySelectorAll('.delete-warehouse-btn');
        const deleteForm = document.getElementById('deleteWarehouseForm');
        const warehouseTitleSpan = document.getElementById('warehouseTitleToDelete');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const warehouseTitle = this.getAttribute('data-warehouse-title');
                const deleteUrl = this.getAttribute('data-delete-url');

                warehouseTitleSpan.textContent = warehouseTitle;
                deleteForm.action = deleteUrl;
            });
        });

        // Подтверждение изменения статуса
        const statusButtons = document.querySelectorAll('form[action*="toggle-status"] button');
        statusButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const form = this.closest('form');
                const isActive = this.textContent.includes('Активен');
                const action = isActive ? 'деактивировать' : 'активировать';

                if (!confirm(`Вы уверены, что хотите ${action} этот склад?`)) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
@endpush