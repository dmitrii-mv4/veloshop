@extends('admin::layouts.default')

@section('title', 'Управление товарами | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [['title' => 'Каталог'], ['title' => 'Товары']],
        ])
    </div>

    <!-- Вкладки: Активные и Корзина -->
    <div class="d-flex mb-4 fade-in">
        <div class="btn-group" role="group">
            <a href="{{ route('catalog.goods.index') }}" class="btn btn-primary">
                <i class="bi bi-box-seam me-1"></i> Активные товары
            </a>
            <a href="{{ route('catalog.goods.trash.index') }}" class="btn btn-outline-primary position-relative">
                <i class="bi bi-trash me-1"></i> Корзина
                @if($trashedGoods > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ $trashedGoods }}
                    <span class="visually-hidden">товаров в корзине</span>
                </span>
                @endif
            </a>
        </div>
    </div>

    <!-- Действия с товарами -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Управление товарами</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Всего: {{ $totalGoods }} | В корзине: {{ $trashedGoods }}
            </p>
        </div>
        <a href="{{ route('catalog.goods.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Добавить товар
        </a>
    </div>

    <!-- Карточка с фильтрами -->
    <div class="card fade-in mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('catalog.goods.index') }}" class="row g-2">
                <!-- Поиск -->
                <div class="col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                            placeholder="Поиск по названию или артикулу...">
                    </div>
                </div>

                <!-- Сортировка -->
                <div class="col-md-3">
                    <select name="sort_by" class="form-select form-select-sm">
                        <option value="created_at" {{ $sortBy == 'created_at' ? 'selected' : '' }}>Дата добавления</option>
                        <option value="title" {{ $sortBy == 'title' ? 'selected' : '' }}>Название</option>
                        <option value="articul" {{ $sortBy == 'articul' ? 'selected' : '' }}>Артикул</option>
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
                <div class="col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-funnel me-1"></i>
                    </button>
                    <a href="{{ route('catalog.goods.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Список товаров -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Список товаров</h5>
                <div class="text-muted small">
                    Показано {{ $goods->count() }} из {{ $goods->total() }} товаров
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="30%">
                                <a href="{{ route('catalog.goods.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'title', 'sort_order' => $sortBy == 'title' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none d-flex align-items-center">
                                    Название товара
                                    @if ($sortBy == 'title')
                                        <i class="bi bi-chevron-{{ $sortOrder == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="10%">Артикул</th>
                            <th width="15%">Раздел</th>
                            <th width="10%">Добавил</th>
                            <th width="10%">Обновлен</th>
                            <th width="10%">Добавлен</th>
                            <th width="15%" class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($goods as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3"
                                            style="width: 40px; height: 40px;">
                                            <i class="bi bi-box-seam"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $item->title }}</div>
                                            <div class="text-muted small">ID: {{ $item->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code class="small">{{ $item->articul }}</code>
                                </td>
                                <td>
                                    @if($item->section)
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center me-2"
                                                style="width: 24px; height: 24px; font-size: 0.75rem;">
                                                <i class="bi bi-folder"></i>
                                            </div>
                                            <div>
                                                <div class="small fw-semibold">{{ $item->section->name }}</div>
                                                <div class="text-muted x-small">ID: {{ $item->section_id }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge bg-light text-dark">
                                            <i class="bi bi-folder-x me-1"></i> Без раздела
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->author)
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-2"
                                                style="width: 24px; height: 24px; font-size: 0.75rem;">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <span class="small">{{ $item->author->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted small">Не указан</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="small" title="{{ $item->updated_at }}">
                                        {{ $item->updated_at->format('d.m.Y H:i') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="small" title="{{ $item->created_at }}">
                                        {{ $item->created_at->format('d.m.Y H:i') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions justify-content-end">
                                        <a href="{{ route('catalog.goods.edit', $item) }}"
                                            class="btn btn-outline-primary btn-sm me-1" title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm delete-goods-btn"
                                            title="В корзину" data-goods-id="{{ $item->id }}"
                                            data-goods-title="{{ $item->title }}"
                                            data-delete-url="{{ route('catalog.goods.destroy', $item) }}"
                                            data-bs-toggle="modal" data-bs-target="#deleteGoodsModal">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-box-seam fs-4"></i>
                                        <p class="mt-2">Товары не найдены</p>
                                        @if (request()->has('search'))
                                            <a href="{{ route('catalog.goods.index') }}" class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-arrow-clockwise me-1"></i> Сбросить фильтры
                                            </a>
                                        @else
                                            <a href="{{ route('catalog.goods.create') }}"
                                                class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-plus-circle me-1"></i> Добавить первый товар
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
        @if ($goods->hasPages())
            <div class="card-footer border-0 bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Показано {{ $goods->firstItem() }} - {{ $goods->lastItem() }} из {{ $goods->total() }}
                    </div>
                    <div>
                        {{ $goods->links('admin::partials.pagination') }}
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
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> О товарах</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2" style="font-size: 0.85rem;">
                        В этом разделе вы можете управлять товарами в каталоге.
                    </p>
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li>Создавайте и редактируйте товары</li>
                        <li>Используйте артикулы для идентификации товаров</li>
                        <li>Корзина хранит удаленные товары 30 дней</li>
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
                    <!-- API товаров -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-grow-1">
                            <div class="fw-semibold mb-1" style="font-size: 0.85rem;">
                                <i class="bi bi-link-45deg me-1"></i> API товаров
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <code class="p-2 bg-light rounded small api-endpoint flex-grow-1" title="{{ url('api/catalog/goods') }}">
                                    {{ url('api/catalog/goods') }}
                                </code>
                                <div class="d-flex gap-1">
                                    <a href="{{ url('api/catalog/goods') }}" target="_blank" 
                                       class="btn btn-outline-primary btn-sm copy-btn" 
                                       title="Открыть API в новой вкладке">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    <button class="btn btn-outline-secondary btn-sm copy-btn" 
                                            data-clipboard-text="{{ url('api/catalog/goods') }}"
                                            title="Копировать URL API">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
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
                                    <button class="btn btn-outline-secondary btn-sm copy-btn" 
                                            data-clipboard-text="{{ url('api/documentation') }}"
                                            title="Копировать URL документации">
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
<div class="modal fade" id="deleteGoodsModal" tabindex="-1" aria-labelledby="deleteGoodsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteGoodsModalLabel">Перемещение в корзину</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите переместить товар <strong id="goodsTitleToDelete"></strong> в корзину?</p>
                <div class="alert alert-info alert-sm mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Товар будет доступен в корзине для восстановления в течение 30 дней
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form id="deleteGoodsForm" method="POST" class="d-inline">
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
        const deleteButtons = document.querySelectorAll('.delete-goods-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const goodsId = this.getAttribute('data-goods-id');
                const goodsTitle = this.getAttribute('data-goods-title');
                const deleteUrl = this.getAttribute('data-delete-url');
                
                document.getElementById('goodsTitleToDelete').textContent = goodsTitle;
                document.getElementById('deleteGoodsForm').action = deleteUrl;
            });
        });
    });
</script>
@endpush