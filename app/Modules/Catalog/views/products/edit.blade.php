@extends('admin::layouts.default')

@section('title', 'Редактирование товара | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Каталог', 'url' => route('catalog.index')],
                ['title' => 'Товар: ' . $product->name, 'url' => route('catalog.products.show', $product)],
                ['title' => 'Редактирование']
            ],
        ])
    </div>

    <!-- Заголовок формы -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Редактирование товара</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                ID: <code>{{ $product->product_id }}</code> |
                Создан: {{ $product->created_at->format('d.m.Y H:i') }} |
                Обновлен: {{ $product->updated_at->format('d.m.Y H:i') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('catalog.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад к списку
            </a>
            <a href="{{ route('catalog.products.show', $product) }}" class="btn btn-outline-info">
                <i class="bi bi-eye"></i> Просмотр
            </a>
            <button type="button" class="btn btn-outline-danger"
                    data-bs-toggle="modal" data-bs-target="#deleteProductModal">
                <i class="bi bi-trash"></i> Удалить
            </button>
        </div>
    </div>

    <!-- Форма редактирования товара -->
    <form action="{{ route('catalog.products.update', $product->id) }}" method="POST" id="editProductForm">
        @csrf
        @method('PUT')

        <div class="row fade-in">
            <!-- Основные поля -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0"><i class="bi bi-box-seam me-2"></i> Основная информация</h6>
                        <span class="badge bg-primary">ID: {{ $product->product_id }}</span>
                    </div>
                    <div class="card-body">
                        <!-- Уникальный ID товара (только для чтения) -->
                        <div class="mb-3">
                            <label for="product_id" class="form-label required">Уникальный ID товара (артикул)</label>
                            <input type="text"
                                   class="form-control bg-light"
                                   id="product_id"
                                   name="product_id"
                                   value="{{ $product->product_id }}"
                                   readonly>
                                   
                            <div class="form-text">
                                Уникальный идентификатор товара. Не может быть изменен после создания.
                            </div>
                        </div>

                        <!-- Название товара -->
                        <div class="mb-3">
                            <label for="name" class="form-label required">Название товара</label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $product->name) }}"
                                   required
                                   maxlength="255"
                                   placeholder="Введите полное название товара">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Категория -->
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Категория</label>
                            <select class="form-select @error('category_id') is-invalid @enderror"
                                   id="category_id"
                                   name="category_id">
                                <option value="">-- Выберите категорию --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
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
                                   value="{{ old('brand', $product->brand) }}"
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
                                   value="{{ old('model', $product->model) }}"
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
                                   value="{{ old('seazon', $product->seazon) }}"
                                   maxlength="50"
                                   placeholder="Сезонность товара (например: Лето 2024)">
                            @error('seazon')
                                <div class="invalid-feedback">{{ $message }}</div>
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
                                   value="{{ old('meta_title', $product->meta_title) }}"
                                   maxlength="255"
                                   placeholder="Мета-заголовок для поисковых систем">
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
                                      placeholder="Мета-описание для поисковых систем...">{{ old('meta_description', $product->meta_description) }}</textarea>
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
                                   value="{{ old('meta_keywords', $product->meta_keywords) }}"
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
                        <!-- Информация о редакторе -->
                        <div class="mb-4">
                            <h6 class="small text-muted mb-2">Информация об изменении</h6>
                            <div class="d-flex align-items-center mb-2">
                                <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center me-2"
                                    style="width: 32px; height: 32px;">
                                    <i class="bi bi-person-plus text-muted"></i>
                                </div>
                                <div>
                                    <div class="small">Создатель</div>
                                    <div class="fw-semibold">
                                        @php
                                            $creatorName = 'Неизвестно';
                                            if ($product->creator) {
                                                $creatorName = $product->creator->name ?? 'Пользователь #' . $product->created_by;
                                            }
                                        @endphp
                                        {{ $creatorName }}
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center me-2"
                                    style="width: 32px; height: 32px;">
                                    <i class="bi bi-person-check text-primary"></i>
                                </div>
                                <div>
                                    <div class="small">Редактор</div>
                                    <div class="fw-semibold">{{ auth()->user()->name ?? 'Вы' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Статистика товара -->
                        <div class="mb-4">
                            <h6 class="small text-muted mb-2">Статистика товара</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="border rounded p-2 text-center">
                                        <div class="text-muted small">Предложений</div>
                                        <div class="h5 mb-0">{{ $product->offers()->count() }}</div>
                                    </div>
                                </div>
                                {{--<div class="col-6">
                                    <div class="border rounded p-2 text-center">
                                        <div class="text-muted small">На складах</div>
                                        <div class="h5 mb-0">{{ $product->offers->sum(function($offer) { return $offer->getTotalQuantity(); }) }}</div>
                                    </div>
                                </div>--}}
                            </div>
                        </div>

                        <!-- Быстрые действия -->
                        <div class="mb-3">
                            <h6 class="small text-muted mb-2">Быстрые действия</h6>
                            <div class="d-grid gap-2">
                                <a href="{{ route('catalog.products.offers.index', $product) }}"
                                   class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-plus-circle me-1"></i> Добавить предложение
                                </a>
                                <a href="{{ route('catalog.products.show', $product) }}"
                                   class="btn btn-outline-info btn-sm">
                                    <i class="bi bi-eye me-1"></i> Просмотр товара
                                </a>
                            </div>
                        </div>

                        <!-- История изменений -->
                        <div class="alert alert-warning alert-sm mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            <small>
                                Последнее изменение: {{ $product->updated_at->format('d.m.Y в H:i') }}
                            </small>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i> Сохранить изменения
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise me-2"></i> Отменить изменения
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Информация о товаре -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> Информация</h6>
                    </div>
                    <div class="card-body">
                        <dl class="mb-0" style="font-size: 0.85rem;">
                            <dt class="text-muted">Дата создания:</dt>
                            <dd class="mb-2">{{ $product->created_at->format('d.m.Y H:i') }}</dd>

                            <dt class="text-muted">Дата обновления:</dt>
                            <dd class="mb-2">{{ $product->updated_at->format('d.m.Y H:i') }}</dd>

                            <dt class="text-muted">Создатель:</dt>
                            <dd class="mb-2">
                                @php
                                    $creatorName = 'Неизвестно';
                                    if ($product->creator) {
                                        $creatorName = $product->creator->name ?? 'Пользователь #' . $product->created_by;
                                    }
                                @endphp
                                {{ $creatorName }}
                            </dd>

                            <dt class="text-muted">Редактор:</dt>
                            <dd>
                                @php
                                    $editorName = 'Неизвестно';
                                    if ($product->editor) {
                                        $editorName = $product->editor->name ?? 'Пользователь #' . $product->updated_by;
                                    }
                                @endphp
                                {{ $editorName }}
                            </dd>
                        </dl>
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
        const nameInput = document.getElementById('name');
        const metaTitleInput = document.getElementById('meta_title');
        const metaDescriptionInput = document.getElementById('meta_description');

        // Функция обновления счетчика
        function updateCounter(input, counter) {
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

        // SEO анализ
        function updateSeoAnalysis() {
            let score = 0;
            const maxScore = 4;

            // Проверка названия товара
            const nameLength = nameInput.value.length;
            const nameCheck = document.getElementById('seoTitleCheck');
            if (nameLength > 0) {
                score++;
                nameCheck.innerHTML = '<i class="bi bi-check-circle text-success me-1"></i><span>Заголовок товара: <span class="text-success">' + nameLength + '/255</span></span>';
            }

            // Проверка мета-заголовка
            const metaTitleLength = metaTitleInput.value.length;
            const metaTitleCheck = document.getElementById('seoMetaTitleCheck');
            if (metaTitleLength >= 30 && metaTitleLength <= 60) {
                score++;
                metaTitleCheck.innerHTML = '<i class="bi bi-check-circle text-success me-1"></i><span>Мета-заголовок: <span class="text-success">' + metaTitleLength + '/60</span></span>';
            } else {
                metaTitleCheck.innerHTML = '<i class="bi bi-exclamation-circle text-warning me-1"></i><span>Мета-заголовок: <span class="text-warning">' + metaTitleLength + '/60</span></span>';
            }

            // Проверка мета-описания
            const metaDescriptionLength = metaDescriptionInput.value.length;
            const metaDescriptionCheck = document.getElementById('seoMetaDescriptionCheck');
            if (metaDescriptionLength >= 120 && metaDescriptionLength <= 160) {
                score++;
                metaDescriptionCheck.innerHTML = '<i class="bi bi-check-circle text-success me-1"></i><span>Мета-описание: <span class="text-success">' + metaDescriptionLength + '/160</span></span>';
            } else {
                metaDescriptionCheck.innerHTML = '<i class="bi bi-exclamation-circle text-warning me-1"></i><span>Мета-описание: <span class="text-warning">' + metaDescriptionLength + '/160</span></span>';
            }

            // Проверка ключевых слов
            const keywordsInput = document.getElementById('meta_keywords');
            const keywordsLength = keywordsInput.value.split(',').filter(k => k.trim()).length;
            const keywordsCheck = document.getElementById('seoKeywordsCheck');
            if (keywordsLength >= 3 && keywordsLength <= 10) {
                score++;
                keywordsCheck.innerHTML = '<i class="bi bi-check-circle text-success me-1"></i><span>Ключевые слова: <span class="text-success">' + keywordsLength + '/10</span></span>';
            } else {
                keywordsCheck.innerHTML = '<i class="bi bi-exclamation-circle text-warning me-1"></i><span>Ключевые слова: <span class="text-warning">' + keywordsLength + '/10</span></span>';
            }

            // Обновление прогресса
            const percentage = (score / maxScore) * 100;
            document.getElementById('seoScore').textContent = Math.round(percentage) + '%';
            document.getElementById('seoProgress').style.width = percentage + '%';

            // Цвет прогресс-бара
            const progressBar = document.getElementById('seoProgress');
            if (percentage >= 75) {
                progressBar.className = 'progress-bar bg-success';
                document.getElementById('seoScore').className = 'badge bg-success';
            } else if (percentage >= 50) {
                progressBar.className = 'progress-bar bg-warning';
                document.getElementById('seoScore').className = 'badge bg-warning';
            } else {
                progressBar.className = 'progress-bar bg-danger';
                document.getElementById('seoScore').className = 'badge bg-danger';
            }
        }

        // Инициализация SEO анализа
        updateSeoAnalysis();

        // Обновление SEO анализа при изменении полей
        nameInput.addEventListener('input', updateSeoAnalysis);
        metaTitleInput.addEventListener('input', updateSeoAnalysis);
        metaDescriptionInput.addEventListener('input', updateSeoAnalysis);
        document.getElementById('meta_keywords').addEventListener('input', updateSeoAnalysis);

        // Валидация формы перед отправкой
        document.getElementById('editProductForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();

            if (!name) {
                e.preventDefault();
                alert('Пожалуйста, заполните название товара.');
                return;
            }

            // Можно добавить дополнительные проверки
            if (name.length > 255) {
                e.preventDefault();
                alert('Название товара не должно превышать 255 символов.');
                return;
            }
        });

        // Автозаполнение SEO полей
        nameInput.addEventListener('blur', function() {
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

            updateSeoAnalysis();
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

.form-control:disabled, .form-control[readonly] {
    background-color: #f8f9fa;
    opacity: 1;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0,0,0,.125);
}

.card-title {
    color: #495057;
    font-weight: 600;
}

.badge {
    font-size: 0.75em;
    font-weight: 600;
}

.progress {
    background-color: #e9ecef;
    border-radius: 0.375rem;
}

.progress-bar {
    transition: width 0.6s ease;
}

.alert-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

dl dt {
    font-size: 0.8rem;
}

dl dd {
    margin-left: 0;
    font-weight: 500;
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

<!-- Модальное окно удаления товара -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Удаление товара</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить товар <strong>"{{ $product->name }}"</strong>?</p>
                <div class="alert alert-danger alert-sm mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Внимание!</strong> Это действие невозможно отменить. Все связанные данные будут удалены.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form action="{{ route('catalog.products.destroy', $product) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i> Удалить товар
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endpush
