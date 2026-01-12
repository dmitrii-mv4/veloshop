@extends('admin::layouts.default')

@section('title', 'Управление информационными блоками | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [['title' => 'Информационные блоки']],
        ])
    </div>

    <!-- Вкладки: Активные и Корзина -->
    <div class="d-flex mb-4 fade-in">
        <div class="btn-group" role="group">
            <a href="{{ route('admin.iblock.index') }}" class="btn btn-primary">
                <i class="bi bi-file-text me-1"></i> Активные блоки
            </a>
            <a href="{{ route('admin.iblock.trash.index') }}" class="btn btn-outline-primary position-relative">
                <i class="bi bi-trash me-1"></i> Корзина
                @if($trashedIBlocks > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ $trashedIBlocks }}
                    <span class="visually-hidden">блоков в корзине</span>
                </span>
                @endif
            </a>
        </div>
    </div>

    <!-- Действия с блоками -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Управление информационными блоками</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Всего: {{ $totalIBlocks }} | В корзине: {{ $trashedIBlocks }}
            </p>
        </div>
        <a href="{{ route('admin.iblock.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Создать блок
        </a>
    </div>

    <!-- Карточка с фильтрами -->
    <div class="card fade-in mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.iblock.index') }}" class="row g-2">
                <!-- Поиск -->
                <div class="col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                            placeholder="Поиск по заголовку или содержанию...">
                    </div>
                </div>

                <!-- Сортировка -->
                <div class="col-md-3">
                    <select name="sort_by" class="form-select form-select-sm">
                        <option value="created_at" {{ $sortBy == 'created_at' ? 'selected' : '' }}>Дата создания</option>
                        <option value="title" {{ $sortBy == 'title' ? 'selected' : '' }}>Заголовок</option>
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
                    <a href="{{ route('admin.iblock.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Список информационных блоков -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Список информационных блоков</h5>
                <div class="text-muted small">
                    Показано {{ $iblocks->count() }} из {{ $iblocks->total() }} блоков
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="40%">
                                <a href="{{ route('admin.iblock.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'title', 'sort_order' => $sortBy == 'title' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none d-flex align-items-center">
                                    Заголовок
                                    @if ($sortBy == 'title')
                                        <i class="bi bi-chevron-{{ $sortOrder == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="15%">Автор</th>
                            <th width="15%">Обновлено</th>
                            <th width="15%">Созданно</th>
                            <th width="15%" class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($iblocks as $iblock)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3"
                                            style="width: 40px; height: 40px;">
                                            <i class="bi-input-cursor-text nav-icon"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $iblock->title }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($iblock->author)
                                        <div class="d-flex align-items-center">
                                            <span class="small">{{ $iblock->author->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted small">Не указан</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $iblock->updated_at }}
                                </td>
                                <td>
                                    {{ $iblock->created_at }}
                                </td>
                                <td>
                                    <div class="table-actions justify-content-end">
                                        <a href="{{ route('admin.iblock.edit', $iblock) }}"
                                            class="btn btn-outline-primary btn-sm me-1" title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm delete-iblock-btn"
                                            title="В корзину" data-iblock-id="{{ $iblock->id }}"
                                            data-iblock-title="{{ $iblock->title }}"
                                            data-delete-url="{{ route('admin.iblock.destroy', $iblock) }}"
                                            data-bs-toggle="modal" data-bs-target="#deleteIBlockModal">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-file-text fs-4"></i>
                                        <p class="mt-2">Информационные блоки не найдены</p>
                                        @if (request()->has('search'))
                                            <a href="{{ route('admin.iblock.index') }}" class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-arrow-clockwise me-1"></i> Сбросить фильтры
                                            </a>
                                        @else
                                            <a href="{{ route('admin.iblock.create') }}"
                                                class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-plus-circle me-1"></i> Создать первый блок
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
        @if ($iblocks->hasPages())
            <div class="card-footer border-0 bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Показано {{ $iblocks->firstItem() }} - {{ $iblocks->lastItem() }} из {{ $iblocks->total() }}
                    </div>
                    <div>
                        {{ $iblocks->links('admin::partials.pagination') }}
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
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> О информационных блоках</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2" style="font-size: 0.85rem;">
                        В этом разделе вы можете управлять блоками на сайте.
                    </p>
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li>Создавайте и редактируйте блоки</li>
                        <li>Используйте корзину для временного хранения удаленных блоков</li>
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
                                <code class="p-2 bg-light rounded small api-endpoint flex-grow-1" title="{{ url('api/iblocks') }}">
                                    {{ url('api/iblocks') }}
                                </code>
                                <div class="d-flex gap-1">
                                    <a href="{{ url('api/iblocks') }}" target="_blank" 
                                       class="btn btn-outline-primary btn-sm copy-btn" 
                                       title="Открыть API в новой вкладке">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    <button class="btn btn-outline-secondary btn-sm copy-btn" 
                                            data-clipboard-text="{{ url('api/iblocks') }}"
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
<div class="modal fade" id="deleteIBlockModal" tabindex="-1" aria-labelledby="deleteIBlockModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteIBlockModalLabel">Перемещение в корзину</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите переместить информационный блок <strong id="iblockTitleToDelete"></strong> в корзину?</p>
                <div class="alert alert-info alert-sm mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Блок будет доступен в корзине для восстановления в течение 30 дней
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form id="deleteIBlockForm" method="POST" class="d-inline">
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