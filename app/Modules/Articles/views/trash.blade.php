@extends('admin::layouts.default')

@section('title', 'Корзина статей | KotiksCMS')

@section('content')
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Статьи', 'url' => route('admin.articles.index')],
                ['title' => 'Корзина'],
            ],
        ])
    </div>

    <!-- Вкладки -->
    <div class="d-flex mb-4 fade-in">
        <div class="btn-group" role="group">
            <a href="{{ route('admin.articles.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-gift"></i> Активные статьи
            </a>
            <a href="{{ route('admin.articles.categories.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-tags me-1"></i> Категории
            </a>
            <a href="{{ route('admin.articles.trash.index') }}" class="btn btn-primary position-relative">
                <i class="bi bi-trash me-1"></i> Корзина
                @if($trashedCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ $trashedCount }}
                    <span class="visually-hidden">статей в корзине</span>
                </span>
                @endif
            </a>
        </div>
    </div>

    <!-- Действия -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Корзина статей</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">В корзине: {{ $trashedCount }} статей</p>
        </div>
        @if($trashedCount > 0)
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#emptyTrashModal">
            <i class="bi bi-trash"></i> Очистить корзину
        </button>
        @endif
    </div>

    @if($trashedCount > 0)
    <!-- Фильтры -->
    <div class="card fade-in mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.articles.trash.index') }}" class="row g-2">
                <div class="col-md-8">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Поиск по заголовку или отрывку...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="per_page" class="form-select form-select-sm">
                        @foreach ([10, 25, 50, 100] as $count)
                            <option value="{{ $count }}" {{ $perPage == $count ? 'selected' : '' }}>{{ $count }} на странице</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="bi bi-funnel me-1"></i> Применить</button>
                    <a href="{{ route('admin.articles.trash.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Список -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Статьи в корзине</h5>
                <div class="text-muted small">Показано {{ $articles->count() }} из {{ $articles->total() }}</div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="40%">Заголовок</th>
                            <th width="25%">Автор</th>
                            <th width="20%">Удалено</th>
                            <th width="15%" class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($articles as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($item->image)
                                            <img src="{{ asset($item->image) }}" alt="" class="rounded me-3 opacity-50" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="rounded bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center me-3 opacity-50" style="width: 40px; height: 40px;">
                                                <i class="bi bi-newspaper"></i>
                                            </div>
                                        @endif
                                        <div class="opacity-75">
                                            <div class="fw-semibold">{{ $item->title }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="opacity-75">
                                    @if($item->author)
                                        <span class="small">{{ $item->author->name }}</span>
                                    @else
                                        <span class="text-muted small">Не указан</span>
                                    @endif
                                </td>
                                <td class="opacity-75">
                                    <div class="text-muted small">
                                        {{ $item->deleted_at->format('d.m.Y H:i') }}<br>
                                        <span class="text-muted">{{ $item->deleted_at->diffForHumans() }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-actions justify-content-end">
                                        <form action="{{ route('admin.articles.trash.restore', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success btn-sm me-1" title="Восстановить">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-outline-danger btn-sm force-delete-btn"
                                                title="Удалить навсегда"
                                                data-articles-id="{{ $item->id }}"
                                                data-articles-title="{{ $item->title }}"
                                                data-force-url="{{ route('admin.articles.trash.force', $item->id) }}"
                                                data-bs-toggle="modal" data-bs-target="#forceDeleteModal">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($articles->hasPages())
            <div class="card-footer border-0 bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">Показано {{ $articles->firstItem() }} - {{ $articles->lastItem() }} из {{ $articles->total() }}</div>
                    <div>{{ $articles->links('admin::partials.pagination') }}</div>
                </div>
            </div>
        @endif
    </div>
    @else
    <!-- Пусто -->
    <div class="card fade-in">
        <div class="card-body text-center py-5">
            <div class="text-muted">
                <i class="bi bi-trash fs-1 opacity-50"></i>
                <h4 class="mt-3">Корзина пуста</h4>
                <p class="mb-4">Удаленные статьи будут отображаться здесь</p>
                <a href="{{ route('admin.articles.index') }}" class="btn btn-primary">
                    <i class="bi bi-arrow-left me-2"></i> Вернуться к статьям
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Модалки -->
    <div class="modal fade" id="forceDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Полное удаление</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Вы уверены, что хотите полностью удалить статью <strong id="articlesTitleToForceDelete"></strong>?</p>
                    <div class="alert alert-danger alert-sm mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i> Это действие нельзя отменить.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <form id="forceDeleteForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-2"></i>Удалить навсегда</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="emptyTrashModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Очистка корзины</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Вы уверены, что хотите очистить всю корзину?</p>
                    <div class="alert alert-danger alert-sm mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i> Это действие удалит <strong>{{ $trashedCount }}</strong> статьи безвозвратно.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <form action="{{ route('admin.articles.trash.empty') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-2"></i>Очистить корзину</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Для модалки force delete
        const forceDeleteBtns = document.querySelectorAll('.force-delete-btn');
        forceDeleteBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.articlesId;
                const title = this.dataset.articlesTitle;
                const url = this.dataset.forceUrl;
                document.getElementById('articlesTitleToForceDelete').textContent = title;
                document.getElementById('forceDeleteForm').action = url;
            });
        });
    });
</script>
@endpush