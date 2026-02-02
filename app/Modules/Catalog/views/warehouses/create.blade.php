@extends('admin::layouts.default')

@section('title', 'Добавление склада | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Каталог', 'url' => route('catalog.index')],
                ['title' => 'Склады', 'url' => route('catalog.warehouses.index')],
                ['title' => 'Добавление склада'],
            ],
        ])
    </div>

    <!-- Вкладки: Товары, Предложения, Склады -->
    <div class="d-flex mb-4 fade-in">
        <div class="btn-group" role="group">
            <a href="{{ route('catalog.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-box-seam me-1"></i> Товары
            </a>
            <a href="{{ route('catalog.warehouses.index') }}" class="btn btn-primary">
                <i class="bi bi-house-door me-1"></i> Склады
            </a>
            <a href="{{ route('catalog.statistics') }}" class="btn btn-outline-primary">
                <i class="bi bi-graph-up me-1"></i> Статистика
            </a>
        </div>
    </div>

    <!-- Основной контент -->
    <div class="row fade-in">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-house-door-add me-2"></i> Добавление нового склада
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('catalog.warehouses.store') }}" method="POST">
                        @csrf

                        <!-- Название склада -->
                        <div class="mb-4">
                            <label for="title" class="form-label required">
                                Название склада
                            </label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}"
                                   placeholder="Введите название склада"
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Укажите уникальное название склада для идентификации
                            </div>
                        </div>

                        <!-- Описание склада -->
                        <div class="mb-4">
                            <label for="description" class="form-label">
                                Описание склада
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4"
                                      placeholder="Введите описание склада (адрес, особенности и т.д.)">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Дополнительная информация о складе
                            </div>
                        </div>

                        <!-- Контактная информация -->
                        <div class="mb-4">
                            <label for="contacts" class="form-label">
                                Контактная информация
                            </label>
                            <textarea class="form-control @error('contacts') is-invalid @enderror" 
                                      id="contacts" 
                                      name="contacts" 
                                      rows="3"
                                      placeholder="Введите контактную информацию (телефоны, ответственные лица и т.д.)">{{ old('contacts') }}</textarea>
                            @error('contacts')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Контакты для связи по вопросам склада
                            </div>
                        </div>

                        <!-- Статус активности -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input @error('is_active') is-invalid @enderror" 
                                       type="checkbox" 
                                       role="switch" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Склад активен
                                </label>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">
                                Неактивные склады не отображаются при выборе в заказах
                            </div>
                        </div>

                        <!-- Кнопки формы -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('catalog.warehouses.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Отмена
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Создать склад
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Боковая панель с подсказками -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-lightbulb me-2"></i> Советы по заполнению
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Название склада</strong> должно быть уникальным и понятным для всех сотрудников.
                    </div>
                    
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        При создании <strong>неактивного</strong> склада он не будет доступен для выбора при оформлении заказов.
                    </div>

                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle me-2"></i>
                        После создания склада вы сможете добавлять на него товары и отслеживать остатки.
                    </div>
                </div>
            </div>

            <!-- Статистика -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-bar-chart me-2"></i> Статистика
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                            <i class="bi bi-house-door fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">{{ $totalWarehouses ?? 0 }} складов</div>
                            <div class="text-muted small">в системе</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection