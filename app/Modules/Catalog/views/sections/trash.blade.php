@extends('admin::layouts.default')

@section('title', 'Корзина разделов каталога | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Каталог', 'url' => route('catalog.index')],
                ['title' => 'Разделы', 'url' => route('catalog.sections.index')],
                ['title' => 'Корзина']
            ],
        ])
    </div>

    <!-- Вкладки: Активные и Корзина -->
    <div class="d-flex mb-4 fade-in">
        <div class="btn-group" role="group">
            <a href="{{ route('catalog.sections.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-folder me-1"></i> Активные разделы
            </a>
            <a href="{{ route('catalog.sections.trash.index') }}" class="btn btn-primary">
                <i class="bi bi-trash me-1"></i> Корзина
                @if($trashedSections > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ $trashedSections }}
                    <span class="visually-hidden">разделов в корзине</span>
                </span>
                @endif
            </a>
        </div>
    </div>

    <!-- Действия с корзиной -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Корзина разделов каталога</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Всего разделов: {{ $totalSections }} | Активных: {{ $activeSections }} | В корзине: {{ $trashedSections }}
            </p>
        </div>
        <div class="d-flex gap-2">
            @if($trashedSections > 0)
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#emptyTrashModal">
                    <i class="bi bi-trash3"></i> Очистить корзину
                </button>
            @endif
            <a href="{{ route('catalog.sections.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> К разделам
            </a>
        </div>
    </div>

    <!-- Карточка с фильтрами -->
    @if($trashedSections > 0)
    <div class="card fade-in mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('catalog.sections.trash.index') }}" class="row g-2">
                <!-- Поиск -->
                <div class="col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                            placeholder="Поиск по названию или URL...">
                    </div>
                </div>

                <!-- Сортировка -->
                <div class="col-md-3">
                    <select name="sort_by" class="form-select form-select-sm">
                        <option value="deleted_at" {{ $sortBy == 'deleted_at' ? 'selected' : '' }}>Дата удаления</option>
                        <option value="name" {{ $sortBy == 'name' ? 'selected' : '' }}>Название</option>
                        <option value="created_at" {{ $sortBy == 'created_at' ? 'selected' : '' }}>Дата создания</option>
                    </select>
                </div>

                <!-- Количество на странице -->
                <div class="col-md-2">
                    <select name="per_page" class="form-select form-select-sm">
                        @foreach ([10, 20, 50, 100] as $count)
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
                    <a href="{{ route('catalog.sections.trash.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Список разделов в корзине -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Разделы в корзине</h5>
                @if($trashedSections > 0)
                <div class="text-muted small">
                    Показано {{ $sections->count() }} из {{ $sections->total() }} разделов
                </div>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            @if($trashedSections > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="35%">
                                <a href="{{ route('catalog.sections.trash.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'name', 'sort_order' => $sortBy == 'name' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none d-flex align-items-center">
                                    Название раздела
                                    @if ($sortBy == 'name')
                                        <i class="bi bi-chevron-{{ $sortOrder == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="15%">URL</th>
                            <th width="15%">Родительский раздел</th>
                            <th width="15%">Дата удаления</th>
                            <th width="10%">Удалил</th>
                            <th width="15%" class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sections as $section)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center me-3"
                                            style="width: 40px; height: 40px;">
                                            <i class="bi bi-folder"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $section->name }}</div>
                                            <div class="text-muted small">
                                                ID: {{ $section->id }}
                                                @if($section->description)
                                                    <br>{{ Str::limit($section->description, 50) }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code class="small">/{{ $section->slug }}</code>
                                </td>
                                <td>
                                    @if($section->parent)
                                        <span class="small">{{ $section->parent->name }}</span>
                                    @else
                                        <span class="text-muted small">Корневой раздел</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="small" title="{{ $section->deleted_at }}">
                                        {{ $section->deleted_at->format('d.m.Y H:i') }}
                                    </span>
                                </td>
                                <td>
                                    @if($section->author)
                                        <div class="d-flex align-items-center">
                                            <span class="small">{{ $section->author->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted small">Не указан</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="table-actions justify-content-end">
                                        <form action="{{ route('catalog.sections.trash.restore', $section->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success btn-sm me-1" 
                                                    title="Восстановить" 
                                                    onclick="return confirm('Восстановить раздел {{ $section->name }}?')">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-outline-danger btn-sm force-delete-section-btn"
                                                title="Удалить полностью" 
                                                data-section-id="{{ $section->id }}"
                                                data-section-name="{{ $section->name }}"
                                                data-force-delete-url="{{ route('catalog.sections.trash.force', $section->id) }}"
                                                data-bs-toggle="modal" data-bs-target="#forceDeleteSectionModal">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <div class="text-muted">
                    <i class="bi bi-trash fs-1"></i>
                    <h5 class="mt-3">Корзина пуста</h5>
                    <p class="mb-4">Нет удаленных разделов</p>
                    <a href="{{ route('catalog.sections.index') }}" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-1"></i> Вернуться к разделам
                    </a>
                </div>
            </div>
            @endif
        </div>

        <!-- Пагинация -->
        @if ($sections->hasPages())
            <div class="card-footer border-0 bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Показано {{ $sections->firstItem() }} - {{ $sections->lastItem() }} из {{ $sections->total() }}
                    </div>
                    <div>
                        {{ $sections->links('admin::partials.pagination') }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Информационная панель -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> О корзине разделов</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2" style="font-size: 0.85rem;">
                        В корзине хранятся удаленные разделы. Вы можете восстановить разделы или удалить их окончательно.
                    </p>
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li><strong>Восстановление:</strong> Раздел будет возвращен в список активных разделов</li>
                        <li><strong>Окончательное удаление:</strong> Раздел будет полностью удален из системы</li>
                        <li><strong>Автоматическая очистка:</strong> Разделы в корзине хранятся 30 дней, после чего удаляются автоматически</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

<!-- Модальное окно очистки корзины -->
<div class="modal fade" id="emptyTrashModal" tabindex="-1" aria-labelledby="emptyTrashModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emptyTrashModalLabel">Очистка корзины</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите полностью очистить корзину разделов?</p>
                <div class="alert alert-danger alert-sm mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <div>
                        <strong>Внимание: Это действие нельзя отменить!</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            <li>Будет удалено {{ $trashedSections }} разделов</li>
                            <li>Все разделы будут удалены окончательно</li>
                            <li>Восстановление данных будет невозможно</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form action="{{ route('catalog.sections.trash.empty') }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash3 me-2"></i> Очистить корзину
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно полного удаления -->
<div class="modal fade" id="forceDeleteSectionModal" tabindex="-1" aria-labelledby="forceDeleteSectionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="forceDeleteSectionModalLabel">Полное удаление раздела</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите полностью удалить раздел <strong id="forceDeleteSectionName"></strong>?</p>
                <div class="alert alert-danger alert-sm mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <div>
                        <strong>Внимание: Это действие нельзя отменить!</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            <li>Раздел будет удален окончательно</li>
                            <li>Все данные раздела будут потеряны</li>
                            <li>Восстановление данных будет невозможно</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form id="forceDeleteSectionForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash3 me-2"></i> Удалить полностью
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Обработка кнопок полного удаления
        const forceDeleteButtons = document.querySelectorAll('.force-delete-section-btn');
        forceDeleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const sectionId = this.getAttribute('data-section-id');
                const sectionName = this.getAttribute('data-section-name');
                const forceDeleteUrl = this.getAttribute('data-force-delete-url');
                
                document.getElementById('forceDeleteSectionName').textContent = sectionName;
                document.getElementById('forceDeleteSectionForm').action = forceDeleteUrl;
            });
        });
    });
</script>
@endpush