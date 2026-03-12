@extends('admin::layouts.default')

@section('title', 'Корзина типов покупателей | KotiksCMS')

@section('content')
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Каталог', 'url' => route('catalog.index')],
                ['title' => 'Покупатели', 'url' => route('catalog.customers.index')],
                ['title' => 'Типы покупателей', 'url' => route('catalog.customers.type.index')],
                ['title' => 'Корзина']
            ],
        ])
    </div>

    <!-- Вкладки и возврат -->
    <div class="d-flex justify-content-between align-items-center mb-4 fade-in">
        <div class="btn-group" role="group">
            <a href="{{ route('catalog.customers.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-people"></i> Покупатели
            </a>
            <a href="{{ route('catalog.customers.type.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-person-badge"></i> Типы покупателей
            </a>
        </div>
        <a href="{{ route('catalog.customers.type.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Вернуться к списку
        </a>
    </div>

    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Корзина типов покупателей</h1>
            <p class="text-muted mb-0">Удалённых записей: {{ $trashedCount }}</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('catalog.customers.type.force-delete-all') }}" method="POST" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Очистить корзину полностью?')">
                    <i class="bi bi-trash"></i> Очистить корзину
                </button>
            </form>
        </div>
    </div>

    <!-- Фильтры -->
    <div class="card fade-in mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('catalog.customers.type.trash') }}" class="row g-2">
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
                    <a href="{{ route('catalog.customers.type.trash') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Таблица удалённых типов -->
    <div class="card fade-in">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Удалённые типы покупателей</h5>
            <span class="text-muted small">Показано {{ $types->count() }} из {{ $types->total() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ route('catalog.customers.type.trash', array_merge(request()->except(['sort_by','sort_order']), ['sort_by' => 'id', 'sort_order' => $sortBy=='id' && $sortOrder=='asc' ? 'desc' : 'asc'])) }}"
                                   class="text-decoration-none d-flex align-items-center">
                                    ID
                                    @if($sortBy == 'id') <i class="bi bi-chevron-{{ $sortOrder=='asc'?'up':'down' }} ms-1"></i> @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('catalog.customers.type.trash', array_merge(request()->except(['sort_by','sort_order']), ['sort_by' => 'title', 'sort_order' => $sortBy=='title' && $sortOrder=='asc' ? 'desc' : 'asc'])) }}"
                                   class="text-decoration-none d-flex align-items-center">
                                    Название
                                    @if($sortBy == 'title') <i class="bi bi-chevron-{{ $sortOrder=='asc'?'up':'down' }} ms-1"></i> @endif
                                </a>
                            </th>
                            <th>Кол-во покупателей</th>
                            <th>Дата удаления</th>
                            <th class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($types as $type)
                            <tr>
                                <td>#{{ $type->id }}</td>
                                <td class="fw-semibold">{{ $type->title }}</td>
                                <td>{{ $type->customers()->count() }}</td>
                                <td>{{ $type->deleted_at->format('d.m.Y H:i') }}</td>
                                <td class="text-end">
                                    <form action="{{ route('catalog.customers.type.restore', $type->id) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-outline-success btn-sm" title="Восстановить">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('catalog.customers.type.force-delete', $type->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Удалить навсегда?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Удалить навсегда">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">Корзина пуста</td></tr>
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
@endsection