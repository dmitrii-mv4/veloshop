@extends('admin::layouts.default')

@section('title', 'Создание предложения | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Каталог', 'url' => route('catalog.index')],
                ['title' => $product->name, 'url' => route('catalog.products.show', $product)],
                ['title' => 'Предложения', 'url' => route('catalog.products.offers.index', $product)],
                ['title' => 'Создание предложения']
            ],
        ])
    </div>

    <!-- Заголовок формы -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Создание предложения для товара</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Товар: <strong>{{ $product->name }}</strong> |
                ID: <code>{{ $product->product_id }}</code>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('catalog.products.offers.index', $product) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад к списку
            </a>
        </div>
    </div>

    <!-- Форма создания предложения -->
    <form action="{{ route('catalog.products.offers.store', $product) }}" method="POST" id="createOfferForm">
        @csrf

        <div class="row fade-in">
            <!-- Основные поля -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-tags me-2"></i> Основная информация</h6>
                    </div>
                    <div class="card-body">
                        <!-- Уникальный ID предложения -->
                        <div class="mb-3">
                            <label for="offer_id" class="form-label required">
                                Уникальный ID предложения
                                <i class="bi bi-info-circle ms-1"
                                   data-bs-toggle="tooltip"
                                   title="Уникальный идентификатор предложения. Генерируется автоматически."></i>
                            </label>
                            <div class="input-group">
                                <input type="text"
                                       class="form-control @error('offer_id') is-invalid @enderror"
                                       id="offer_id"
                                       name="offer_id"
                                       value="{{ old('offer_id', $offerId) }}"
                                       required
                                       maxlength="50"
                                       placeholder="HQ-0000000"
                                       pattern="[A-Za-z0-9_\-]+">
                                <button type="button" class="btn btn-outline-secondary" id="generateOfferId">
                                    <i class="bi bi-arrow-repeat"></i> Сгенерировать
                                </button>
                            </div>
                            <div class="form-text">
                                Уникальный идентификатор предложения. Можно использовать латинские буквы, цифры и дефисы.
                            </div>
                            @error('offer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Название предложения -->
                        <div class="mb-3">
                            <label for="name" class="form-label required">Название предложения</label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required
                                   maxlength="255"
                                   placeholder="Введите название предложения (вариации)">
                            <div class="form-text text-end">
                                <span id="name-counter">0</span>/255
                            </div>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- V-код -->
                        <div class="mb-3">
                            <label for="vcode" class="form-label">V-код</label>
                            <input type="text"
                                class="form-control @error('vcode') is-invalid @enderror"
                                id="vcode"
                                name="vcode"
                                value="{{ old('vcode') }}"
                                maxlength="255"
                                placeholder="Уникальный код вариации">
                            @error('vcode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Артикул поставщика -->
                        <div class="mb-3">
                            <label for="articul_supplier" class="form-label">Артикул поставщика</label>
                            <input type="text"
                                   class="form-control @error('articul_supplier') is-invalid @enderror"
                                   id="articul_supplier"
                                   name="articul_supplier"
                                   value="{{ old('articul_supplier') }}"
                                   maxlength="100"
                                   placeholder="Артикул предложения">
                            @error('articul_supplier')
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
                                Выберите теги для предложения. Используйте Ctrl (Cmd на Mac) для выбора нескольких тегов.
                            </div>
                            @error('tags')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Атрибуты -->
                        @include('catalog::partials.attributes-widget', [
                            'attributes' => $attributes ?? [],
                            'entityAttributes' => [],
                            'entityType' => 'offer'
                        ])
                    </div>
                </div>

                <!-- Цены -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Цены</h6>
                    </div>
                    <div class="card-body">
                        @foreach($priceTypes as $type)
                            <div class="price-item mb-3 border rounded p-3">
                                <div class="row g-2">
                                    <div class="col-md-10">
                                        <label class="form-label small">
                                            {{ $type->title }}
                                            @if($type->type === 'uprice')
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <input type="hidden" name="prices[{{ $loop->index }}][price_type_id]" value="{{ $type->id }}">
                                            <input type="text"
                                                class="form-control @error('prices.'.$loop->index.'.value') is-invalid @enderror"
                                                name="prices[{{ $loop->index }}][value]"
                                                value="{{ old('prices.'.$loop->index.'.value') }}"
                                                placeholder="0.00"
                                                pattern="\d+(\.\d{1,2})?">
                                            <span class="input-group-text">{{ $type->currency }}</span>
                                            @error('prices.'.$loop->index.'.value')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @if($errors->has('prices'))
                            <div class="text-danger small">Пожалуйста, проверьте введенные цены.</div>
                        @endif
                    </div>
                </div>

                <!-- Остатки на складах -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-house-door me-2"></i> Остатки на складах
                        </h6>
                        <span class="badge bg-secondary">{{ $warehouses->count() }} складов</span>
                    </div>
                    <div class="card-body">
                        @if($warehouses->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap">Название склада</th>
                                        <th class="text-nowrap text-center" style="width: 150px;">Количество</th>
                                        <th class="text-nowrap text-center" style="width: 100px;">Доступно</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($warehouses as $warehouse)
                                    <tr>
                                        <td>
                                            <label for="warehouse_{{ $warehouse->id }}" class="form-label mb-0 d-flex align-items-center">
                                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-2"
                                                    style="width: 24px; height: 24px; font-size: 12px;">
                                                    <i class="bi bi-house-door"></i>
                                                </div>
                                                <span class="fw-medium">{{ $warehouse->title }}</span>
                                                @if($warehouse->address)
                                                <small class="text-muted ms-2" style="font-size: 0.75rem;">
                                                    <i class="bi bi-geo-alt"></i> {{ $warehouse->address }}
                                                </small>
                                                @endif
                                            </label>
                                        </td>
                                        <td class="text-center">
                                            <input type="number"
                                                class="form-control form-control-sm text-center @error('warehouses.'.$warehouse->id) is-invalid @enderror"
                                                id="warehouse_{{ $warehouse->id }}"
                                                name="warehouses[{{ $warehouse->id }}]"
                                                value="{{ old('warehouses.'.$warehouse->id, 0) }}"
                                                min="0"
                                                step="1"
                                                style="max-width: 120px; margin: 0 auto;"
                                                placeholder="0">
                                            @error('warehouses.'.$warehouse->id)
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border">
                                                <span id="available_badge_{{ $warehouse->id }}">0</span> шт.
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td class="fw-semibold">Общее количество на всех складах:</td>
                                        <td class="text-center fw-semibold" id="total_stock">0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Склады не настроены. Для управления остатками сначала создайте склады в разделе "Склады".
                        </div>
                        @endif
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
                        <!-- Информация о товаре -->
                        <div class="mb-4">
                            <h6 class="small text-muted mb-2">Информация о товаре</h6>
                            <div class="d-flex align-items-center mb-2">
                                <div class="rounded bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-2"
                                     style="width: 40px; height: 40px;">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ Str::limit($product->name, 30) }}</div>
                                    <div class="text-muted small">ID: {{ $product->product_id }}</div>
                                </div>
                            </div>
                            @if($product->brand)
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small me-2">Бренд:</div>
                                    <div class="fw-semibold">{{ $product->brand }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i> Создать предложение
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

        // Генерация уникального ID предложения
        document.getElementById('generateOfferId').addEventListener('click', function() {
            const prefix = 'HQ-';
            const randomNumber = Math.floor(Math.random() * 9999999).toString().padStart(7, '0');
            document.getElementById('offer_id').value = prefix + randomNumber;
        });

        // Валидация формы
        document.getElementById('createOfferForm').addEventListener('submit', function(e) {
            const offerId = document.getElementById('offer_id').value.trim();
            const name = document.getElementById('name').value.trim();

            if (!offerId) {
                e.preventDefault();
                alert('Пожалуйста, заполните уникальный ID предложения или сгенерируйте его автоматически.');
                return;
            }

            if (!name) {
                e.preventDefault();
                alert('Пожалуйста, заполните название предложения.');
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
                metaKeywords.value = name.toLowerCase() + ', купить, цена, отзывы';
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

.price-item, .attribute-item {
    background-color: #f8f9fa;
}

.remove-price, .remove-attribute {
    transition: all 0.2s;
}

.remove-price:hover, .remove-attribute:hover {
    background-color: #dc3545;
    color: white;
    border-color: #dc3545;
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

    .price-item .row > div {
        margin-bottom: 0.5rem;
    }
}
</style>
@endpush
