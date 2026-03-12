@extends('admin::layouts.default')

@section('title', 'Редактировать покупателя | KotiksCMS')

@section('content')
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Покупатели', 'url' => route('catalog.customers.index')],
                ['title' => 'Редактировать покупателя'],
            ],
        ])
    </div>

    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Редактировать покупателя</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">Пользователь: {{ $customer->user?->name }}</p>
        </div>
        <a href="{{ route('catalog.customers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>

    <div class="row fade-in">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Основные данные</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('catalog.customers.update', $customer->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="user_id" class="form-label required">Пользователь</label>
                                <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                    <option value="">Выберите пользователя</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id', $customer->user_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="type_id" class="form-label required">Тип покупателя</label>
                                <select name="type_id" id="type_id" class="form-select @error('type_id') is-invalid @enderror" required>
                                    <option value="">Выберите тип</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}" {{ old('type_id', $customer->type_id) == $type->id ? 'selected' : '' }}>
                                            {{ $type->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        @if($customer->trashed())
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="bi bi-exclamation-triangle"></i> Эта запись находится в корзине.
                                Чтобы восстановить, используйте кнопку "Восстановить" в списке.
                            </div>
                        @endif

                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Сохранить изменения
                            </button>
                            <a href="{{ route('catalog.customers.index') }}" class="btn btn-outline-secondary ms-2">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-person-circle"></i> Информация о записи</h6>
                </div>
                <div class="card-body">
                    <dl class="mb-0 small">
                        <dt>ID</dt>
                        <dd>#{{ $customer->id }}</dd>
                        <dt>Создан</dt>
                        <dd>{{ $customer->created_at?->format('d.m.Y H:i') ?? '—' }}</dd>
                        <dt>Обновлён</dt>
                        <dd>{{ $customer->updated_at?->format('d.m.Y H:i') ?? '—' }}</dd>
                        @if($customer->deleted_at)
                            <dt>Удалён</dt>
                            <dd>{{ $customer->deleted_at->format('d.m.Y H:i') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection