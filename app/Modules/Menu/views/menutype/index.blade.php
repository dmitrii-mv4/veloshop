@extends('admin::layouts.default')

@section('title', 'Типы меню | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Меню', 'url' => route('admin.menu.index')],
                ['title' => 'Типы меню']
            ],
        ])
    </div>

    <!-- Действия с типами меню -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Типы меню</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Всего: {{ $menuTypes->total() }} | Используется: {{ $usedTypes }} | Не используется: {{ $unusedTypes }}
            </p>
        </div>
        <a href="{{ route('admin.menu.types.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Создать тип
        </a>
    </div>

    <!-- Карточка с фильтрами -->
    <div class="card fade-in mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.menu.types.index') }}" class="row g-2">
                <!-- Поиск -->
                <div class="col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                            placeholder="Поиск по названию типа...">
                    </div>
                </div>

                <!-- Сортировка -->
                <div class="col-md-3">
                    <select name="sort_by" class="form-select form-select-sm">
                        <option value="created_at" {{ $sortBy == 'created_at' ? 'selected' : '' }}>Дата создания</option>
                        <option value="name" {{ $sortBy == 'name' ? 'selected' : '' }}>Название</option>
                        <option value="updated_at" {{ $sortBy == 'updated_at' ? 'selected' : '' }}>Дата обновления</option>
                        <option value="menus_count" {{ $sortBy == 'menus_count' ? 'selected' : '' }}>Количество меню</option>
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
                        <i class="bi bi-funnel me-1"></i> Применить
                    </button>
                    <a href="{{ route('admin.menu.types.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Список типов меню -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Список типов меню</h5>
                <div class="text-muted small">
                    Показано {{ $menuTypes->count() }} из {{ $menuTypes->total() }} типов
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="40%">
                                <a href="{{ route('admin.menu.types.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'name', 'sort_order' => $sortBy == 'name' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none d-flex align-items-center">
                                    Название типа
                                    @if ($sortBy == 'name')
                                        <i class="bi bi-chevron-{{ $sortOrder == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="20%">
                                <a href="{{ route('admin.menu.types.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'menus_count', 'sort_order' => $sortBy == 'menus_count' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none d-flex align-items-center">
                                    Меню
                                    @if ($sortBy == 'menus_count')
                                        <i class="bi bi-chevron-{{ $sortOrder == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="20%">Создано</th>
                            <th width="20%">Обновлено</th>
                            <th width="20%" class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($menuTypes as $menuType)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3"
                                            style="width: 40px; height: 40px;">
                                            <i class="bi bi-tag" style="font-size: 1rem;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $menuType->name }}</div>
                                            <div class="text-muted small">
                                                ID: {{ $menuType->id }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($menuType->menus_count > 0)
                                        <span class="badge bg-primary">
                                            {{ $menuType->menus_count }} меню
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            Не используется
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-muted small">
                                        {{ $menuType->created_at->format('d.m.Y H:i') }}
                                        @if($menuType->creator)
                                            <br>{{ $menuType->creator->name }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="text-muted small">
                                        {{ $menuType->updated_at->format('d.m.Y H:i') }}
                                        @if($menuType->updater)
                                            <br>{{ $menuType->updater->name }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="table-actions justify-content-end">
                                        <a href="{{ route('admin.menu.types.edit', $menuType) }}"
                                            class="btn btn-outline-primary btn-sm me-1" title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <!-- Форма удаления типа меню -->
                                        <form action="{{ route('admin.menu.types.destroy', $menuType) }}" 
                                            method="POST" 
                                            class="d-inline"
                                            onsubmit="return confirmDeleteMenuType({{ $menuType->menus_count }}, '{{ addslashes($menuType->name) }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Удалить">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-tag fs-4"></i>
                                        <p class="mt-2">Типы меню не найдены</p>
                                        @if (request()->has('search'))
                                            <a href="{{ route('admin.menu.types.index') }}" class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-arrow-clockwise me-1"></i> Сбросить фильтры
                                            </a>
                                        @else
                                            <a href="{{ route('admin.menu.types.create') }}"
                                                class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-plus-circle me-1"></i> Создать первый тип меню
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
        @if ($menuTypes->hasPages())
            <div class="card-footer border-0 bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Показано {{ $menuTypes->firstItem() }} - {{ $menuTypes->lastItem() }} из {{ $menuTypes->total() }}
                    </div>
                    <div>
                        {{ $menuTypes->links('admin::partials.pagination') }}
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
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> О типах меню</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2" style="font-size: 0.85rem;">
                        Типы меню позволяют классифицировать меню по назначению.
                    </p>
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li>Создавайте типы для разных областей сайта (Header, Footer, Sidebar)</li>
                        <li>Каждое меню можно связать с определенным типом</li>
                        <li>Типы помогают организовать меню в на строне front-end части</li>
                        <li>При удалении типа, у связанных меню тип будет сброшен</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-link me-2"></i> Быстрые ссылки</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.menu.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-menu-button-wide me-2"></i> Перейти к меню
                        </a>
                        <a href="{{ route('admin.menu.create') }}" class="btn btn-outline-success">
                            <i class="bi bi-plus-circle me-2"></i> Создать новое меню
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
function confirmDeleteMenuType(menusCount, name) {
    let message = 'Вы уверены, что хотите удалить тип меню «' + name + '»?\n\n';
    
    if (menusCount > 0) {
        message += 'Внимание! Этот тип используется в ' + menusCount + ' меню. При удалении у этих меню будет удалена связь с типом.\n\n';
    } else {
        message += 'Этот тип меню не используется.\n\n';
    }
    
    message += 'Это действие нельзя отменить.';
    
    return confirm(message);
}
</script>
@endsection

@section('styles')
<style>
    .table-actions {
        display: flex;
        gap: 0.25rem;
        justify-content: flex-end;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    /* Адаптивные стили для таблицы */
    @media (max-width: 768px) {
        .table-responsive {
            margin: 0 -1rem;
            width: calc(100% + 2rem);
        }
        
        .table th,
        .table td {
            padding: 0.5rem;
            font-size: 0.875rem;
        }
        
        .table-actions {
            flex-direction: column;
            gap: 0.125rem;
        }
        
        .table-actions .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .table-actions .btn .bi {
            margin: 0 !important;
        }
    }
</style>
@endsection