@extends('admin::layouts.default')

@section('title', 'Управление разделами каталога | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [['title' => 'Каталог'], ['title' => 'Разделы']],
        ])
    </div>

    <!-- Вкладки: Активные и Корзина -->
    <div class="d-flex mb-4 fade-in">
        <div class="btn-group" role="group">
            <a href="{{ route('catalog.sections.index') }}" class="btn btn-primary">
                <i class="bi bi-folder me-1"></i> Активные разделы
            </a>
            <a href="{{ route('catalog.sections.trash.index') }}" class="btn btn-outline-primary position-relative">
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

    <!-- Действия с разделами -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Управление разделами каталога</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Всего: {{ $totalSections }} | Активных: {{ $activeSections }} | В корзине: {{ $trashedSections }}
            </p>
        </div>
        <a href="{{ route('catalog.sections.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Добавить раздел
        </a>
    </div>

    <!-- Карточка с фильтрами -->
    <div class="card fade-in mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('catalog.sections.index') }}" class="row g-2">
                <!-- Поиск -->
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                            placeholder="Поиск по названию, URL или описанию...">
                    </div>
                </div>

                <!-- Статус -->
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Все статусы</option>
                        <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Активные</option>
                        <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Неактивные</option>
                    </select>
                </div>

                <!-- Сортировка -->
                <div class="col-md-3">
                    <select name="sort_by" class="form-select form-select-sm">
                        <option value="sort_order" {{ $sortBy == 'sort_order' ? 'selected' : '' }}>По порядку</option>
                        <option value="name" {{ $sortBy == 'name' ? 'selected' : '' }}>По названию</option>
                        <option value="created_at" {{ $sortBy == 'created_at' ? 'selected' : '' }}>По дате добавления</option>
                        <option value="updated_at" {{ $sortBy == 'updated_at' ? 'selected' : '' }}>По дате обновления</option>
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
                    <a href="{{ route('catalog.sections.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Список разделов -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Список разделов</h5>
                <div class="text-muted small">
                    Показано {{ $sections->count() }} из {{ $sections->total() }} разделов
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="35%">
                                <a href="{{ route('catalog.sections.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'name', 'sort_order' => $sortBy == 'name' && $sortOrder == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="text-decoration-none d-flex align-items-center">
                                    Название раздела
                                    @if ($sortBy == 'name')
                                        <i class="bi bi-chevron-{{ $sortOrder == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th width="15%">URL</th>
                            <th width="15%">Статус</th>
                            <th width="15%">Родительский раздел</th>
                            <th width="10%">Товары</th>
                            <th width="10%">Добавил</th>
                            <th width="15%" class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sections as $section)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3"
                                            style="width: 40px; height: 40px;">
                                            @if($section->parent_id)
                                                <i class="bi bi-folder-symlink"></i>
                                            @else
                                                <i class="bi bi-folder"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $section->name }}</div>
                                            <div class="text-muted small">
                                                ID: {{ $section->id }}
                                                @if($section->description)
                                                    <br>{{ Str::limit($section->description, 50) }}
                                                @endif
                                            </div>
                                            @if($section->children->count() > 0)
                                                <small class="text-primary">
                                                    <i class="bi bi-folder-plus me-1"></i>
                                                    {{ $section->children->count() }} подраздел(ов)
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code class="small">/{{ $section->slug }}</code>
                                </td>
                                <td>
                                    <form action="{{ route('catalog.sections.toggle-status', $section) }}" method="POST" class="toggle-status-form">
                                        @csrf
                                        <button type="button" 
                                                class="status-toggle-btn badge border-0 {{ $section->is_active ? 'bg-success' : 'bg-secondary' }}"
                                                data-section-id="{{ $section->id }}"
                                                data-is-active="{{ $section->is_active }}">
                                            {{ $section->is_active ? 'Активен' : 'Неактивен' }}
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    @if($section->parent)
                                        <span class="small">{{ $section->parent->name }}</span>
                                    @else
                                        <span class="text-muted small">Корневой раздел</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $section->goods->count() }}</span>
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
                                        <a href="{{ route('catalog.sections.edit', $section) }}"
                                            class="btn btn-outline-primary btn-sm me-1" title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm delete-section-btn"
                                            title="В корзину" data-section-id="{{ $section->id }}"
                                            data-section-name="{{ $section->name }}"
                                            data-delete-url="{{ route('catalog.sections.destroy', $section) }}"
                                            data-bs-toggle="modal" data-bs-target="#deleteSectionModal">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-folder-x fs-4"></i>
                                        <p class="mt-2">Разделы не найдены</p>
                                        @if (request()->hasAny(['search', 'status']))
                                            <a href="{{ route('catalog.sections.index') }}" class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-arrow-clockwise me-1"></i> Сбросить фильтры
                                            </a>
                                        @else
                                            <a href="{{ route('catalog.sections.create') }}"
                                                class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-plus-circle me-1"></i> Добавить первый раздел
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
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> О разделах каталога</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2" style="font-size: 0.85rem;">
                        Разделы позволяют организовать структуру каталога товаров в виде дерева.
                    </p>
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li>Создавайте иерархическую структуру разделов и подразделов</li>
                        <li>Настраивайте SEO-параметры для каждого раздела</li>
                        <li>Управляйте видимостью разделов (активен/неактивен)</li>
                        <li>Привязывайте товары к конкретным разделам</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card api-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0"><i class="bi bi-code-slash me-2"></i> API разделов</h6>
                    </div>
                </div>
                <div class="card-body">
                    <!-- API разделов -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-grow-1">
                            <div class="fw-semibold mb-1" style="font-size: 0.85rem;">
                                <i class="bi bi-link-45deg me-1"></i> API разделов каталога
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <code class="p-2 bg-light rounded small api-endpoint flex-grow-1" title="{{ url('api/catalog/sections') }}">
                                    {{ url('api/catalog/sections') }}
                                </code>
                                <div class="d-flex gap-1">
                                    <a href="{{ url('api/catalog/sections') }}" target="_blank" 
                                       class="btn btn-outline-primary btn-sm copy-btn" 
                                       title="Открыть API в новой вкладке">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    <button class="btn btn-outline-secondary btn-sm copy-btn" 
                                            data-clipboard-text="{{ url('api/catalog/sections') }}"
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
                                <span class="api-endpoint flex-grow-1" title="{{ url('api/documentation#catalog') }}">
                                    {{ url('api/documentation#catalog') }}
                                </span>
                                <div class="d-flex gap-1">
                                    <a href="{{ url('api/documentation#catalog') }}" target="_blank" 
                                       class="btn btn-outline-info btn-sm copy-btn" 
                                       title="Открыть документацию в новой вкладке">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    <button class="btn btn-outline-secondary btn-sm copy-btn" 
                                            data-clipboard-text="{{ url('api/documentation#catalog') }}"
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
<div class="modal fade" id="deleteSectionModal" tabindex="-1" aria-labelledby="deleteSectionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSectionModalLabel">Перемещение в корзину</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите переместить раздел <strong id="sectionNameToDelete"></strong> в корзину?</p>
                <div class="alert alert-warning alert-sm mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <div>
                        <strong>Внимание:</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            <li>Убедитесь, что в разделе нет товаров</li>
                            <li>Раздел не должен содержать подразделов</li>
                            <li>Раздел будет доступен в корзине для восстановления в течение 30 дней</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form id="deleteSectionForm" method="POST" class="d-inline">
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

