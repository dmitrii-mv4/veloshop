@extends('admin::layouts.default')

@section('title', 'Управление заказами | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [['title' => 'Каталог'], ['title' => 'Заказы']],
        ])
    </div>

    <!-- Вкладки: Активные и Корзина -->
    <div class="d-flex mb-4 fade-in">
        <div class="btn-group" role="group">
            <a href="{{ route('catalog.orders.index') }}" class="btn btn-primary">
                <i class="bi bi-bag-check me-1"></i> Активные заказы
            </a>
            <a href="{{ route('catalog.orders.trash.index') }}" class="btn btn-outline-primary position-relative">
                <i class="bi bi-trash me-1"></i> Корзина
                @if($trashedOrders > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ $trashedOrders }}
                    <span class="visually-hidden">заказов в корзине</span>
                </span>
                @endif
            </a>
        </div>
    </div>

    <!-- Действия с заказами -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Управление заказами</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Всего: {{ $totalOrders }} | В корзине: {{ $trashedOrders }}
            </p>
        </div>
        <a href="{{ route('catalog.orders.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Добавить заказ
        </a>
    </div>

    <!-- Карточка с фильтрами -->
    <div class="card fade-in mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('catalog.orders.index') }}" class="row g-2">
                <!-- Поиск -->
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                            placeholder="Поиск по номеру заказа или комментарию...">
                    </div>
                </div>

                <!-- Сортировка -->
                <div class="col-md-3">
                    <select name="sort_by" class="form-select form-select-sm">
                        <option value="created_at" {{ $sortBy == 'created_at' ? 'selected' : '' }}>Дата добавления</option>
                        <option value="order_number" {{ $sortBy == 'order_number' ? 'selected' : '' }}>Номер заказа</option>
                        <option value="total_amount" {{ $sortBy == 'total_amount' ? 'selected' : '' }}>Сумма заказа</option>
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
                <div class="col-md-1">
                    <select name="per_page" class="form-select form-select-sm">
                        @foreach ([10, 25, 50, 100] as $count)
                            <option value="{{ $count }}" {{ $perPage == $count ? 'selected' : '' }}>
                                {{ $count }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Кнопки фильтрации -->
                <div class="col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-funnel me-1"></i>
                    </button>
                    <a href="{{ route('catalog.orders.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Список заказов -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Список заказов</h5>
                <div class="text-muted small">
                    Показано {{ $orders->count() }} из {{ $orders->total() }} заказов
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="15%">
                                <a href="{{ route('catalog.orders.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'order_number', 'sort_order' => $sortBy == 'order_number' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none d-flex align-items-center">
                                    Номер заказа
                                    @if ($sortBy == 'order_number')
                                        <i class="bi bi-chevron-{{ $sortOrder == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="15%">Покупатель</th>
                            <th width="10%">Сумма</th>
                            <th width="10%">Статус</th>
                            <th width="15%">Ответственный</th>
                            <th width="10%">Создан</th>
                            <th width="10%">Обновлен</th>
                            <th width="15%" class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3"
                                            style="width: 40px; height: 40px;">
                                            <i class="bi bi-bag-check"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $order->order_number }}</div>
                                            <div class="text-muted small">ID: {{ $order->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($order->customer)
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center me-2"
                                                style="width: 24px; height: 24px; font-size: 0.75rem;">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <div>
                                                <div class="small fw-semibold">{{ $order->customer->name }}</div>
                                                <div class="text-muted x-small">ID: {{ $order->customer_id }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge bg-light text-dark">
                                            <i class="bi bi-person-x me-1"></i> Не указан
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ number_format($order->total_amount, 2, '.', ' ') }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $order->status_color }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td>
                                    @if($order->responsible)
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center me-2"
                                                style="width: 24px; height: 24px; font-size: 0.75rem;">
                                                <i class="bi bi-person-badge"></i>
                                            </div>
                                            <span class="small">{{ $order->responsible->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted small">Не назначен</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="small" title="{{ $order->created_at }}">
                                        {{ $order->created_at->format('d.m.Y') }}
                                    </span>
                                    <div class="text-muted x-small">{{ $order->created_at->format('H:i') }}</div>
                                </td>
                                <td>
                                    <span class="small" title="{{ $order->updated_at }}">
                                        {{ $order->updated_at->format('d.m.Y') }}
                                    </span>
                                    <div class="text-muted x-small">{{ $order->updated_at->format('H:i') }}</div>
                                </td>
                                <td>
                                    <div class="table-actions justify-content-end">
                                        <a href="{{ route('catalog.orders.edit', $order) }}"
                                            class="btn btn-outline-primary btn-sm me-1" title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm delete-order-btn"
                                            title="В корзину" data-order-id="{{ $order->id }}"
                                            data-order-number="{{ $order->order_number }}"
                                            data-delete-url="{{ route('catalog.orders.destroy', $order) }}"
                                            data-bs-toggle="modal" data-bs-target="#deleteOrderModal">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-bag-check fs-4"></i>
                                        <p class="mt-2">Заказы не найдены</p>
                                        @if (request()->has('search'))
                                            <a href="{{ route('catalog.orders.index') }}" class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-arrow-clockwise me-1"></i> Сбросить фильтры
                                            </a>
                                        @else
                                            <a href="{{ route('catalog.orders.create') }}"
                                                class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-plus-circle me-1"></i> Добавить первый заказ
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
        @if ($orders->hasPages())
            <div class="card-footer border-0 bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Показано {{ $orders->firstItem() }} - {{ $orders->lastItem() }} из {{ $orders->total() }}
                    </div>
                    <div>
                        {{ $orders->links('admin::partials.pagination') }}
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
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> О заказах</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2" style="font-size: 0.85rem;">
                        В этом разделе вы можете управлять заказами в системе.
                    </p>
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li>Создавайте и редактируйте заказы</li>
                        <li>Назначайте ответственных за заказы</li>
                        <li>Отслеживайте статусы оплаты и проблем</li>
                        <li>Корзина хранит удаленные заказы 30 дней</li>
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
                    <!-- API заказов -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-grow-1">
                            <div class="fw-semibold mb-1" style="font-size: 0.85rem;">
                                <i class="bi bi-link-45deg me-1"></i> API заказов
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <code class="p-2 bg-light rounded small api-endpoint flex-grow-1" title="{{ url('api/catalog/orders') }}">
                                    {{ url('api/catalog/orders') }}
                                </code>
                                <div class="d-flex gap-1">
                                    <a href="{{ url('api/catalog/orders') }}" target="_blank" 
                                       class="btn btn-outline-primary btn-sm copy-btn" 
                                       title="Открыть API в новой вкладке">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    <button class="btn btn-outline-secondary btn-sm copy-btn" 
                                            data-clipboard-text="{{ url('api/catalog/orders') }}"
                                            title="Копировать URL API">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<!-- Модальное окно подтверждения удаления в корзину -->
<div class="modal fade" id="deleteOrderModal" tabindex="-1" aria-labelledby="deleteOrderModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteOrderModalLabel">Перемещение в корзину</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите переместить заказ <strong id="orderNumberToDelete"></strong> в корзину?</p>
                <div class="alert alert-info alert-sm mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Заказ будет доступен в корзине для восстановления в течение 30 дней
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form id="deleteOrderForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i> В корзину
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Обработка кнопок удаления
        const deleteButtons = document.querySelectorAll('.delete-order-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order-id');
                const orderNumber = this.getAttribute('data-order-number');
                const deleteUrl = this.getAttribute('data-delete-url');
                
                document.getElementById('orderNumberToDelete').textContent = orderNumber;
                document.getElementById('deleteOrderForm').action = deleteUrl;
            });
        });
    });
</script>
@endpush