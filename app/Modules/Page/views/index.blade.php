@extends('admin::layouts.default')

@section('title', 'Управление страницами | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [['title' => 'Страницы']],
        ])
    </div>

    <!-- Вкладки: Активные и Корзина -->
    <div class="d-flex mb-4 fade-in">
        <div class="btn-group" role="group">
            <a href="{{ route('admin.page.index') }}" class="btn btn-primary">
                <i class="bi bi-file-text me-1"></i> Активные страницы
            </a>
            <a href="{{ route('admin.page.trash.index') }}" class="btn btn-outline-primary position-relative">
                <i class="bi bi-trash me-1"></i> Корзина
                @if($trashedPages > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ $trashedPages }}
                    <span class="visually-hidden">страниц в корзине</span>
                </span>
                @endif
            </a>
        </div>
    </div>

    <!-- Действия с страницами -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Управление страницами</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Всего: {{ $totalPages }} | Опубликовано: {{ $publishedPages }} | Черновиков: {{ $draftPages }} | В корзине: {{ $trashedPages }}
            </p>
        </div>
        <a href="{{ route('admin.page.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Создать страницу
        </a>
    </div>

    <!-- Карточка с фильтрами -->
    <div class="card fade-in mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.page.index') }}" class="row g-2">
                <!-- Поиск -->
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                            placeholder="Поиск по заголовку, URL или содержанию...">
                    </div>
                </div>

                <!-- Фильтр по статусу -->
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Все статусы</option>
                        <option value="published" {{ $status == 'published' ? 'selected' : '' }}>Опубликованные</option>
                        <option value="draft" {{ $status == 'draft' ? 'selected' : '' }}>Черновики</option>
                        <option value="private" {{ $status == 'private' ? 'selected' : '' }}>Приватные</option>
                    </select>
                </div>

                <!-- Сортировка -->
                <div class="col-md-2">
                    <select name="sort_by" class="form-select form-select-sm">
                        <option value="created_at" {{ $sortBy == 'created_at' ? 'selected' : '' }}>Дата создания</option>
                        <option value="title" {{ $sortBy == 'title' ? 'selected' : '' }}>Заголовок</option>
                        <option value="updated_at" {{ $sortBy == 'updated_at' ? 'selected' : '' }}>Дата обновления</option>
                        <option value="order" {{ $sortBy == 'order' ? 'selected' : '' }}>Порядок</option>
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
                    <a href="{{ route('admin.page.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Список страниц -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Список страниц</h5>
                <div class="text-muted small">
                    Показано {{ $pages->count() }} из {{ $pages->total() }} страниц
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="40%">
                                <a href="{{ route('admin.page.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'title', 'sort_order' => $sortBy == 'title' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none d-flex align-items-center">
                                    Заголовок
                                    @if ($sortBy == 'title')
                                        <i class="bi bi-chevron-{{ $sortOrder == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="15%">URL</th>
                            <th width="15%">Статус</th>
                            <th width="15%">Автор</th>
                            <th width="20%">Обновлено</th>
                            <th width="10%">Созданно</th>
                            <th width="10%" class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pages as $page)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center me-3"
                                            style="width: 40px; height: 40px;">
                                            <i class="bi bi-file-text" style="font-size: 1rem;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $page->title }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code>/{{ $page->slug }}</code>
                                </td>
                                <td>
                                    @if($page->status == 'published')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                            <i class="bi bi-check-circle me-1"></i> Опубликовано
                                        </span>
                                    @elseif($page->status == 'draft')
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                                            <i class="bi bi-pencil me-1"></i> Черновик
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                            <i class="bi bi-lock me-1"></i> Приватная
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-muted small">
                                        {{ $page->author->name }}
                                    </div>
                                </td>
                                <td>
                                    <div class="text-muted small">
                                        {{ $page->updated_at->format('d.m.Y H:i') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="text-muted small">
                                        {{ $page->created_at->format('d.m.Y H:i') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="table-actions justify-content-end">
                                        <a href="{{ route('admin.page.edit', $page) }}"
                                            class="btn btn-outline-primary btn-sm me-1" title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm delete-page-btn"
                                            title="В корзину" data-page-id="{{ $page->id }}"
                                            data-page-title="{{ $page->title }}"
                                            data-delete-url="{{ route('admin.page.destroy', $page) }}"
                                            data-bs-toggle="modal" data-bs-target="#deletePageModal">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-file-text fs-4"></i>
                                        <p class="mt-2">Страницы не найдены</p>
                                        @if (request()->hasAny(['search', 'status']))
                                            <a href="{{ route('admin.page.index') }}" class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-arrow-clockwise me-1"></i> Сбросить фильтры
                                            </a>
                                        @else
                                            <a href="{{ route('admin.page.create') }}"
                                                class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-plus-circle me-1"></i> Создать первую страницу
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
        @if ($pages->hasPages())
            <div class="card-footer border-0 bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Показано {{ $pages->firstItem() }} - {{ $pages->lastItem() }} из {{ $pages->total() }}
                    </div>
                    <div>
                        {{ $pages->links('admin::partials.pagination') }}
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
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> О страницах</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2" style="font-size: 0.85rem;">
                        В этом разделе вы можете управлять всеми страницами вашего сайта.
                    </p>
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li>Создавайте и редактируйте страницы с помощью визуального редактора</li>
                        <li>Управляйте статусами страниц (черновик, опубликовано, приватно)</li>
                        <li>Настраивайте SEO-параметры для каждой страницы</li>
                        <li>Используйте корзину для временного хранения удаленных страниц</li>
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
                    <!-- API страниц -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-grow-1">
                            <div class="fw-semibold mb-1" style="font-size: 0.85rem;">
                                <i class="bi bi-link-45deg me-1"></i> API страниц
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <code class="p-2 bg-light rounded small api-endpoint flex-grow-1" title="{{ url('api/pages') }}">
                                    {{ url('api/pages') }}
                                </code>
                                <div class="d-flex gap-1">
                                    <a href="{{ url('api/pages') }}" target="_blank" 
                                       class="btn btn-outline-primary btn-sm copy-btn" 
                                       title="Открыть API в новой вкладке">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    <button class="btn btn-outline-secondary btn-sm copy-btn" 
                                            data-clipboard-text="{{ url('api/pages') }}"
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
<div class="modal fade" id="deletePageModal" tabindex="-1" aria-labelledby="deletePageModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePageModalLabel">Перемещение в корзину</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите переместить страницу <strong id="pageTitleToDelete"></strong> в корзину?</p>
                <div class="alert alert-info alert-sm mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Страница будет доступна в корзине для восстановления в течение 30 дней
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form id="deletePageForm" method="POST" class="d-inline">
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
