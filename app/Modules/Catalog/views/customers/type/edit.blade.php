@extends('admin::layouts.default')

@section('title', 'Редактировать тип покупателя | KotiksCMS')

@section('content')
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Типы покупателей', 'url' => route('catalog.customers.type.index')],
                ['title' => 'Редактировать тип'],
            ],
        ])
    </div>

    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Редактировать тип покупателя</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">{{ $type->title }}</p>
        </div>
        <a href="{{ route('catalog.customers.type.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>

    <div class="row fade-in">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Основная информация</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('catalog.customers.type.update', $type->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="title" class="form-label required">Название типа</label>
                            <input type="text" name="title" id="title"
                                   value="{{ old('title', $type->title) }}"
                                   class="form-control @error('title') is-invalid @enderror"
                                   placeholder="Физическое лицо, Юридическое лицо ..." required>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                       {{ old('is_active', $type->is_active) ? 'checked' : '' }}
                                       class="form-check-input @error('is_active') is-invalid @enderror">
                                <label for="is_active" class="form-check-label">
                                    Активен (доступен для выбора)
                                </label>
                                @error('is_active') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        @if($type->trashed())
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="bi bi-exclamation-triangle"></i> Эта запись находится в корзине.
                            </div>
                        @endif

                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Сохранить изменения
                            </button>
                            <a href="{{ route('catalog.customers.type.index') }}" class="btn btn-outline-secondary ms-2">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle"></i> Информация</h6>
                </div>
                <div class="card-body">
                    <dl class="mb-0 small">
                        <dt>ID</dt>
                        <dd>#{{ $type->id }}</dd>
                        <dt>Создан</dt>
                        <dd>{{ $type->created_at?->format('d.m.Y H:i') ?? '—' }}</dd>
                        <dt>Обновлён</dt>
                        <dd>{{ $type->updated_at?->format('d.m.Y H:i') ?? '—' }}</dd>
                        @if($type->deleted_at)
                            <dt>Удалён</dt>
                            <dd>{{ $type->deleted_at->format('d.m.Y H:i') }}</dd>
                        @endif
                        <dt>Статус активности</dt>
                        <dd>
                            @if($type->is_active)
                                <span class="badge bg-success">Активен</span>
                            @else
                                <span class="badge bg-secondary">Неактивен</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection