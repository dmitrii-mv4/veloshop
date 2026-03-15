@extends('admin::layouts.default')

@section('title', isset($basket) ? 'Редактировать корзину' : 'Создать корзину')

@section('content')

    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Корзины', 'url' => route('catalog.basket.index')],
                ['title' => isset($basket) ? 'Редактирование корзины #'.$basket->id : 'Создание корзины'],
            ],
        ])
    </div>

    <!-- Действия -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">{{ isset($basket) ? 'Редактировать корзину' : 'Создать корзину' }}</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                @if(isset($basket))
                    Изменение состава корзины #{{ $basket->id }}
                @else
                    Заполните данные для новой корзины
                @endif
            </p>
        </div>
        <a href="{{ route('catalog.basket.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Вернуться к списку
        </a>
    </div>

    <!-- Форма -->
    <div class="row fade-in">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Основная информация</h6>
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="{{ isset($basket) ? route('catalog.basket.update', $basket->id) : route('catalog.basket.store') }}"
                          id="basketForm">
                        @csrf
                        @if(isset($basket))
                            @method('PUT')
                        @endif

                        <div class="row g-3">
                            <!-- Пользователь -->
                            <div class="col-md-6">
                                <label for="user_id" class="form-label">Пользователь (владелец)</label>
                                <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror">
                                    <option value="">Не выбран</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ old('user_id', $basket->user_id ?? '') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Покупатель -->
                            <div class="col-md-6">
                                <label for="customer_id" class="form-label">Покупатель (из каталога)</label>
                                <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                                    <option value="">Не выбран</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}"
                                            {{ old('customer_id', $basket->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->user->name ?? 'Покупатель #'.$customer->id }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Товары (офферы) -->
                            <div class="col-12">
                                <label class="form-label">Товары в корзине</label>
                                <div class="border rounded p-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                                    @foreach ($offers as $offer)
                                        <div class="form-check mb-2">
                                            <input type="checkbox"
                                                   name="offers[]"
                                                   value="{{ $offer->offer_id }}"
                                                   class="form-check-input"
                                                   id="offer_{{ $offer->offer_id }}"
                                                   {{ in_array($offer->offer_id, old('offers', $selectedOffers ?? [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="offer_{{ $offer->offer_id }}">
                                                {{ $offer->name }} 
                                                @if($offer->product)
                                                    <span class="text-muted">({{ $offer->product->name }})</span>
                                                @endif
                                                — {{ number_format($offer->getPrice(), 2) }} ₽
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('offers')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            @if(isset($basket))
                                <!-- Информация о корзине -->
                                <div class="col-12">
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">ID корзины</small>
                                            <span class="fw-semibold">#{{ $basket->id }}</span>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Дата создания</small>
                                            <span class="fw-semibold">{{ $basket->created_at->format('d.m.Y H:i') }}</span>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Количество товаров</small>
                                            <span class="fw-semibold">{{ $basket->total_quantity }}</span>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Общая сумма</small>
                                            <span class="fw-semibold">{{ number_format($basket->total_price, 2) }} ₽</span>
                                        </div>
                                        @if($basket->creator)
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Кто создал</small>
                                            <span class="fw-semibold">{{ $basket->creator->name }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Кнопки формы -->
                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i> {{ isset($basket) ? 'Сохранить изменения' : 'Создать корзину' }}
                            </button>
                            <a href="{{ route('catalog.basket.index') }}" class="btn btn-outline-secondary ms-2">
                                Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Боковая панель -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> О корзине</h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        Корзина может быть привязана к пользователю системы (user_id) или к покупателю каталога (customer_id). 
                        Товары добавляются из списка активных предложений.
                    </p>
                    <p class="small mb-0">
                        Общая стоимость и количество пересчитываются автоматически при сохранении.
                    </p>
                </div>
            </div>
        </div>
    </div>

@endsection