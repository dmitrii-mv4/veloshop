@extends('admin::layouts.default')

@section('title', 'Создание товара | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Каталог', 'url' => route('catalog.index')],
                ['title' => 'Товары', 'url' => route('catalog.goods.index')],
                ['title' => 'Создание товара']
            ],
        ])
    </div>

    <!-- Заголовок формы -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Создание нового товара</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Заполните форму ниже для добавления нового товара в каталог
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('catalog.goods.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    <!-- Форма создания товара -->
    <form action="{{ route('catalog.goods.store') }}" method="POST">
        @csrf
        
        <div class="row fade-in">
            <!-- Основные поля -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-pencil-square me-2"></i> Основная информация</h6>
                    </div>
                    <div class="card-body">
                        <!-- Название товара -->
                        <div class="mb-4">
                            <label for="title" class="form-label required">
                                Название товара
                            </label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}" 
                                   required
                                   maxlength="255"
                                   placeholder="Введите полное название товара">
                            <div class="char-counter mt-1">
                                <span id="title-counter">0</span>/255 символов
                            </div>
                            @error('title')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Артикул -->
                        <div class="mb-3">
                            <label for="articul" class="form-label required">
                                Артикул
                            </label>
                            <input type="text" 
                                   class="form-control @error('articul') is-invalid @enderror" 
                                   id="articul" 
                                   name="articul" 
                                   value="{{ old('articul') }}" 
                                   required
                                   maxlength="100"
                                   placeholder="Введите уникальный артикул товара">
                            <div class="char-counter mt-1">
                                <span id="articul-counter">0</span>/100 символов
                            </div>
                            @error('articul')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Боковая панель -->
            <div class="col-lg-4">
                <!-- Действия -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-send me-2"></i> Действия</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info alert-sm mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <small>Товар будет создан от вашего имени как автора</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small">Автор</label>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-2"
                                     style="width: 32px; height: 32px;">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ auth()->user()->name }}</div>
                                    <small class="text-muted">{{ auth()->user()->email }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i> Сохранить товар
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise me-2"></i> Очистить форму
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Счетчики символов
        const titleInput = document.getElementById('title');
        const articulInput = document.getElementById('articul');
        const titleCounter = document.getElementById('title-counter');
        const articulCounter = document.getElementById('articul-counter');
        
        function updateCounter(input, counter) {
            counter.textContent = input.value.length;
        }
        
        titleInput.addEventListener('input', () => updateCounter(titleInput, titleCounter));
        articulInput.addEventListener('input', () => updateCounter(articulInput, articulCounter));
        
        // Инициализация счетчиков
        updateCounter(titleInput, titleCounter);
        updateCounter(articulInput, articulCounter);
        
        // Очистка формы
        document.querySelector('button[type="reset"]').addEventListener('click', function() {
            setTimeout(() => {
                updateCounter(titleInput, titleCounter);
                updateCounter(articulInput, articulCounter);
            }, 0);
        });
    });
</script>
@endpush