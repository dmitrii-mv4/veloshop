@extends('admin::layouts.default')

@section('title', admin_trans('app.modules.module_generator') . ' | KotiksCMS')

@section('content')
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => admin_trans('app.modules.module_generator')]
            ]
        ])
    </div>

    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">{{ admin_trans('app.modules.managing_system_modules') }}</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">{{ admin_trans('app.modules.description') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.module_generator.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Создать новый модуль
            </a>
        </div>
    </div>

    <!-- Панель управления и поиск -->
    <div class="card fade-in mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.module_generator.index') }}" class="row g-2">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Поиск по названию модуля или описанию..." 
                               value="{{ $search }}" aria-label="Поиск">
                        @if($search)
                            <a href="{{ route('admin.module_generator.index') }}" class="btn btn-outline-secondary" 
                               title="Очистить поиск">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="per_page" class="form-select" onchange="this.form.submit()">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 на страницу</option>
                        <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15 на страницу</option>
                        <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 на страницу</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 на страницу</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 на страницу</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-funnel"></i> Применить
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Список модулей -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="modules-list-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Список модулей</h5>
                <div class="text-muted small">
                    Показано {{ $modules->count() }} из {{ $modules->total() }} модулей
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="modules-table">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="30%">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'code_module', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        Название модуля
                                        @if(request('sort') == 'code_module')
                                            <i class="bi bi-chevron-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="15%">Статус</th>
                                <th width="20%">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        Дата создания
                                        @if(request('sort') == 'created_at')
                                            <i class="bi bi-chevron-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="25%" class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($modules as $module)
                                <tr>
                                    <td>
                                        <div class="module-name fw-semibold">{{ $module->code_module }}</div>
                                        <div class="module-description text-muted small mt-1">
                                            {{ $module->description ?? 'Без описания' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($module->status == 'active')
                                            <span class="badge bg-success-subtle text-success">
                                                <i class="bi bi-check-circle me-1"></i> Активен
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">
                                                <i class="bi bi-x-circle me-1"></i> Отключен
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-muted small">
                                            <div>{{ $module->created_at->format('d.m.Y') }}</div>
                                            <div class="text-muted">{{ $module->created_at->format('H:i') }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-actions d-flex justify-content-end gap-1">
                                            @if($module->status == 'active')
                                                <a href="{{ route('admin.' . $module->code_module . '.index') }}"
                                                   class="btn btn-outline-primary btn-sm"
                                                   title="Перейти в модуль" data-bs-toggle="tooltip">
                                                    <i class="bi bi-box-arrow-in-right"></i>
                                                </a>
                                            @endif
                                            <form action="{{ route('admin.module_generator.delete', $module->code_module) }}" 
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Вы уверены, что хотите удалить этот модуль? Это действие нельзя отменить.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                        title="Удалить" data-bs-toggle="tooltip">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox fs-1"></i>
                                            <p class="mt-3">Модули не найдены</p>
                                            @if($search)
                                                <p class="small">Попробуйте изменить параметры поиска</p>
                                                <a href="{{ route('admin.module_generator.index') }}" class="btn btn-outline-secondary btn-sm mt-2">
                                                    <i class="bi bi-arrow-counterclockwise"></i> Сбросить поиск
                                                </a>
                                            @endif
                                            <a href="{{ route('admin.module_generator.create') }}" class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-plus-circle me-1"></i> Создать первый модуль
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Пагинация -->
        @if($modules->hasPages())
            <div class="card-footer border-top-0 bg-light">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div class="text-muted small">
                        Показано с {{ $modules->firstItem() }} по {{ $modules->lastItem() }} из {{ $modules->total() }} модулей
                    </div>
                    <nav aria-label="Навигация по страницам" class="mt-2 mt-md-0">
                        <ul class="pagination pagination-sm mb-0 justify-content-center">
                            {{-- Первая страница --}}
                            <li class="page-item {{ $modules->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $modules->url(1) }}" aria-label="Первая">
                                    <i class="bi bi-chevron-double-left"></i>
                                </a>
                            </li>
                            
                            {{-- Предыдущая страница --}}
                            <li class="page-item {{ $modules->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $modules->previousPageUrl() }}" aria-label="Предыдущая">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            
                            {{-- Номера страниц --}}
                            @php
                                $current = $modules->currentPage();
                                $last = $modules->lastPage();
                                $start = max($current - 2, 1);
                                $end = min($current + 2, $last);
                                
                                if ($start > 1) {
                                    $start = max($current - 1, 1);
                                    $end = min($current + 1, $last);
                                }
                                
                                if ($end == $last && $last - $start < 2) {
                                    $start = max($last - 2, 1);
                                }
                            @endphp
                            
                            @if($start > 1)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                            
                            @for($page = $start; $page <= $end; $page++)
                                <li class="page-item {{ $page == $current ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $modules->url($page) }}">{{ $page }}</a>
                                </li>
                            @endfor
                            
                            @if($end < $last)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                            
                            {{-- Следующая страница --}}
                            <li class="page-item {{ !$modules->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $modules->nextPageUrl() }}" aria-label="Следующая">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                            
                            {{-- Последняя страница --}}
                            <li class="page-item {{ !$modules->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $modules->url($last) }}" aria-label="Последняя">
                                    <i class="bi bi-chevron-double-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        @endif
    </div>

    <!-- Информационная панель -->
    <div class="row mt-4 fade-in">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i>О генераторе модулей</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2" style="font-size: 0.85rem;">Генератор модулей позволяет создавать и управлять
                        функциональными модулями для расширения возможностей вашей системы.</p>
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li>Создавайте новые модули с помощью конструктора</li>
                        <li>Управляйте существующими модулями</li>
                        <li>Активируйте и деактивируйте модули по необходимости</li>
                        <li>Используйте пагинацию для удобной навигации по списку модулей</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-lightbulb me-2"></i>Советы по использованию</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li>Используйте поиск для быстрого нахождения модулей</li>
                        <li>Настройте количество элементов на странице под свои нужды</li>
                        <li>Перед удалением модуля убедитесь, что он не используется в системе</li>
                        <li>Отключенные модули не видны в основном интерфейсе</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection