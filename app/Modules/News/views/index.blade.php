@extends('admin::layouts.default')

@section('title', 'Управление новостями | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [['title' => 'Новости']],
        ])
    </div>

    <!-- Вкладки: Активные, Категории, Корзина -->
    <div class="btn-group" role="group">
        <a href="{{ route('admin.news.index') }}" class="btn btn-primary">
            <i class="bi bi-newspaper me-1"></i> Активные новости
        </a>
        <a href="{{ route('admin.news.categories.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-tags me-1"></i> Категории
        </a>
        <a href="{{ route('admin.news.trash.index') }}" class="btn btn-outline-primary position-relative">
            <i class="bi bi-trash me-1"></i> Корзина
            @if($trashedNews > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ $trashedNews }}
                <span class="visually-hidden">новостей в корзине</span>
            </span>
            @endif
        </a>
    </div>

    <!-- Действия -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Управление новостями</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Всего: {{ $totalNews }} | 
                Категорий: {{ $totalCategories }} | 
                В корзине: {{ $trashedNews }}
            </p>
        </div>
        <a href="{{ route('admin.news.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Создать новость
        </a>
    </div>

    <!-- Фильтры -->
    <div class="card fade-in mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.news.index') }}" class="row g-2">
                <!-- Поиск -->
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Поиск по заголовку...">
                    </div>
                </div>

                <!-- Сортировка (как в Page) -->
                <div class="col-md-2">
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
                            <option value="{{ $count }}" {{ $perPage == $count ? 'selected' : '' }}>{{ $count }} на странице</option>
                        @endforeach
                    </select>
                </div>

                <!-- Кнопки -->
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-funnel me-1"></i> Применить
                    </button>
                    <a href="{{ route('admin.news.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Список новостей -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Список новостей</h5>
                <div class="text-muted small">Показано {{ $news->count() }} из {{ $news->total() }}</div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="5%">Превью</th>
                            <th width="30%">
                                <a href="{{ route('admin.news.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'title', 'sort_order' => $sortBy == 'title' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}"
                                   class="text-decoration-none d-flex align-items-center">
                                    Заголовок
                                    @if ($sortBy == 'title')
                                        <i class="bi bi-chevron-{{ $sortOrder == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="15%">Категории</th>
                            <th width="12%">Автор</th>
                            <th width="12%">Обновлено</th>
                            <th width="12%">Создано</th>
                            <th width="19%" class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($news as $item)
                            <tr>
                                <td>
                                    @if($item->image)
                                        <img src="{{ Storage::url($item->image) }}" alt="" style="max-height: 100px;" class="img-thumbnail">
                                    @else
                                        <div class="rounded bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <i class="bi bi-newspaper"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <div class="fw-semibold">{{ $item->title }}</div>
                                            @if($item->excerpt)
                                                <div class="small text-muted">{{ Str::limit($item->excerpt, 50) }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($item->categories->count())
                                        @foreach($item->categories as $category)
                                            <span class="badge bg-secondary">{{ $category->title }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->author)
                                        <span class="small">{{ $item->author->name }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $item->updated_at ? $item->updated_at->format('d.m.Y H:i') : '—' }}
                                </td>
                                <td>
                                    {{ $item->created_at ? $item->created_at->format('d.m.Y H:i') : '—' }}
                                </td>
                                <td>
                                    <div class="table-actions justify-content-end">
                                        <a href="{{ route('admin.news.edit', $item) }}" class="btn btn-outline-primary btn-sm me-1" title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm delete-news-btn"
                                                title="В корзину"
                                                data-news-id="{{ $item->id }}"
                                                data-news-title="{{ $item->title }}"
                                                data-delete-url="{{ route('admin.news.destroy', $item) }}"
                                                data-bs-toggle="modal" data-bs-target="#deleteNewsModal">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-newspaper fs-4"></i>
                                        <p class="mt-2">Новости не найдены</p>
                                        @if(request()->has('search'))
                                            <a href="{{ route('admin.news.index') }}" class="btn btn-primary btn-sm mt-2">Сбросить фильтры</a>
                                        @else
                                            <a href="{{ route('admin.news.create') }}" class="btn btn-primary btn-sm mt-2">Создать первую новость</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($news->hasPages())
            <div class="card-footer border-0 bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Показано {{ $news->firstItem() }} - {{ $news->lastItem() }} из {{ $news->total() }}
                    </div>
                    <div>
                        {{ $news->links('admin::partials.pagination') }}
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
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> О новостях</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2" style="font-size: 0.85rem;">Управляйте новостными записями на сайте.</p>
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li>Создавайте и редактируйте новости</li>
                        <li>Используйте корзину для временного хранения</li>
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
                                <i class="bi bi-link-45deg me-1"></i> API Новостей
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <code class="p-2 bg-light rounded small api-endpoint flex-grow-1" title="Древовидный тип данных">
                                    {{ url('api/news/tree') }}
                                </code>
                                <div class="d-flex gap-1">
                                    <a href="{{ url('api/news/tree') }}" target="_blank" class="btn btn-outline-primary btn-sm copy-btn" title="Открыть API в новой вкладке">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    <button class="btn btn-outline-secondary btn-sm copy-btn" data-clipboard-text="{{ url('api/news/tree') }}" title="Копировать URL API">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <code class="p-2 bg-light rounded small api-endpoint flex-grow-1" title="Выводит только категории">
                                    {{ url('api/news/categories') }}
                                </code>
                                <div class="d-flex gap-1">
                                    <a href="{{ url('api/news/categories') }}" target="_blank" class="btn btn-outline-primary btn-sm copy-btn" title="Открыть API в новой вкладке">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    <button class="btn btn-outline-secondary btn-sm copy-btn" data-clipboard-text="{{ url('api/news/categories') }}" title="Копировать URL API">
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
                                    <a href="{{ url('api/documentation') }}" target="_blank" class="btn btn-outline-info btn-sm copy-btn" title="Открыть документацию в новой вкладке">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    <button class="btn btn-outline-secondary btn-sm copy-btn" data-clipboard-text="{{ url('api/documentation') }}" title="Копировать URL документации">
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

    <!-- Модальное окно подтверждения удаления в корзину -->
    <div class="modal fade" id="deleteNewsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Перемещение в корзину</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Вы уверены, что хотите переместить новость <strong id="newsTitleToDelete"></strong> в корзину?</p>
                    <div class="alert alert-info alert-sm mb-0">
                        <i class="bi bi-info-circle me-2"></i> Новость будет доступна для восстановления в течение 30 дней.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <form id="deleteNewsForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-2"></i>В корзину</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete-news-btn');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.newsId;
                const title = this.dataset.newsTitle;
                const url = this.dataset.deleteUrl;
                document.getElementById('newsTitleToDelete').textContent = title;
                document.getElementById('deleteNewsForm').action = url;
            });
        });
    });
</script>
@endpush