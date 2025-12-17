@extends('admin::layouts.default')

@section('content')

    <!-- Заголовок страницы -->
    <div class="page-header fade-in">

        <!-- Обертка для breadcrumb с белым фоном -->
        <div class="breadcrumb-wrapper fade-in">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb-custom">
                    <li class="breadcrumb-item">
                        <a href="/">
                            <span class="breadcrumb-home-icon">
                                <i class="bi bi-house-door"></i>
                            </span>
                            {{ trans('app.dashboard') }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ trans('app.modules.module_generator') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Действия с модулями -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">{{ trans('app.modules.managing_system_modules') }}</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">{{ trans('app.modules.description') }}</p>
        </div>
        <a href="{{ route('admin.module_generator.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Создать новый модуль
        </a>
    </div>

    <!-- Список модулей -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="modules-list-header">
                <h5 class="card-title mb-0">Список модулей</h5>
                <div class="modules-count">Показано {{ $modules->count() }} из {{ $modules->total() }} модулей</div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="modules-table">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="30%">Название модуля</th>
                            <th width="15%">Статус</th>
                            <th width="15%">Дата создания</th>
                            <th width="25%" class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($modules as $module)

                        {{-- {{dd($module)}} --}}
                            <tr>
                                <td>
                                    <div class="module-name">{{ $module->code_module}}</div>
                                    <div class="module-description">{{ $module->description ?? 'Без описания' }}</div>
                                </td>
                                <td>
                                    @if ($module->status == 'active')
                                        <span class="module-status status-active">
                                            <i class="bi bi-check-circle"></i> Активен
                                        </span>
                                    @else
                                        <span class="module-status status-disabled">
                                            <i class="bi bi-x-circle"></i> Отключен
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted"
                                        style="font-size: 0.85rem;">{{ $module->created_at->format('d.m.Y H:i') }} </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('admin.' . $module->code_module . '.index') }}"
                                            class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-box-arrow-in-right me-1"></i> Перейти
                                        </a>
                                        <form action="{{ route('admin.module_generator.delete', $module->code_module) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm delete-module-btn">
                                                <i class="bi bi-trash me-1"></i> Удалить
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox fs-4"></i>
                                        <p class="mt-2">Модули не найдены</p>
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
    {{-- @if ($modules->hasPages()) --}}
        <div class="pagination-container fade-in">
            <nav aria-label="Навигация по страницам">
                <ul class="pagination pagination-custom">
                    {{-- Previous Page Link --}}
                    {{-- @if ($modules->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link">
                                <i class="bi bi-chevron-left"></i>
                            </span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $modules->previousPageUrl() }}">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                    @endif --}}

                    {{-- Pagination Elements --}}
                    {{-- @foreach (range(1, $modules->lastPage()) as $page)
                        @if ($page == $modules->currentPage())
                            <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                        @elseif($page >= $modules->currentPage() - 2 && $page <= $modules->currentPage() + 2)
                            <li class="page-item"><a class="page-link"
                                    href="{{ $modules->url($page) }}">{{ $page }}</a></li>
                        @endif
                    @endforeach --}}

                    {{-- Next Page Link --}}
                    {{-- @if ($modules->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $modules->nextPageUrl() }}">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    @else
                        <li class="page-item disabled">
                            <span class="page-link">
                                <i class="bi bi-chevron-right"></i>
                            </span>
                        </li>
                    @endif --}}
                </ul>
            </nav>
        </div>
    {{-- @endif --}}

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
                    </ul>
                </div>
            </div>
        </div>
    </div>

@endsection
