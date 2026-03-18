@extends('admin::layouts.default')

@section('title', 'Управление товарами | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [['title' => 'Товары']],
        ])
    </div>

    <!-- Вкладки: Товары, Предложения, Склады -->
    <div class="d-flex mb-4 fade-in">
        <div class="btn-group" role="group">
            <a href="{{ route('catalog.index') }}" class="btn btn-primary">
                <i class="bi bi-box-seam me-1"></i> Товары
            </a>
            <a href="{{ route('catalog.warehouses.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-house-door me-1"></i> Склады
            </a>
            {{-- <a href="{{ route('catalog.statistics') }}" class="btn btn-outline-primary">
                <i class="bi bi-graph-up me-1"></i> Статистика
            </a> --}}
        </div>
    </div>

    <!-- Действия с товарами -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Управление товарами</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Всего товаров: <strong>{{ $totalProducts }}</strong> | Всего товарных предложений: <strong>{{ $totalOffers }}</strong>
            </p>
        </div>
        <a href="{{ route('catalog.products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Добавить товар
        </a>
    </div>

    <!-- Карточка с фильтрами -->
    <div class="card fade-in mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('catalog.index') }}" class="row g-2">
                <!-- Поиск -->
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                            placeholder="Поиск по названию, бренду, модели или ID...">
                    </div>
                </div>

                <!-- Сортировка -->
                <div class="col-md-2">
                    <select name="sort_by" class="form-select form-select-sm">
                        <option value="created_at" {{ $sortBy == 'created_at' ? 'selected' : '' }}>Дата создания</option>
                        <option value="name" {{ $sortBy == 'name' ? 'selected' : '' }}>Наименование</option>
                        <option value="brand" {{ $sortBy == 'brand' ? 'selected' : '' }}>Бренд</option>
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
                    <a href="{{ route('catalog.index') }}" class="btn btn-outline-secondary btn-sm">
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
                    Показано {{ $products->count() }} из {{ $products->total() }} товаров
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>
                                <a href="{{ route('catalog.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'name', 'sort_order' => $sortBy == 'name' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none d-flex align-items-center">
                                    Наименование
                                    @if ($sortBy == 'name')
                                        <i class="bi bi-chevron-{{ $sortOrder == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>ID из 1С</th>
                            <th>Бренд</th>
                            <th>Модель</th>
                            <th>Сезон</th>
                            <th>Обновлено</th>
                            <th class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>{{ $product->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <div class="fw-semibold">{{ $product->name }}</div>
                                            <div class="text-muted small">
                                                @if($product->proup_name)
                                                    Группа: {{ $product->proup_name }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code class="small">{{ $product->product_id }}</code>
                                </td>
                                <td>
                                    @if($product->brand)
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                            {{ $product->brand }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->model)
                                        {{ $product->model }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->seazon)
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                            {{ $product->seazon }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-muted small">
                                        {{ $product->updated_at->format('d.m.Y H:i') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('catalog.products.edit', $product) }}"
                                            class="btn btn-outline-primary btn-sm me-1" title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="{{ route('catalog.products.offers.index', $product) }}"
                                            class="btn btn-outline-success btn-sm me-1" title="Предложения">
                                            <i class="bi bi-list-check"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm delete-product-btn"
                                            title="Удалить" data-product-id="{{ $product->id }}"
                                            data-product-name="{{ $product->name }}"
                                            data-delete-url="{{ route('catalog.products.destroy', $product) }}"
                                            data-bs-toggle="modal" data-bs-target="#deleteProductModal">
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
                                        @if (request()->hasAny(['search']))
                                            <a href="{{ route('catalog.index') }}" class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-arrow-clockwise me-1"></i> Сбросить фильтры
                                            </a>
                                        @else
                                            <a href="{{ route('catalog.products.create') }}"
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
        @if ($products->hasPages())
            <div class="card-footer border-0 bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Показано {{ $products->firstItem() }} - {{ $products->lastItem() }} из {{ $products->total() }}
                    </div>
                    <div>
                        {{ $products->links('admin::partials.pagination') }}
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
                        В этом разделе вы можете управлять всем ассортиментом товаров вашего магазина.
                    </p>
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li>Создавайте и редактируйте карточки товаров</li>
                        <li>Управляйте брендами, моделями и сезонами товаров</li>
                        <li>Добавляйте различные варианты товаров (предложения)</li>
                        <li>Настраивайте SEO-параметры для каждого товара</li>
                        <li>Отслеживайте наличие товаров на складах</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card api-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0"><i class="bi bi-code-slash me-2"></i> API Каталога</h6>
                    </div>
                </div>
                <div class="card-body">
                    <!-- API товаров -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-grow-1">
                            <div class="fw-semibold mb-1" style="font-size: 0.85rem;">
                                <i class="bi bi-link-45deg me-1"></i> API товаров
                            </div>
                            <!-- Древовидный вид -->
                            <div class="d-flex align-items-center gap-2">
                                <code class="p-2 bg-light rounded small api-endpoint flex-grow-1" title="Древовидный тип данных">
                                    {{ url('api/catalog/products') }}
                                </code>
                                <div class="d-flex gap-1">
                                    <a href="{{ url('api/catalog/products') }}" target="_blank"
                                       class="btn btn-outline-primary btn-sm copy-btn"
                                       title="Открыть API в новой вкладке">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    {{-- <button class="btn btn-outline-secondary btn-sm copy-btn"
                                            data-clipboard-text="{{ url('api/catalog/tree') }}"
                                            title="Копировать URL API">
                                        <i class="bi bi-clipboard"></i>
                                    </button> --}}
                                </div>
                            </div>
                            <br/>

                            <div class="fw-semibold mb-1" style="font-size: 0.85rem;">
                                <i class="bi bi-link-45deg me-1"></i> API цен
                            </div>

                            <!-- Список цен -->
                            <div class="d-flex align-items-center gap-2">
                                <code class="p-2 bg-light rounded small api-endpoint flex-grow-1" title="Раздельный тип данных">
                                    {{ url('api/catalog/prices') }}
                                </code>
                                <div class="d-flex gap-1">
                                    <a href="{{ url('api/catalog/prices') }}" target="_blank"
                                       class="btn btn-outline-primary btn-sm copy-btn"
                                       title="Открыть API в новой вкладке">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    {{-- <button class="btn btn-outline-secondary btn-sm copy-btn"
                                            data-clipboard-text="{{ url('api/catalog/prices') }}"
                                            title="Копировать URL API">
                                        <i class="bi bi-clipboard"></i>
                                    </button> --}}
                                </div>
                            </div>
                            <br/>

                            <div class="fw-semibold mb-1" style="font-size: 0.85rem;">
                                <i class="bi bi-link-45deg me-1"></i> API складов
                            </div>

                            <!-- Список складов -->
                            <div class="d-flex align-items-center gap-2">
                                <code class="p-2 bg-light rounded small api-endpoint flex-grow-1" title="Раздельный тип данных">
                                    {{ url('api/catalog/warehouses') }}
                                </code>
                                <div class="d-flex gap-1">
                                    <a href="{{ url('api/catalog/warehouses') }}" target="_blank"
                                       class="btn btn-outline-primary btn-sm copy-btn"
                                       title="Открыть API в новой вкладке">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    {{-- <button class="btn btn-outline-secondary btn-sm copy-btn"
                                            data-clipboard-text="{{ url('api/catalog/warehouses') }}"
                                            title="Копировать URL API">
                                        <i class="bi bi-clipboard"></i>
                                    </button> --}}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Документация API -->
                    {{-- <div class="d-flex align-items-center">
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
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Обработка удаления товара
        const deleteButtons = document.querySelectorAll('.delete-product-btn');
        const deleteForm = document.getElementById('deleteProductForm');
        const productNameSpan = document.getElementById('productNameToDelete');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productName = this.getAttribute('data-product-name');
                const deleteUrl = this.getAttribute('data-delete-url');

                productNameSpan.textContent = productName;
                deleteForm.action = deleteUrl;
            });
        });
    });
</script>
@endsection

<!-- Модальное окно подтверждения удаления -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteProductModalLabel">Удаление товара</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить товар <strong id="productNameToDelete"></strong>?</p>
                <div class="alert alert-danger alert-sm mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Внимание! Это действие невозможно отменить. Все связанные предложения и цены также будут удалены.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form id="deleteProductForm" method="POST" class="d-inline">
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
