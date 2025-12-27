@extends('admin::layouts.default')

@section('title', 'Корзина информационных блоков | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Информационные блоки', 'url' => route('admin.iblock.index')],
                ['title' => 'Корзина']
            ],
        ])
    </div>

    <!-- Вкладки: Активные и Корзина -->
    <div class="d-flex mb-4 fade-in">
        <div class="btn-group" role="group">
            <a href="{{ route('admin.iblock.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-file-text me-1"></i> Активные блоки
            </a>
            <a href="{{ route('admin.iblock.trash.index') }}" class="btn btn-primary position-relative">
                <i class="bi bi-trash me-1"></i> Корзина
                @if($trashedCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ $trashedCount }}
                    <span class="visually-hidden">блоков в корзине</span>
                </span>
                @endif
            </a>
        </div>
    </div>

    <!-- Действия с корзиной -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Корзина информационных блоков</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                В корзине: {{ $trashedCount }} блоков
            </p>
        </div>
        @if($trashedCount > 0)
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#emptyTrashModal">
            <i class="bi bi-trash"></i> Очистить корзину
        </button>
        @endif
    </div>

    @if($trashedCount > 0)
    <!-- Карточка с фильтрами -->
    <div class="card fade-in mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.iblock.trash.index') }}" class="row g-2">
                <!-- Поиск в корзине -->
                <div class="col-md-8">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                            placeholder="Поиск по заголовку или содержанию...">
                    </div>
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
                    <a href="{{ route('admin.iblock.trash.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Список блоков в корзине -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Информационные блоки в корзине</h5>
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
                            <th width="40%">Заголовок</th>
                            <th width="25%">Автор</th>
                            <th width="20%">Удалено</th>
                            <th width="15%" class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($iblocks as $iblock)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center me-3 opacity-50"
                                            style="width: 40px; height: 40px;">
                                            <i class="bi bi-file-text" style="font-size: 1rem;"></i>
                                        </div>
                                        <div class="opacity-75">
                                            <div class="fw-semibold">{{ $iblock->title }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="opacity-75">
                                    @if($iblock->author)
                                        <div class="d-flex align-items-center">
                                            <span class="small">{{ $iblock->author->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted small">Не указан</span>
                                    @endif
                                </td>
                                <td class="opacity-75">
                                    <div class="text-muted small">
                                        {{ $iblock->deleted_at->format('d.m.Y H:i') }}
                                        <div class="text-muted">
                                            {{ $iblock->deleted_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-actions justify-content-end">
                                        <form action="{{ route('admin.iblock.trash.restore', $iblock->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success btn-sm me-1" 
                                                    title="Восстановить">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-outline-danger btn-sm force-delete-btn"
                                                title="Удалить навсегда" data-iblock-id="{{ $iblock->id }}"
                                                data-iblock-title="{{ $iblock->title }}"
                                                data-force-url="{{ route('admin.iblock.trash.force', $iblock->id) }}"
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
    @else
    <!-- Пустая корзина -->
    <div class="card fade-in">
        <div class="card-body text-center py-5">
            <div class="text-muted">
                <i class="bi bi-trash fs-1 opacity-50"></i>
                <h4 class="mt-3">Корзина пуста</h4>
                <p class="mb-4">Удаленные информационные блоки будут отображаться здесь</p>
                <a href="{{ route('admin.iblock.index') }}" class="btn btn-primary">
                    <i class="bi bi-arrow-left me-2"></i> Вернуться к блокам
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Информационная панель -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> О корзине</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2" style="font-size: 0.85rem;">
                        В этом разделе вы можете управлять всеми удалёнными информационными блоками.
                    </p>
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li>Блоки которые находятся в корзине, не отображаются на сайте</li>
                        <li>Перемещённые блоки в корзину удаляются автоматически через 30 дней</li>
                        <li>После полного удаления блок нельзя восстановить</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

@endsection

<!-- Модальное окно полного удаления -->
<div class="modal fade" id="forceDeleteModal" tabindex="-1" aria-labelledby="forceDeleteModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="forceDeleteModalLabel">Полное удаление</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите полностью удалить информационный блок <strong id="iblockTitleToForceDelete"></strong>?</p>
                <div class="alert alert-danger alert-sm mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Это действие нельзя отменить. Все данные блока будут удалены безвозвратно.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form id="forceDeleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i> Удалить навсегда
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно очистки корзины -->
<div class="modal fade" id="emptyTrashModal" tabindex="-1" aria-labelledby="emptyTrashModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emptyTrashModalLabel">Очистка корзины</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите очистить всю корзину?</p>
                <div class="alert alert-danger alert-sm mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Это действие удалит <strong>{{ $trashedCount }}</strong> блоков безвозвратно. 
                    Отменить это действие будет невозможно.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form action="{{ route('admin.iblock.trash.empty') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i> Очистить корзину
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>