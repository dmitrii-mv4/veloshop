@extends('admin::layouts.default')

@section('title', 'Управление корзинами | KotiksCMS')

@section('content')

    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [['title' => 'Корзины']],
        ])
    </div>

    <!-- Действия -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Управление корзинами</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Всего: {{ $baskets->total() }} | Сумма всех корзин: {{ number_format($baskets->sum('total_price'), 2) }} руб.
            </p>
        </div>
        <a href="{{ route('catalog.basket.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Создать корзину
        </a>
    </div>

    <!-- Карточка с фильтрами -->
    <div class="card fade-in mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('catalog.basket.index') }}" class="row g-2">
                <!-- Поиск -->
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control"
                               placeholder="Поиск по имени пользователя...">
                    </div>
                </div>

                <!-- Фильтр по пользователю -->
                <div class="col-md-2">
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="all" {{ ($userId ?? 'all') == 'all' ? 'selected' : '' }}>Все пользователи</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ ($userId ?? '') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Фильтр по покупателю -->
                <div class="col-md-2">
                    <select name="customer_id" class="form-select form-select-sm">
                        <option value="all" {{ ($customerId ?? 'all') == 'all' ? 'selected' : '' }}>Все покупатели</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" {{ ($customerId ?? '') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->user->name ?? 'ID '.$customer->id }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Количество на странице -->
                <div class="col-md-2">
                    <select name="per_page" class="form-select form-select-sm">
                        @foreach ([10, 20, 50, 100] as $count)
                            <option value="{{ $count }}" {{ ($perPage ?? 20) == $count ? 'selected' : '' }}>
                                {{ $count }} на странице
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Кнопки -->
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-funnel me-1"></i> Применить
                    </button>
                    <a href="{{ route('catalog.basket.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Таблица корзин -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Список корзин</h5>
                <div class="text-muted small">
                    Показано {{ $baskets->count() }} из {{ $baskets->total() }} корзин
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="5%">
                                <a href="{{ route('catalog.basket.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'id', 'sort_order' => $sortBy == 'id' && $sortOrder == 'asc' ? 'desc' : 'asc']) ) }}"
                                   class="text-decoration-none d-flex align-items-center">
                                    ID
                                    @if ($sortBy == 'id')
                                        <i class="bi bi-chevron-{{ $sortOrder == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="20%">Пользователь</th>
                            <th width="15%">Покупатель</th>
                            <th width="10%">
                                <a href="{{ route('catalog.basket.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'total_quantity', 'sort_order' => $sortBy == 'total_quantity' && $sortOrder == 'asc' ? 'desc' : 'asc']) ) }}"
                                   class="text-decoration-none d-flex align-items-center">
                                    Кол-во
                                    @if ($sortBy == 'total_quantity')
                                        <i class="bi bi-chevron-{{ $sortOrder == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="15%">
                                <a href="{{ route('catalog.basket.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'total_price', 'sort_order' => $sortBy == 'total_price' && $sortOrder == 'asc' ? 'desc' : 'asc']) ) }}"
                                   class="text-decoration-none d-flex align-items-center">
                                    Сумма
                                    @if ($sortBy == 'total_price')
                                        <i class="bi bi-chevron-{{ $sortOrder == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="15%">Дата создания</th>
                            <th width="15%" class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($baskets as $basket)
                            <tr>
                                <td>#{{ $basket->id }}</td>
                                <td>
                                    @if ($basket->user)
                                        <div class="d-flex align-items-center">
                                            @if ($basket->user->avatar)
                                                <img src="{{ url(Storage::url($basket->user->avatar)) }}" alt="{{ $basket->user->name }}"
                                                     class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center me-2"
                                                     style="width: 32px; height: 32px;">
                                                    <i class="bi bi-person"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-semibold">{{ $basket->user->name }}</div>
                                                <div class="text-muted small">{{ $basket->user->email }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">Не указан</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($basket->customer && $basket->customer->user)
                                        {{ $basket->customer->user->name }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $basket->total_quantity }}</td>
                                <td>{{ number_format($basket->total_price, 2) }} ₽</td>
                                <td>{{ $basket->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    <div class="table-actions justify-content-end">
                                        <a href="{{ route('catalog.basket.edit', $basket->id) }}"
                                           class="btn btn-outline-primary btn-sm me-1" title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-danger btn-sm delete-basket-btn"
                                                title="Удалить"
                                                data-basket-id="{{ $basket->id }}"
                                                data-delete-url="{{ route('catalog.basket.destroy', $basket->id) }}"
                                                data-bs-toggle="modal" data-bs-target="#deleteBasketModal">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-cart fs-4"></i>
                                        <p class="mt-2">Корзины не найдены</p>
                                        @if (request()->hasAny(['search', 'user_id', 'customer_id']))
                                            <a href="{{ route('catalog.basket.index') }}" class="btn btn-primary btn-sm mt-2">
                                                Сбросить фильтры
                                            </a>
                                        @else
                                            <a href="{{ route('catalog.basket.create') }}" class="btn btn-primary btn-sm mt-2">
                                                Создать первую корзину
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
        @if ($baskets->hasPages())
            <div class="card-footer border-0 bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Показано {{ $baskets->firstItem() }} - {{ $baskets->lastItem() }} из {{ $baskets->total() }} корзин
                    </div>
                    <div>
                        {{ $baskets->links('admin::partials.pagination') }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Модальное окно подтверждения удаления -->
    <div class="modal fade" id="deleteBasketModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Подтверждение удаления</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Вы уверены, что хотите удалить корзину <strong id="basketIdToDelete"></strong>?</p>
                    <div class="alert alert-danger alert-sm mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Это действие нельзя отменить.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <form id="deleteBasketForm" method="POST" class="d-inline">
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
        const deleteModal = document.getElementById('deleteBasketModal');
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const deleteUrl = button.getAttribute('data-delete-url');
            const basketId = button.getAttribute('data-basket-id');
            
            document.getElementById('basketIdToDelete').textContent = '#' + basketId;
            document.getElementById('deleteBasketForm').action = deleteUrl;
        });
    });
</script>
@endpush