@extends('admin::layouts.default')

@section('title', 'Предложения товара | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Каталог', 'url' => route('catalog.index')],
                ['title' => $product->name, 'url' => route('catalog.products.show', $product)],
                ['title' => 'Предложения']
            ],
        ])
    </div>

    <!-- Навигация -->
    <div class="d-flex mb-4 fade-in">
        <div class="btn-group" role="group">
            <a href="{{ route('catalog.products.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Назад к товарам
            </a>
            <a href="{{ route('catalog.products.edit', $product) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i> Редактировать товар
            </a>
            <a href="{{ route('catalog.products.offers.create', $product) }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Добавить предложение
            </a>
        </div>
    </div>

    <!-- Заголовок -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Предложения товара: {{ $product->name }}</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                ID товара: <code>{{ $product->product_id }}</code> |
                Всего предложений: {{ $totalOffers }}
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-info">
                <i class="bi bi-box-seam me-1"></i> {{ $product->brand ?? 'Без бренда' }}
            </span>
            @if($product->model)
                <span class="badge bg-secondary">{{ $product->model }}</span>
            @endif
        </div>
    </div>

    <!-- Карточка с фильтрами -->
    <div class="card fade-in mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('catalog.products.offers.index', $product) }}" class="row g-2">
                <!-- Поиск -->
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                            placeholder="Поиск по названию, артикулу или ID...">
                    </div>
                </div>

                <!-- Сортировка -->
                <div class="col-md-2">
                    <select name="sort_by" class="form-select form-select-sm">
                        <option value="created_at" {{ $sortBy == 'created_at' ? 'selected' : '' }}>Дата создания</option>
                        <option value="name" {{ $sortBy == 'name' ? 'selected' : '' }}>Название</option>
                        <option value="articul_supplier" {{ $sortBy == 'articul_supplier' ? 'selected' : '' }}>Артикул</option>
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
                    <a href="{{ route('catalog.products.offers.index', $product) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Список предложений -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Список предложений</h5>
                <div class="text-muted small">
                    Показано {{ $offers->count() }} из {{ $offers->total() }} предложений
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="25%">
                                <a href="{{ route('catalog.products.offers.index', array_merge(['product' => $product->id], request()->except(['sort_by', 'sort_order']), ['sort_by' => 'name', 'sort_order' => $sortBy == 'name' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none d-flex align-items-center">
                                    Название предложения
                                    @if ($sortBy == 'name')
                                        <i class="bi bi-chevron-{{ $sortOrder == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="15%">ID предложения</th>
                            <th width="15%">Артикул поставщика</th>
                            <th width="10%">Обновлено</th>
                            <th width="10%">Добавлено</th>
                            <th width="15%" class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($offers as $offer)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center me-3"
                                            style="width: 40px; height: 40px;">
                                            <i class="bi bi-tags" style="font-size: 1rem;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $offer->name }}</div>
                                            <div class="text-muted small">
                                                @if($offer->meta_title)
                                                    {{ Str::limit($offer->meta_title, 50) }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code class="small">{{ $offer->offer_id }}</code>
                                </td>
                                <td>
                                    @if($offer->articul_supplier)
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                            {{ $offer->articul_supplier }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-muted small">
                                        {{ $offer->updated_at->format('d.m.Y H:i') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="text-muted small">
                                        {{ $offer->created_at->format('d.m.Y H:i') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="table-actions justify-content-end">
                                        <a href="{{ route('catalog.products.offers.show', ['product' => $product->id, 'offer' => $offer->id]) }}"
                                            class="btn btn-outline-info btn-sm me-1" title="Просмотр">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('catalog.products.offers.edit', ['product' => $product->id, 'offer' => $offer->id]) }}"
                                            class="btn btn-outline-primary btn-sm me-1" title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm delete-offer-btn"
                                            title="Удалить"
                                            data-offer-id="{{ $offer->id }}"
                                            data-offer-name="{{ $offer->name }}"
                                            data-delete-url="{{ route('catalog.products.offers.destroy', ['product' => $product->id, 'offer' => $offer->id]) }}"
                                            data-bs-toggle="modal" data-bs-target="#deleteOfferModal">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-tags fs-4"></i>
                                        <p class="mt-2">Предложения не найдены</p>
                                        @if (request()->hasAny(['search']))
                                            <a href="{{ route('catalog.products.offers.index', $product) }}" class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-arrow-clockwise me-1"></i> Сбросить фильтры
                                            </a>
                                        @else
                                            <a href="{{ route('catalog.products.offers.create', $product) }}"
                                                class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-plus-circle me-1"></i> Добавить первое предложение
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
        @if ($offers->hasPages())
            <div class="card-footer border-0 bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Показано {{ $offers->firstItem() }} - {{ $offers->lastItem() }} из {{ $offers->total() }}
                    </div>
                    <div>
                        {{ $offers->links('admin::partials.pagination') }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Информация о товаре -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> Информация о товаре</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl>
                                <dt class="text-muted small">Название:</dt>
                                <dd class="mb-2">{{ $product->name }}</dd>

                                <dt class="text-muted small">Бренд:</dt>
                                <dd class="mb-2">{{ $product->brand ?? '—' }}</dd>

                                <dt class="text-muted small">Модель:</dt>
                                <dd>{{ $product->model ?? '—' }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl>
                                <dt class="text-muted small">ID товара:</dt>
                                <dd class="mb-2"><code>{{ $product->product_id }}</code></dd>

                                <dt class="text-muted small">Сезон:</dt>
                                <dd class="mb-2">{{ $product->seazon ?? '—' }}</dd>

                                <dt class="text-muted small">Группа:</dt>
                                <dd>{{ $product->proup_name ?? '—' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-lightbulb me-2"></i> О предложениях</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2" style="font-size: 0.85rem;">
                        Предложения (вариации) — это разные версии одного товара с разными характеристиками.
                    </p>
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li>Каждое предложение может иметь свои цены и атрибуты</li>
                        <li>Управляйте наличием на складах для каждого предложения</li>
                        <li>Настраивайте SEO-параметры отдельно для каждой вариации</li>
                        <li>Используйте артикулы для быстрого поиска</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

<!-- Модальное окно подтверждения удаления -->
<div class="modal fade" id="deleteOfferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Удаление предложения</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить предложение <strong id="offerNameToDelete"></strong>?</p>
                <div class="alert alert-danger alert-sm mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Внимание!</strong> Это действие невозможно отменить. Все связанные цены и атрибуты также будут удалены.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form id="deleteOfferForm" method="POST" class="d-inline">
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
        // Обработка удаления предложения
        const deleteButtons = document.querySelectorAll('.delete-offer-btn');
        const deleteForm = document.getElementById('deleteOfferForm');
        const offerNameSpan = document.getElementById('offerNameToDelete');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const offerName = this.getAttribute('data-offer-name');
                const deleteUrl = this.getAttribute('data-delete-url');

                offerNameSpan.textContent = offerName;
                deleteForm.action = deleteUrl;
            });
        });
    });
</script>
@endpush
