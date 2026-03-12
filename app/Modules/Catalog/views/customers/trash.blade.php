@extends('admin::layouts.default')

@section('title', 'Корзина покупателей | KotiksCMS')

@section('content')
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Каталог', 'url' => route('catalog.index')],
                ['title' => 'Покупатели', 'url' => route('catalog.customers.index')],
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
        <a href="{{ route('catalog.customers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Вернуться к списку
        </a>
    </div>

    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Корзина покупателей</h1>
            <p class="text-muted mb-0">Удалённых записей: {{ $trashedCount }}</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('catalog.customers.force-delete-all') }}" method="POST" class="d-inline">
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
            <form method="GET" action="{{ route('catalog.customers.trash') }}" class="row g-2">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                               placeholder="Поиск по имени или email">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="type_id" class="form-select form-select-sm">
                        <option value="all" {{ $typeId == 'all' ? 'selected' : '' }}>Все типы</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" {{ $typeId == $type->id ? 'selected' : '' }}>
                                {{ $type->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="per_page" class="form-select form-select-sm">
                        @foreach([10,25,50,100] as $count)
                            <option value="{{ $count }}" {{ $perPage == $count ? 'selected' : '' }}>
                                {{ $count }} на странице
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-funnel"></i> Применить
                    </button>
                    <a href="{{ route('catalog.customers.trash') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Таблица удалённых покупателей -->
    <div class="card fade-in">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Удалённые покупатели</h5>
            <span class="text-muted small">Показано {{ $customers->count() }} из {{ $customers->total() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ route('catalog.customers.trash', array_merge(request()->except(['sort_by','sort_order']), ['sort_by' => 'id', 'sort_order' => $sortBy=='id' && $sortOrder=='asc' ? 'desc' : 'asc'])) }}"
                                   class="text-decoration-none d-flex align-items-center">
                                    ID
                                    @if($sortBy == 'id') <i class="bi bi-chevron-{{ $sortOrder=='asc'?'up':'down' }} ms-1"></i> @endif
                                </a>
                            </th>
                            <th>Покупатель</th>
                            <th>Email</th>
                            <th>Тип</th>
                            <th>Дата удаления</th>
                            <th class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td>{{ $customer->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($customer->user?->avatar)
                                            <img src="{{ Storage::url($customer->user->avatar) }}"
                                                 class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center me-2"
                                                 style="width:32px;height:32px;">
                                                <i class="bi bi-person"></i>
                                            </div>
                                        @endif
                                        <span class="fw-semibold">{{ $customer->user?->name ?? '—' }}</span>
                                    </div>
                                </td>
                                <td><a href="mailto:{{ $customer->user?->email }}">{{ $customer->user?->email }}</a></td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info">
                                        {{ $customer->type?->title ?? '—' }}
                                    </span>
                                </td>
                                <td>{{ $customer->deleted_at->format('d.m.Y H:i') }}</td>
                                <td class="text-end">
                                    <form action="{{ route('catalog.customers.restore', $customer->id) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-outline-success btn-sm" title="Восстановить">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('catalog.customers.force-delete', $customer->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Удалить навсегда?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Удалить навсегда">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted">Корзина пуста</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($customers->hasPages())
            <div class="card-footer bg-light">
                {{ $customers->links('admin::partials.pagination') }}
            </div>
        @endif
    </div>
@endsection