@extends('admin::layouts.default')

@section('title', 'Новый тип покупателя | KotiksCMS')

@section('content')
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Типы покупателей', 'url' => route('catalog.customers.type.index')],
                ['title' => 'Создать тип'],
            ],
        ])
    </div>

    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Новый тип покупателя</h1>
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
                    <form method="POST" action="{{ route('catalog.customers.type.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="title" class="form-label required">Название типа</label>
                            <input type="text" name="title" id="title"
                                   value="{{ old('title') }}"
                                   class="form-control @error('title') is-invalid @enderror"
                                   placeholder="Физическое лицо, Юридическое лицо ..." required>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}
                                       class="form-check-input @error('is_active') is-invalid @enderror">
                                <label for="is_active" class="form-check-label">
                                    Активен (доступен для выбора)
                                </label>
                                @error('is_active') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Создать
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
                    <h6 class="card-title mb-0"><i class="bi bi-tag"></i> Описание</h6>
                </div>
                <div class="card-body">
                    <p>Тип покупателя влияет на набор полей в профиле и поведение при оформлении заказа.</p>
                    <p>Примеры: Физическое лицо, Юридическое лицо, Индивидуальный предприниматель.</p>
                    <p class="mb-0">Неактивные типы не показываются на сайте.</p>
                </div>
            </div>
        </div>
    </div>
@endsection