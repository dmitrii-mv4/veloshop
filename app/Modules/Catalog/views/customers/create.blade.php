@extends('admin::layouts.default')

@section('title', 'Новый покупатель | KotiksCMS')

@section('content')
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Покупатели', 'url' => route('catalog.customers.index')],
                ['title' => 'Создать покупателя'],
            ],
        ])
    </div>

    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Новый покупатель</h1>
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
                    <form method="POST" action="{{ route('catalog.customers.store') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="user_id" class="form-label required">Пользователь</label>
                                <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                    <option value="">Выберите пользователя</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
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
                                        <option value="{{ $type->id }}" {{ old('type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Создать
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
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle"></i> Информация</h6>
                </div>
                <div class="card-body">
                    <p>Привязка к пользователю позволяет использовать данные покупателя в заказах и личном кабинете.</p>
                    <p>Тип покупателя определяет набор полей и поведение (физлицо/юрлицо).</p>
                </div>
            </div>
        </div>
    </div>
@endsection