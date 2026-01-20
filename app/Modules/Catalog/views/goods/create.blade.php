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

                        <!-- Раздел -->
                        <div class="mb-3">
                            <label for="section_id" class="form-label">
                                <i class="bi bi-folder me-1"></i> Раздел каталога
                            </label>
                            <select class="form-select @error('section_id') is-invalid @enderror" 
                                    id="section_id" 
                                    name="section_id">
                                <option value="">— Без раздела —</option>
                                @foreach($sections as $id => $name)
                                    <option value="{{ $id }}" {{ old('section_id') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                <small>Выберите раздел каталога для классификации товара</small>
                            </div>
                            @error('section_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Мета-информация -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-search me-2"></i> SEO-настройки</h6>
                    </div>
                    <div class="card-body">
                        <!-- Мета-заголовок -->
                        <div class="mb-3">
                            <label for="meta_title" class="form-label">Мета-заголовок (title)</label>
                            <input type="text" 
                                class="form-control @error('meta_title') is-invalid @enderror" 
                                id="meta_title" 
                                name="meta_title" 
                                value="{{ old('meta_title') }}"
                                maxlength="255"
                                placeholder="Мета-заголовок для SEO">
                            @error('meta_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Мета-описание -->
                        <div class="mb-3">
                            <label for="meta_description" class="form-label">Мета-описание (description)</label>
                            <textarea class="form-control @error('meta_description') is-invalid @enderror" 
                                    id="meta_description" 
                                    name="meta_description" 
                                    rows="3"
                                    maxlength="500"
                                    placeholder="Мета-описание для поисковых систем...">{{ old('meta_description') }}</textarea>
                            @error('meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Ключевые слова -->
                        <div class="mb-3">
                            <label for="meta_keywords" class="form-label">Ключевые слова (keywords)</label>
                            <input type="text" 
                                class="form-control @error('meta_keywords') is-invalid @enderror" 
                                id="meta_keywords" 
                                name="meta_keywords" 
                                value="{{ old('meta_keywords') }}"
                                maxlength="500"
                                placeholder="ключевое, слово, другое">
                            <div class="form-text">
                                Указывайте через запятую
                            </div>
                            @error('meta_keywords')
                                <div class="invalid-feedback">{{ $message }}</div>
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

                        <!-- Статистика разделов -->
                        <div class="mb-3 pt-3 border-top">
                            <label class="form-label small">Статистика разделов</label>
                            <div class="small text-muted">
                                <div class="d-flex justify-content-between">
                                    <span>Всего разделов:</span>
                                    <span class="fw-semibold">{{ $sections->count() }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Активных разделов:</span>
                                    <span class="fw-semibold text-success">{{ $sections->count() }}</span>
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
        const titleCounter = document.getElementById('title-counter');
        
        function updateCounter(input, counter) {
            counter.textContent = input.value.length;
        }
        
        titleInput.addEventListener('input', () => updateCounter(titleInput, titleCounter));
        
        // Инициализация счетчиков
        updateCounter(titleInput, titleCounter);
        
        // Очистка формы
        document.querySelector('button[type="reset"]').addEventListener('click', function() {
            setTimeout(() => {
                updateCounter(titleInput, titleCounter);
                // Сброс выбора раздела
                document.getElementById('section_id').selectedIndex = 0;
            }, 0);
        });

        // Поиск в выпадающем списке разделов
        const sectionSelect = document.getElementById('section_id');
        if (sectionSelect) {
            // Создаем кнопку для быстрого выбора "Без раздела"
            const clearSectionBtn = document.createElement('button');
            clearSectionBtn.type = 'button';
            clearSectionBtn.className = 'btn btn-sm btn-outline-secondary mt-2 w-100';
            clearSectionBtn.innerHTML = '<i class="bi bi-x-circle me-1"></i> Сбросить раздел';
            clearSectionBtn.addEventListener('click', function() {
                sectionSelect.value = '';
            });
            sectionSelect.parentNode.appendChild(clearSectionBtn);
        }
    });
</script>
@endpush