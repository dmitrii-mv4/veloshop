@extends('admin::layouts.default')

@section('title', 'Создание раздела каталога | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Каталог', 'url' => route('catalog.index')],
                ['title' => 'Разделы', 'url' => route('catalog.sections.index')],
                ['title' => 'Создание раздела']
            ],
        ])
    </div>

    <!-- Заголовок формы -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Создание нового раздела каталога</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Заполните форму ниже для добавления нового раздела в каталог
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('catalog.sections.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    <!-- Форма создания раздела -->
    <form action="{{ route('catalog.sections.store') }}" method="POST">
        @csrf
        
        <div class="row fade-in">
            <!-- Основные поля -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-pencil-square me-2"></i> Основная информация</h6>
                    </div>
                    <div class="card-body">
                        <!-- Название раздела -->
                        <div class="mb-4">
                            <label for="name" class="form-label required">
                                Название раздела
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required
                                   maxlength="255"
                                   minlength="2"
                                   placeholder="Введите название раздела">
                            <div class="char-counter mt-1">
                                <span id="name-counter">0</span>/255 символов
                            </div>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- URL (Slug) -->
                        <div class="mb-4">
                            <label for="slug" class="form-label required">
                                URL-адрес (slug)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">/</span>
                                <input type="text" 
                                    class="form-control @error('slug') is-invalid @enderror" 
                                    id="slug" 
                                    name="slug" 
                                    value="{{ old('slug') }}" 
                                    required
                                    pattern="[a-z0-9-]+"
                                    maxlength="255"
                                    placeholder="url-razdela">
                                <button type="button" class="btn btn-outline-secondary" id="generate-slug">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                Только латинские буквы в нижнем регистре, цифры и дефисы
                            </div>
                            <div class="slug-preview mt-2">
                                <strong>Предпросмотр:</strong> 
                                <span id="slug-preview" class="text-muted">/</span>
                            </div>
                            @error('slug')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Описание раздела -->
                        <div class="mb-4">
                            <label for="description" class="form-label">
                                Описание раздела
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4"
                                      maxlength="2000"
                                      placeholder="Краткое описание раздела...">{{ old('description') }}</textarea>
                            <div class="char-counter mt-1">
                                <span id="description-counter">0</span>/2000 символов
                            </div>
                            @error('description')
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
                            <label for="meta_title" class="form-label">
                                Мета-заголовок (title)
                            </label>
                            <input type="text" 
                                   class="form-control @error('meta_title') is-invalid @enderror" 
                                   id="meta_title" 
                                   name="meta_title" 
                                   value="{{ old('meta_title') }}"
                                   maxlength="255"
                                   placeholder="Мета-заголовок для SEO">
                            <div class="char-counter mt-1">
                                <span id="meta-title-counter">0</span>/255 символов
                            </div>
                            @error('meta_title')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Мета-описание -->
                        <div class="mb-3">
                            <label for="meta_description" class="form-label">
                                Мета-описание (description)
                            </label>
                            <textarea class="form-control @error('meta_description') is-invalid @enderror" 
                                      id="meta_description" 
                                      name="meta_description" 
                                      rows="3"
                                      maxlength="500"
                                      placeholder="Мета-описание для поисковых систем...">{{ old('meta_description') }}</textarea>
                            <div class="char-counter mt-1">
                                <span id="meta-description-counter">0</span>/500 символов
                            </div>
                            @error('meta_description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Ключевые слова -->
                        <div class="mb-3">
                            <label for="meta_keywords" class="form-label">
                                Ключевые слова (keywords)
                            </label>
                            <input type="text" 
                                   class="form-control @error('meta_keywords') is-invalid @enderror" 
                                   id="meta_keywords" 
                                   name="meta_keywords" 
                                   value="{{ old('meta_keywords') }}"
                                   maxlength="255"
                                   placeholder="ключевое, слово, другое">
                            <div class="form-text">
                                Указывайте через запятую
                            </div>
                            @error('meta_keywords')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Боковая панель -->
            <div class="col-lg-4">
                <!-- Настройки -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-gear me-2"></i> Настройки</h6>
                    </div>
                    <div class="card-body">
                        <!-- Статус активности -->
                        <div class="mb-4">
                            <label for="is_active" class="form-label required">
                                Статус активности
                            </label>
                            <div class="form-check form-switch">
                                <input class="form-check-input @error('is_active') is-invalid @enderror" 
                                       type="checkbox" 
                                       role="switch" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <span class="status-indicator">
                                        <span class="status-active text-success" style="display: none;">
                                            <i class="bi bi-check-circle me-1"></i> Активен
                                        </span>
                                        <span class="status-inactive text-secondary">
                                            <i class="bi bi-x-circle me-1"></i> Неактивен
                                        </span>
                                    </span>
                                </label>
                            </div>
                            <div class="form-text mt-2">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bi bi-eye text-success me-2"></i>
                                    <small class="text-muted">Активен: виден на сайте</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-eye-slash text-secondary me-2"></i>
                                    <small class="text-muted">Неактивен: скрыт на сайте</small>
                                </div>
                            </div>
                            @error('is_active')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Родительский раздел -->
                        <div class="mb-4">
                            <label for="parent_id" class="form-label">
                                Родительский раздел
                            </label>
                            <select class="form-select @error('parent_id') is-invalid @enderror" 
                                    id="parent_id" 
                                    name="parent_id">
                                <option value="">Без родителя (корневой раздел)</option>
                                @foreach($sectionTree as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->name }}
                                    </option>
                                    @if($parent->children->count() > 0)
                                        @include('catalog::sections.partials.options', [
                                            'sections' => $parent->children,
                                            'level' => 1,
                                            'oldValue' => old('parent_id')
                                        ])
                                    @endif
                                @endforeach
                            </select>
                            <div class="form-text">
                                Выберите родительский раздел для создания подраздела
                            </div>
                            @error('parent_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Порядок сортировки -->
                        <div class="mb-4">
                            <label for="sort_order" class="form-label">
                                Порядок сортировки
                            </label>
                            <input type="number" 
                                   class="form-control @error('sort_order') is-invalid @enderror" 
                                   id="sort_order" 
                                   name="sort_order" 
                                   value="{{ old('sort_order', 0) }}"
                                   min="0"
                                   max="999"
                                   step="1">
                            <div class="form-text">
                                Меньшее значение - выше в списке (0-999) <br/>
                                В этом порядке отображаются разделы на сайте
                            </div>
                            @error('sort_order')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i> Сохранить раздел
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise me-2"></i> Очистить форму
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Информация об авторе -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-person me-2"></i> Автор</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3"
                                 style="width: 48px; height: 48px;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ auth()->user()->name }}</div>
                                <small class="text-muted">{{ auth()->user()->email }}</small>
                            </div>
                        </div>
                        <div class="alert alert-info alert-sm mt-3 mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            <small>Раздел будет создан от вашего имени</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('styles')
<style>
    .form-check-input:checked + .form-check-label .status-active {
        display: inline-block !important;
    }
    .form-check-input:checked + .form-check-label .status-inactive {
        display: none !important;
    }
    .form-check-input:not(:checked) + .form-check-label .status-active {
        display: none !important;
    }
    .form-check-input:not(:checked) + .form-check-label .status-inactive {
        display: inline-block !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Элементы DOM для счетчиков
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');
        const descriptionInput = document.getElementById('description');
        const metaTitleInput = document.getElementById('meta_title');
        const metaDescriptionInput = document.getElementById('meta_description');
        
        const nameCounter = document.getElementById('name-counter');
        const slugPreview = document.getElementById('slug-preview');
        const descriptionCounter = document.getElementById('description-counter');
        const metaTitleCounter = document.getElementById('meta-title-counter');
        const metaDescriptionCounter = document.getElementById('meta-description-counter');
        
        // Кнопка генерации slug
        const generateSlugBtn = document.getElementById('generate-slug');
        
        // Переключатель активности
        const isActiveCheckbox = document.getElementById('is_active');
        
        // Кнопка сброса формы
        const resetBtn = document.querySelector('button[type="reset"]');
        
        // Функция обновления счетчика символов
        function updateCounter(input, counter) {
            if (counter && input) {
                counter.textContent = input.value.length;
            }
        }
        
        // Функция обновления предпросмотра slug
        function updateSlugPreview() {
            if (slugPreview && slugInput) {
                const slugValue = slugInput.value.trim();
                slugPreview.textContent = '/' + (slugValue || 'url-razdela');
            }
        }
        
        // Функция для генерации slug из текста
        function generateSlugFromText(text) {
            if (!text) return '';
            
            return text
                .toLowerCase()
                // Транслитерация кириллицы
                .replace(/[а-яё]/g, function(char) {
                    const translitMap = {
                        'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd',
                        'е': 'e', 'ё': 'yo', 'ж': 'zh', 'з': 'z', 'и': 'i',
                        'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n',
                        'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't',
                        'у': 'u', 'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch',
                        'ш': 'sh', 'щ': 'shch', 'ъ': '', 'ы': 'y', 'ь': '',
                        'э': 'e', 'ю': 'yu', 'я': 'ya'
                    };
                    return translitMap[char] || char;
                })
                // Заменяем пробелы и подчеркивания на дефисы
                .replace(/[\s_]+/g, '-')
                // Удаляем все символы, кроме латинских букв, цифр и дефисов
                .replace(/[^a-z0-9-]/g, '')
                // Убираем множественные дефисы
                .replace(/-+/g, '-')
                // Убираем дефисы в начале и конце
                .replace(/^-+/, '')
                .replace(/-+$/, '');
        }
        
        // Обработчик генерации slug
        function handleGenerateSlug() {
            const name = nameInput.value.trim();
            
            if (name) {
                const slug = generateSlugFromText(name);
                slugInput.value = slug;
                updateSlugPreview();
                
                // Показать уведомление (опционально)
                console.log('URL сгенерирован:', slug);
            } else {
                // Если поле названия пустое
                alert('Введите название раздела для генерации URL');
                nameInput.focus();
            }
        }
        
        // Автоматическая генерация slug при потере фокуса в поле названия
        function handleNameBlur() {
            // Генерируем slug только если:
            // 1. Поле названия не пустое
            // 2. Поле slug пустое
            // 3. Пользователь не находится в процессе редактирования slug
            const name = nameInput.value.trim();
            const slug = slugInput.value.trim();
            
            if (name && !slug && document.activeElement !== slugInput) {
                const generatedSlug = generateSlugFromText(name);
                slugInput.value = generatedSlug;
                updateSlugPreview();
            }
        }
        
        // Инициализация счетчиков символов
        function initCharCounters() {
            if (nameInput && nameCounter) {
                nameInput.addEventListener('input', () => updateCounter(nameInput, nameCounter));
                updateCounter(nameInput, nameCounter); // Инициализация начального значения
            }
            
            if (descriptionInput && descriptionCounter) {
                descriptionInput.addEventListener('input', () => updateCounter(descriptionInput, descriptionCounter));
                updateCounter(descriptionInput, descriptionCounter);
            }
            
            if (metaTitleInput && metaTitleCounter) {
                metaTitleInput.addEventListener('input', () => updateCounter(metaTitleInput, metaTitleCounter));
                updateCounter(metaTitleInput, metaTitleCounter);
            }
            
            if (metaDescriptionInput && metaDescriptionCounter) {
                metaDescriptionInput.addEventListener('input', () => updateCounter(metaDescriptionInput, metaDescriptionCounter));
                updateCounter(metaDescriptionInput, metaDescriptionCounter);
            }
        }
        
        // Инициализация работы с slug
        function initSlugHandlers() {
            // Обработчик клика на кнопку генерации
            if (generateSlugBtn) {
                generateSlugBtn.addEventListener('click', handleGenerateSlug);
            }
            
            // Автогенерация при изменении названия (потеря фокуса)
            if (nameInput) {
                nameInput.addEventListener('blur', handleNameBlur);
            }
            
            // Обновление предпросмотра при ручном вводе slug
            if (slugInput) {
                slugInput.addEventListener('input', updateSlugPreview);
                
                // Валидация ввода в реальном времени
                slugInput.addEventListener('input', function() {
                    const value = this.value;
                    // Разрешаем только латинские буквы в нижнем регистре, цифры и дефисы
                    const validValue = value.toLowerCase().replace(/[^a-z0-9-]/g, '');
                    if (value !== validValue) {
                        this.value = validValue;
                    }
                });
            }
            
            // Инициализация предпросмотра
            updateSlugPreview();
        }
        
        // Обработчик переключателя активности
        function initActivityToggle() {
            if (isActiveCheckbox) {
                // Стили управляются через CSS, но можем добавить дополнительную логику
                isActiveCheckbox.addEventListener('change', function() {
                    console.log('Статус активности изменен:', this.checked);
                });
            }
        }
        
        // Обработчик сброса формы
        function initResetHandler() {
            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    setTimeout(() => {
                        // Обновляем счетчики после сброса
                        if (nameInput && nameCounter) updateCounter(nameInput, nameCounter);
                        if (slugInput && slugPreview) updateSlugPreview();
                        if (descriptionInput && descriptionCounter) updateCounter(descriptionInput, descriptionCounter);
                        if (metaTitleInput && metaTitleCounter) updateCounter(metaTitleInput, metaTitleCounter);
                        if (metaDescriptionInput && metaDescriptionCounter) updateCounter(metaDescriptionInput, metaDescriptionCounter);
                    }, 0);
                });
            }
        }
        
        // Инициализация всех обработчиков
        function initAll() {
            initCharCounters();
            initSlugHandlers();
            initActivityToggle();
            initResetHandler();
        }
        
        // Запуск инициализации
        initAll();
        
        // Добавим вспомогательную функцию для отладки
        console.log('Форма создания/редактирования раздела инициализирована');
    });
</script>
@endpush