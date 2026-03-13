@extends('admin::layouts.default')

@section('title', 'Список модулей | KotiksCMS')

@section('content')
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [['title' => 'Список модулей']]
        ])
    </div>

    <!-- Карточка со списком модулей -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Список модулей</h5>
                <span class="text-muted small">Обнаружено модулей: {{ count($modules) }}</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Название модуля</th>
                            <th>Версия</th>
                            <th>Автор</th>
                            <th>Системный</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($modules as $moduleName => $module)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $module['module']['title'] ?? $moduleName }}</div>
                                    @if(!empty($module['module']['description']))
                                        <div class="text-muted small mt-1">{{ $module['module']['description'] }}</div>
                                    @endif
                                </td>
                                <td>{{ $module['module']['version'] ?? '—' }}</td>
                                <td>{{ $module['module']['author'] ?? '—' }}</td>
                                <td>
                                    @if($module['module']['system'] ?? false)
                                        <span class="badge bg-warning">Да</span>
                                    @else
                                        <span class="badge bg-secondary">Нет</span>
                                    @endif
                                </td>
                                <td>
                                    @if($module['module']['enabled'] ?? false)
                                        <span class="badge bg-success">Активен</span>
                                    @else
                                        <span class="badge bg-secondary">Неактивен</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox fs-1"></i>
                                        <p class="mt-3">Модули не найдены</p>
                                        <p class="small">Проверьте наличие директорий в <code>app/Modules/</code></p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection