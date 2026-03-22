@extends('admin::layouts.default')

@section('title', 'Типы покупателей | KotiksCMS')

@section('content')
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Каталог', 'url' => route('catalog.index')],
                ['title' => 'Покупатели', 'url' => route('catalog.customers.index')],
                ['title' => 'Типы покупателей']
            ],
        ])
    </div>

    <!-- Вкладки и корзина -->
    <div class="d-flex justify-content-between align-items-center mb-4 fade-in">
        <div class="btn-group" role="group">
            <a href="{{ route('catalog.customers.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-people"></i> Покупатели
            </a>
            <a href="{{ route('catalog.customers.type.index') }}" class="btn btn-primary">
                <i class="bi bi-person-badge"></i> Типы покупателей
            </a>
        </div>
        <a href="{{ route('catalog.customers.type.trash') }}" class="btn btn-outline-danger">
            <i class="bi bi-trash"></i> Корзина ({{ $trashedCount }})
        </a>
    </div>

    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Типы покупателей</h1>
            <p class="text-muted mb-0">Всего: {{ $totalTypes }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('catalog.customers.type.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Добавить тип
            </a>
        </div>
    </div>

    <!-- Фильтры -->
    <div class="card fade-in mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('catalog.customers.type.index') }}" class="row g-2">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                               placeholder="Поиск по названию">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="per_page" class="form-select form-select-sm">
                        @foreach([10,25,50,100] as $count)
                            <option value="{{ $count }}" {{ $perPage == $count ? 'selected' : '' }}>{{ $count }} на стр.</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-funnel"></i> Применить
                    </button>
                    <a href="{{ route('catalog.customers.type.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Таблица типов -->
    <div class="card fade-in">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Список типов покупателей</h5>
            <span class="text-muted small">Показано {{ $types->count() }} из {{ $types->total() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ route('catalog.customers.type.index', array_merge(request()->except(['sort_by','sort_order']), ['sort_by' => 'id', 'sort_order' => $sortBy=='id' && $sortOrder=='asc' ? 'desc' : 'asc'])) }}"
                                   class="text-decoration-none d-flex align-items-center">
                                    ID
                                    @if($sortBy == 'id') <i class="bi bi-chevron-{{ $sortOrder=='asc'?'up':'down' }} ms-1"></i> @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('catalog.customers.type.index', array_merge(request()->except(['sort_by','sort_order']), ['sort_by' => 'title', 'sort_order' => $sortBy=='title' && $sortOrder=='asc' ? 'desc' : 'asc'])) }}"
                                   class="text-decoration-none d-flex align-items-center">
                                    Название
                                    @if($sortBy == 'title') <i class="bi bi-chevron-{{ $sortOrder=='asc'?'up':'down' }} ms-1"></i> @endif
                                </a>
                            </th>
                            <th>Кол-во покупателей</th>
                            <th>Статус</th>
                            <th class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($types as $type)
                            <tr>
                                <td>#{{ $type->id }}</td>
                                <td class="fw-semibold">{{ $type->title }}</td>
                                <td>{{ $type->customers()->count() }}</td>
                                <td>
                                    @if($type->is_active)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                            <i class="bi bi-check-circle"></i> Активен
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">
                                            <i class="bi bi-x-circle"></i> Неактивен
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('catalog.customers.type.edit', $type->id) }}" class="btn btn-outline-primary btn-sm" title="Редактировать">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger btn-sm delete-type-btn"
                                            data-id="{{ $type->id }}"
                                            data-name="{{ $type->title }}"
                                            data-url="{{ route('catalog.customers.type.destroy', $type->id) }}"
                                            data-bs-toggle="modal" data-bs-target="#deleteTypeModal">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">Типы не найдены</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($types->hasPages())
            <div class="card-footer bg-light">
                {{ $types->links('admin::partials.pagination') }}
            </div>
        @endif
    </div>

    <!-- Модальное окно подтверждения удаления -->
    <div class="modal fade" id="deleteTypeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Подтверждение удаления</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Удалить тип <strong id="deleteTypeName"></strong>?</p>
                    <div class="alert alert-warning">
                        Если есть покупатели этого типа, удаление будет запрещено.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <form id="deleteTypeForm" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">Удалить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Информационная панель (без изменений) -->
    <div class="row mt-4 fade-in">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> О покупателях</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2" style="font-size: 0.85rem;">
                        В этом разделе вы можете создавать и управлять типами плательщиков для покупателей.
                    </p>
                    <p class="mb-2" style="font-size: 0.85rem;">
                        Тип плательщика можно выбирать на сайте при регистрации или оформления заказа.
                    </p>
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
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-grow-1">
                            <div class="fw-semibold mb-1" style="font-size: 0.85rem;">
                                <i class="bi bi-link-45deg me-1"></i> API страниц
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <code class="p-2 bg-light rounded small api-endpoint flex-grow-1">
                                    {{ url('api/catalog/customers/type') }}
                                </code>
                                <div class="d-flex gap-1">
                                    <a href="{{ url('api/catalog/customers/type') }}" target="_blank" 
                                       class="btn btn-outline-primary btn-sm copy-btn" 
                                       title="Открыть API в новой вкладке">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    <button class="btn btn-outline-secondary btn-sm copy-btn" 
                                            data-clipboard-text="{{ url('api/catalog/customers/type') }}"
                                            title="Копировать URL API">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="fw-semibold mb-1" style="font-size: 0.85rem;">
                                <i class="bi bi-book me-1"></i> Документация
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="api-endpoint flex-grow-1">
                                    {{ url('api/documentation') }}
                                </span>
                                <div class="d-flex gap-1">
                                    <a href="{{ url('api/documentation') }}" target="_blank" 
                                       class="btn btn-outline-info btn-sm copy-btn" 
                                       title="Открыть документацию">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    <button class="btn btn-outline-secondary btn-sm copy-btn" 
                                            data-clipboard-text="{{ url('api/documentation') }}"
                                            title="Копировать URL">
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

@push('scripts')
<script>
    document.querySelectorAll('.delete-type-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('deleteTypeName').textContent = this.dataset.name;
            document.getElementById('deleteTypeForm').action = this.dataset.url;
        });
    });
</script>
@endpush