@push('styles')
<style>
    .status-toggle-btn {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .status-toggle-btn:hover {
        opacity: 0.8;
        transform: scale(1.05);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Обработка кнопок удаления
        const deleteButtons = document.querySelectorAll('.delete-section-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const sectionId = this.getAttribute('data-section-id');
                const sectionName = this.getAttribute('data-section-name');
                const deleteUrl = this.getAttribute('data-delete-url');
                
                document.getElementById('sectionNameToDelete').textContent = sectionName;
                document.getElementById('deleteSectionForm').action = deleteUrl;
            });
        });
        
        // Обработка переключения статуса
        const statusButtons = document.querySelectorAll('.status-toggle-btn');
        statusButtons.forEach(button => {
            button.addEventListener('click', function() {
                const sectionId = this.getAttribute('data-section-id');
                const isActive = this.getAttribute('data-is-active') === '1';
                const form = this.closest('.toggle-status-form');
                
                if (confirm('Вы уверены, что хотите изменить статус раздела?')) {
                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Обновляем кнопку
                            this.setAttribute('data-is-active', data.data.is_active);
                            this.textContent = data.data.status_text;
                            this.className = 'status-toggle-btn badge border-0 ' + data.data.status_class;
                            
                            // Показываем уведомление
                            showNotification('Статус раздела успешно изменен', 'success');
                        } else {
                            showNotification('Ошибка при изменении статуса', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Ошибка при изменении статуса', 'error');
                    });
                }
            });
        });
        
        function showNotification(message, type = 'info') {
            // Реализация уведомлений зависит от вашей системы
            alert(message);
        }
    });
</script>
@endpush