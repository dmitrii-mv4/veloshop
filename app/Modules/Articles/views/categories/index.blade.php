@extends('admin::layouts.default')

@section('title', 'Категории статей | KotiksCMS')

@section('content')
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Статьи', 'url' => route('admin.articles.index')],
                ['title' => 'Категории'],
            ],
        ])
    </div>

    <!-- Вкладки: Активные, Категории, Корзина -->
    <div class="btn-group" role="group">
        <a href="{{ route('admin.articles.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-gift"></i> Активные статьи
        </a>
        <a href="{{ route('admin.articles.categories.index') }}" class="btn btn-primary">
            <i class="bi bi-tags me-1"></i> Категории
        </a>
    </div>

    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Категории статей</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Всего: {{ $categories->total() }}
            </p>
        </div>
        <a href="{{ route('admin.articles.categories.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Создать категорию
        </a>
    </div>

    <!-- Фильтры -->
    <div class="card fade-in mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.articles.categories.index') }}" class="row g-2">
                <div class="col-md-8">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                               placeholder="Поиск по названию или описанию...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="per_page" class="form-select form-select-sm">
                        @foreach ([10, 20, 50, 100] as $count)
                            <option value="{{ $count }}" {{ request('per_page', 20) == $count ? 'selected' : '' }}>
                                {{ $count }} на странице
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-funnel me-1"></i> Применить
                    </button>
                    <a href="{{ route('admin.articles.categories.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Список категорий -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Категории</h5>
                <div class="text-muted small">
                    Показано {{ $categories->count() }} из {{ $categories->total() }}
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="30%">Название</th>
                            <th width="20%">Символьный код</th>
                            <th width="30%">Описание</th>
                            <th width="10%">Статей</th>
                            <th width="10%" class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $category->title }}</div>
                                </td>
                                <td><code>{{ $category->slug }}</code></td>
                                <td>{{ Str::limit($category->description, 50) }}</td>
                                <td>{{ $category->articles()->count() }}</td>
                                <td>
                                    <div class="table-actions justify-content-end">
                                        <a href="{{ route('admin.articles.categories.edit', $category) }}"
                                           class="btn btn-outline-primary btn-sm me-1" title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm delete-category-btn"
                                                title="Удалить"
                                                data-category-id="{{ $category->id }}"
                                                data-category-title="{{ $category->title }}"
                                                data-delete-url="{{ route('admin.articles.categories.destroy', $category) }}"
                                                data-bs-toggle="modal" data-bs-target="#deleteCategoryModal">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-tags fs-4"></i>
                                        <p class="mt-2">Категории не найдены</p>
                                        @if(request()->has('search'))
                                            <a href="{{ route('admin.articles.categories.index') }}" class="btn btn-primary btn-sm mt-2">
                                                Сбросить фильтры
                                            </a>
                                        @else
                                            <a href="{{ route('admin.articles.categories.create') }}" class="btn btn-primary btn-sm mt-2">
                                                Создать первую категорию
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

        @if($categories->hasPages())
            <div class="card-footer border-0 bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Показано {{ $categories->firstItem() }} - {{ $categories->lastItem() }} из {{ $categories->total() }}
                    </div>
                    <div>
                        {{ $categories->links('admin::partials.pagination') }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Модальное окно подтверждения удаления -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Удаление категории</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Вы уверены, что хотите удалить категорию <strong id="categoryTitleToDelete"></strong>?</p>
                    <div class="alert alert-danger alert-sm mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Это действие удалит категорию. Статьи останутся, но потеряют связь с этой категорией.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <form id="deleteCategoryForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i> Удалить
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete-category-btn');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const title = this.dataset.categoryTitle;
                const url = this.dataset.deleteUrl;
                document.getElementById('categoryTitleToDelete').textContent = title;
                document.getElementById('deleteCategoryForm').action = url;
            });
        });
    });
</script>
@endpush