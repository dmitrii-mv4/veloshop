@extends('admin::layouts.default')

@section('title', 'Создание товара | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Каталог', 'url' => route('catalog.index')],
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
            <a href="{{ route('catalog.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад к списку
            </a>
        </div>
    </div>

    <!-- Форма создания товара -->
    <form action="{{ route('catalog.products.store') }}" method="POST" id="createProductForm">
        @csrf
        
        <div class="row fade-in">
            <!-- Основные поля -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-box-seam me-2"></i> Основная информация</h6>
                    </div>
                    <div class="card-body">
                        <!-- Уникальный ID товара -->
                        <div class="mb-3">
                            <label for="product_id" class="form-label required">
                                Уникальный ID товара (артикул)
                                <i class="bi bi-info-circle ms-1" 
                                   data-bs-toggle="tooltip" 
                                   title="Уникальный идентификатор товара в системе. Генерируется автоматически."></i>
                            </label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control @error('product_id') is-invalid @enderror" 
                                       id="product_id" 
                                       name="product_id" 
                                       value="{{ old('product_id', $productId) }}" 
                                       required
                                       maxlength="50"
                                       placeholder="U00000000000000000000000"
                                       pattern="[A-Za-z0-9-]+">
                            </div>
                            <div class="form-text">
                                Уникальный идентификатор товара. Можно использовать латинские буквы, цифры и дефисы.
                            </div>
                            @error('product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Название товара -->
                        <div class="mb-3">
                            <label for="name" class="form-label required">Название товара</label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required
                                   maxlength="255"
                                   placeholder="Введите полное название товара">
                            <div class="form-text text-end">
                                <span id="name-counter">0</span>/255
                            </div>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        </div>

                        <!-- Категория -->
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Категория</label>
                            <select class="form-select @error('category_id') is-invalid @enderror"
                                   id="category_id"
                                   name="category_id">
                                <option value="">-- Выберите категорию --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Выберите категорию для товара
                            </div>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Бренд -->
                        <div class="mb-3">
                            <label for="brand" class="form-label">Бренд</label>
                            <input type="text" 
                                   class="form-control @error('brand') is-invalid @enderror" 
                                   id="brand" 
                                   name="brand" 
                                   value="{{ old('brand') }}"
                                   maxlength="100"
                                   placeholder="Название бренда производителя">
                            @error('brand')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Модель -->
                        <div class="mb-3">
                            <label for="model" class="form-label">Модель</label>
                            <input type="text" 
                                   class="form-control @error('model') is-invalid @enderror" 
                                   id="model" 
                                   name="model" 
                                   value="{{ old('model') }}"
                                   maxlength="100"
                                   placeholder="Модель товара">
                            @error('model')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Сезон -->
                        <div class="mb-3">
                            <label for="seazon" class="form-label">Сезон</label>
                            <input type="text"
                                   class="form-control @error('seazon') is-invalid @enderror"
                                   id="seazon"
                                   name="seazon"
                                   value="{{ old('seazon') }}"
                                   maxlength="50"
                                   placeholder="Сезонность товара (например: Лето 2024)">
                            @error('seazon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Теги -->
                        <div class="mb-3">
                            <label for="tags" class="form-label">Теги</label>
                            <select class="form-select @error('tags') is-invalid @enderror"
                                   id="tags"
                                   name="tags[]"
                                   multiple
                                   style="min-height: 120px;">
                                @foreach($tags as $tag)
                                    <option value="{{ $tag->id }}" {{ in_array($tag->id, old('tags', [])) ? 'selected' : '' }}>
                                        {{ $tag->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Выберите теги для товара. Используйте Ctrl (Cmd на Mac) для выбора нескольких тегов.
                            </div>
                            @error('tags')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Атрибуты -->
                        @include('catalog::partials.attributes-widget', [
                            'attributes' => $attributes ?? [],
                            'entityAttributes' => [],
                            'entityType' => 'product'
                        ])
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
                                   placeholder="Мета-заголовок для поисковых систем">
                            <div class="form-text text-end">
                                <span id="meta-title-counter">0</span>/255
                            </div>
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
                            <div class="form-text text-end">
                                <span id="meta-description-counter">0</span>/500
                            </div>
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
                                Указывайте через запятую. Максимум 500 символов.
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
                        <!-- Информация о создателе -->
                        <div class="mb-4">
                            <h6 class="small text-muted mb-2">Информация о создании</h6>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center me-2"
                                     style="width: 32px; height: 32px;">
                                    <i class="bi bi-person text-muted"></i>
                                </div>
                                <div>
                                    <div class="small">Создатель</div>
                                    <div class="fw-semibold">{{ auth()->user()->name ?? 'Администратор' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i> Создать товар
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Модальное окно подтверждения -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Подтверждение создания</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Вы уверены, что хотите создать новый товар?</p>
                    <div class="alert alert-info alert-sm mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        После создания вы сможете добавить предложения (вариации) товара.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary" id="confirmCreate">Да, создать</button>
                </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Инициализация тултипов
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Счетчики символов
        const nameInput = document.getElementById('name');
        const nameCounter = document.getElementById('name-counter');
        const metaTitleInput = document.getElementById('meta_title');
        const metaTitleCounter = document.getElementById('meta-title-counter');
        const metaDescriptionInput = document.getElementById('meta_description');
        const metaDescriptionCounter = document.getElementById('meta-description-counter');

        // Функция обновления счетчика
        function updateCounter(input, counter) {
            if (!counter) return; // Skip if counter element doesn't exist
            counter.textContent = input.value.length;
        }

        // Инициализация счетчиков
        updateCounter(nameInput, nameCounter);
        updateCounter(metaTitleInput, metaTitleCounter);
        updateCounter(metaDescriptionInput, metaDescriptionCounter);

        // Слушатели событий для счетчиков
        nameInput.addEventListener('input', () => updateCounter(nameInput, nameCounter));
        metaTitleInput.addEventListener('input', () => updateCounter(metaTitleInput, metaTitleCounter));
        metaDescriptionInput.addEventListener('input', () => updateCounter(metaDescriptionInput, metaDescriptionCounter));

        // Генерация уникального ID товара
        document.getElementById('generateProductId').addEventListener('click', function() {
            const prefix = 'U';
            const randomNumber = Math.floor(Math.random() * 99999999999).toString().padStart(11, '0');
            document.getElementById('product_id').value = prefix + randomNumber;
        });

        // Загрузка статистики
        function loadStatistics() {
            fetch('{{ route("catalog.statistics") }}')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('totalProductsCount').textContent = data.totalProducts || 0;
                    document.getElementById('todayProductsCount').textContent = data.todayProducts || 0;
                })
                .catch(error => console.error('Error loading statistics:', error));
        }

        // Загружаем статистику при загрузке страницы
        loadStatistics();

        // Обработка сохранения как черновика
        document.getElementById('saveAsDraft').addEventListener('click', function() {
            // Добавляем скрытое поле для черновика
            const draftInput = document.createElement('input');
            draftInput.type = 'hidden';
            draftInput.name = 'is_draft';
            draftInput.value = '1';
            document.getElementById('createProductForm').appendChild(draftInput);

            // Отправляем форму
            document.getElementById('createProductForm').submit();
        });

        // Подтверждение создания
        const confirmCreateBtn = document.getElementById('confirmCreate');
        if (confirmCreateBtn) {
            confirmCreateBtn.addEventListener('click', function() {
                document.getElementById('createProductForm').submit();
            });
        }

        // Валидация формы перед отправкой
        document.getElementById('createProductForm').addEventListener('submit', function(e) {
            const productId = document.getElementById('product_id').value.trim();
            const name = document.getElementById('name').value.trim();

            if (!productId) {
                e.preventDefault();
                alert('Пожалуйста, заполните уникальный ID товара или сгенерируйте его автоматически.');
                return;
            }

            if (!name) {
                e.preventDefault();
                alert('Пожалуйста, заполните название товара.');
                return;
            }

            // Можно добавить дополнительные проверки
            if (productId.length > 50) {
                e.preventDefault();
                alert('Уникальный ID товара не должен превышать 50 символов.');
                return;
            }
        });

        // Автозаполнение SEO полей
        document.getElementById('name').addEventListener('blur', function() {
            const name = this.value.trim();
            const metaTitle = document.getElementById('meta_title');
            const metaDescription = document.getElementById('meta_description');
            const metaKeywords = document.getElementById('meta_keywords');

            if (name && !metaTitle.value) {
                metaTitle.value = `Купить ${name} - цена, отзывы, характеристики`;
            }

            if (name && !metaDescription.value) {
                metaDescription.value = `✅ ${name} - подробное описание, характеристики, отзывы покупателей. ✅ Гарантия качества. ✅ Быстрая доставка. ✅ Лучшие цены.`;
            }

            if (name && !metaKeywords.value) {
                const brand = document.getElementById('brand').value.trim();
                const model = document.getElementById('model').value.trim();
                let keywords = name.toLowerCase();
                if (brand) keywords += `, ${brand.toLowerCase()}`;
                if (model) keywords += `, ${model.toLowerCase()}`;
                metaKeywords.value = keywords + ', купить, цена, отзывы';
            }
        });
    });
</script>

<style>
.char-counter {
    font-size: 0.75rem;
    color: #6c757d;
    text-align: right;
}

.char-counter span {
    font-weight: 600;
}

.required::after {
    content: " *";
    color: #dc3545;
}

.form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0,0,0,.125);
}

.card-title {
    color: #495057;
    font-weight: 600;
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    border-color: #6c757d;
}

.alert-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .page-actions {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 1rem;
    }

    .page-actions > div:last-child {
        width: 100%;
    }

    .btn-group {
        width: 100%;
        flex-wrap: wrap;
    }

    .btn-group .btn {
        flex: 1;
        min-width: 120px;
        margin-bottom: 0.5rem;
    }
}
</style>
@endpush