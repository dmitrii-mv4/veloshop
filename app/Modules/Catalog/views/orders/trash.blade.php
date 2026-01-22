@extends('admin::layouts.default')

@section('title', 'Корзина заказов | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Каталог', 'url' => route('catalog.index')],
                ['title' => 'Заказы', 'url' => route('catalog.orders.index')],
                ['title' => 'Корзина']
            ],
        ])
    </div>

    <!-- Вкладки: Активные и Корзина -->
    <div class="d-flex mb-4 fade-in">
        <div class="btn-group" role="group">
            <a href="{{ route('catalog.orders.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-bag-check me-1"></i> Активные заказы
            </a>
            <a href="{{ route('catalog.orders.trash.index') }}" class="btn btn-primary position-relative">
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

    <!-- Действия с корзиной -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Корзина заказов</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Всего заказов: {{ $totalOrders }} | В корзине: {{ $trashedOrders }}
            </p>
        </div>
        @if($trashedOrders > 0)
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#emptyTrashModal">
            <i class="bi bi-trash"></i> Очистить корзину
        </button>
        @endif
    </div>

    @if($trashedOrders > 0)
    <!-- Карточка с фильтрами -->
    <div class="card fade-in mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('catalog.orders.trash.index') }}" class="row g-2">
                <!-- Поиск в корзине -->
                <div class="col-md-9">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                            placeholder="Поиск по номеру заказа или комментарию...">
                    </div>
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
                <div class="col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-funnel me-1"></i> Применить
                    </button>
                    <a href="{{ route('catalog.orders.trash.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Список заказов в корзине -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Заказы в корзине</h5>
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
                            <th width="25%">Номер заказа</th>
                            <th width="15%">Покупатель</th>
                            <th width="10%">Сумма</th>
                            <th width="15%">Удалено</th>
                            <th width="15%">Кем удалено</th>
                            <th width="20%" class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr class="opacity-75">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center me-3"
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
                                            <span class="small">{{ $order->customer->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted small">Не указан</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ number_format($order->total_amount, 2, '.', ' ') }}</span>
                                </td>
                                <td>
                                    <div class="text-muted small">
                                        {{ $order->deleted_at->format('d.m.Y H:i') }}
                                        <div class="text-muted">
                                            {{ $order->deleted_at->diffForHumans() }}
                                        </div>
                                        @php
                                            $daysAgo = $order->deleted_at->diffInDays(now());
                                            $daysLeft = max(0, 30 - $daysAgo);
                                        @endphp
                                        <div class="text-danger x-small">
                                            @if($daysLeft > 0)
                                                Осталось {{ $daysLeft }} {{ trans_choice('день|дня|дней', $daysLeft) }}
                                            @else
                                                Будет удален сегодня
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($order->deleter)
                                        <div class="d-flex align-items-center">
                                            <span class="small">{{ $order->deleter->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted small">Система</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="table-actions justify-content-end">
                                        <form action="{{ route('catalog.orders.trash.restore', $order->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success btn-sm me-1" 
                                                    title="Восстановить">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-outline-danger btn-sm force-delete-btn"
                                                title="Удалить навсегда" data-order-id="{{ $order->id }}"
                                                data-order-number="{{ $order->order_number }}"
                                                data-force-url="{{ route('catalog.orders.trash.force', $order->id) }}"
                                                data-bs-toggle="modal" data-bs-target="#forceDeleteModal">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
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
    @else
    <!-- Пустая корзина -->
    <div class="card fade-in">
        <div class="card-body text-center py-5">
            <div class="text-muted">
                <i class="bi bi-trash fs-1 opacity-50"></i>
                <h4 class="mt-3">Корзина пуста</h4>
                <p class="mb-4">Удаленные заказы будут отображаться здесь</p>
                <a href="{{ route('catalog.orders.index') }}" class="btn btn-primary">
                    <i class="bi bi-arrow-left me-2"></i> Вернуться к заказам
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Информационная панель -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> О корзине</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2" style="font-size: 0.85rem;">
                        В этом разделе вы можете управлять всеми удалёнными заказами.
                    </p>
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li>Заказы, которые находятся в корзине, не отображаются в основном списке</li>
                        <li>Перемещённые заказы в корзину удаляются автоматически через 30 дней</li>
                        <li>После полного удаления заказ нельзя восстановить</li>
                        <li>Номера удаленных заказов могут быть использованы для новых заказов</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card api-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0"><i class="bi bi-code-slash me-2"></i> Статистика</h6>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <div class="fw-semibold mb-1" style="font-size: 0.85rem;">
                                <i class="bi bi-bag-check me-1"></i> Всего заказов
                            </div>
                            <h4 class="mb-0">{{ $totalOrders }}</h4>
                        </div>
                        <div>
                            <div class="fw-semibold mb-1" style="font-size: 0.85rem;">
                                <i class="bi bi-trash me-1"></i> В корзине
                            </div>
                            <h4 class="mb-0">{{ $trashedOrders }}</h4>
                        </div>
                    </div>
                    
                    <!-- Автоматическая очистка -->
                    <div class="alert alert-info alert-sm mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        <small>Заказы автоматически удаляются из корзины через 30 дней</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<!-- Модальное окно полного удаления -->
<div class="modal fade" id="forceDeleteModal" tabindex="-1" aria-labelledby="forceDeleteModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="forceDeleteModalLabel">Полное удаление заказа</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите полностью удалить заказ <strong id="orderNumberToForceDelete"></strong>?</p>
                <div class="alert alert-danger alert-sm mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Это действие нельзя отменить. Все данные заказа будут удалены безвозвратно.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form id="forceDeleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i> Удалить навсегда
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно очистки корзины -->
<div class="modal fade" id="emptyTrashModal" tabindex="-1" aria-labelledby="emptyTrashModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emptyTrashModalLabel">Очистка корзины заказов</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите очистить всю корзину заказов?</p>
                <div class="alert alert-danger alert-sm mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Это действие удалит <strong>{{ $trashedOrders }}</strong> заказов безвозвратно. 
                    Отменить это действие будет невозможно.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form action="{{ route('catalog.orders.trash.empty') }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i> Очистить корзину
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Обработка кнопок полного удаления
        const forceDeleteButtons = document.querySelectorAll('.force-delete-btn');
        forceDeleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order-id');
                const orderNumber = this.getAttribute('data-order-number');
                const forceUrl = this.getAttribute('data-force-url');
                
                document.getElementById('orderNumberToForceDelete').textContent = orderNumber;
                document.getElementById('forceDeleteForm').action = forceUrl;
            });
        });
    });
</script>
@endpush