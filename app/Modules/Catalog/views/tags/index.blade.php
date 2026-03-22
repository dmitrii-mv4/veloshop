@extends('admin::layouts.default')

@section('title', 'Управление тегами | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [['title' => 'Каталог', 'url' => route('catalog.index')], ['title' => 'Теги']],
        ])
    </div>

    <!-- Действия с тегами -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Управление тегами</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Всего тегов: {{ $totalTags }}
            </p>
        </div>
        <a href="{{ route('catalog.tags.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Добавить тег
        </a>
    </div>

    <!-- Карточка с фильтрами -->
    <div class="card fade-in mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('catalog.tags.index') }}" class="row g-2">
                <!-- Поиск -->
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                            placeholder="Поиск по названию или слагу...">
                    </div>
                </div>

                <!-- Сортировка -->
                <div class="col-md-2">
                    <select name="sort_by" class="form-select form-select-sm">
                        <option value="created_at" {{ $sortBy == 'created_at' ? 'selected' : '' }}>Дата создания</option>
                        <option value="name" {{ $sortBy == 'name' ? 'selected' : '' }}>Название</option>
                        <option value="slug" {{ $sortBy == 'slug' ? 'selected' : '' }}>Слаг</option>
                        <option value="updated_at" {{ $sortBy == 'updated_at' ? 'selected' : '' }}>Дата обновления</option>
                    </select>
                </div>

                <!-- Порядок сортировки -->
                <div class="col-md-2">
                    <select name="sort_order" class="form-select form-select-sm">
                        <option value="desc" {{ $sortOrder == 'desc' ? 'selected' : '' }}>По убыванию</option>
                        <option value="asc" {{ $sortOrder == 'asc' ? 'selected' : '' }}>По возрастанию</option>
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
                    <a href="{{ route('catalog.tags.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Таблица тегов -->
    <div class="card fade-in">
        <div class="card-body p-0">
            @if($tags->count() > 0)
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th>Название</th>
                            <th>Слаг</th>
                            <th style="width: 150px;">Использований</th>
                            <th style="width: 150px;">Дата создания</th>
                            <th style="width: 200px;" class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tags as $tag)
                            <tr>
                                <td class="text-muted">{{ $tag->id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $tag->name }}</div>
                                </td>
                                <td>
                                    <code class="text-primary">{{ $tag->slug }}</code>
                                </td>
                                <td>
                                    @php
                                        $productsCount = $tag->products()->count();
                                        $offersCount = $tag->offers()->count();
                                        $totalCount = $productsCount + $offersCount;
                                    @endphp
                                    @if($totalCount > 0)
                                        <span class="badge bg-primary">{{ $totalCount }}</span>
                                        <small class="text-muted ms-1">
                                            (Т: {{ $productsCount }}, П: {{ $offersCount }})
                                        </small>
                                    @else
                                        <span class="badge bg-secondary">0</span>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ $tag->created_at->format('d.m.Y H:i') }}
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('catalog.tags.edit', $tag->id) }}"
                                           class="btn btn-outline-primary"
                                           title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-outline-danger"
                                                onclick="confirmDelete({{ $tag->id }}, '{{ $tag->name }}')"
                                                title="Удалить">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>

                                    <!-- Форма удаления -->
                                    <form id="delete-form-{{ $tag->id }}"
                                          action="{{ route('catalog.tags.destroy', $tag->id) }}"
                                          method="POST"
                                          class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-tags text-muted" style="font-size: 3rem;"></i>
                    <h5 class="text-muted mt-3">Теги не найдены</h5>
                    <p class="text-muted">Создайте первый тег, нажав кнопку "Добавить тег"</p>
                </div>
            @endif
        </div>

        <!-- Пагинация -->
        @if($tags->hasPages())
            <div class="card-footer bg-light">
                {{ $tags->withQueryString()->links('vendor.pagination.bootstrap-5') }}
            </div>
        @endif
    </div>

    <!-- Модальное окно подтверждения удаления -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Подтверждение удаления</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Вы уверены, что хотите удалить тег <strong id="deleteTagName"></strong>?</p>
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Это действие нельзя отменить.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Удалить</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let deleteFormId = null;

    function confirmDelete(tagId, tagName) {
        deleteFormId = tagId;
        document.getElementById('deleteTagName').textContent = tagName;
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (deleteFormId) {
            document.getElementById('delete-form-' + deleteFormId).submit();
        }
    });

    // Подсветка сообщений об успехе/ошибке
    @if(session('success'))
        const toast = document.createElement('div');
        toast.className = 'position-fixed top-0 end-0 p-3';
        toast.style.zIndex = '11';
        toast.innerHTML = `
            <div class="toast show align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 5000);
    @endif

    @if(session('error'))
        const errorToast = document.createElement('div');
        errorToast.className = 'position-fixed top-0 end-0 p-3';
        errorToast.style.zIndex = '11';
        errorToast.innerHTML = `
            <div class="toast show align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        document.body.appendChild(errorToast);
        setTimeout(() => errorToast.remove(), 5000);
    @endif
</script>
@endpush

@push('styles')
<style>
    .table th {
        font-weight: 600;
        font-size: 0.875rem;
        color: #495057;
    }

    .table td {
        vertical-align: middle;
    }

    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
    }

    code {
        font-size: 0.875em;
        background-color: rgba(0, 123, 255, 0.1);
        padding: 0.2em 0.4em;
        border-radius: 0.25rem;
    }
</style>
@endpush
