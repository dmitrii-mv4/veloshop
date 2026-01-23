@extends('admin::layouts.default')

@section('title', 'Редактирование предложения | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Каталог', 'url' => route('catalog.index')],
                ['title' => $product->name, 'url' => route('catalog.products.show', $product)],
                ['title' => 'Предложения', 'url' => route('catalog.products.offers.index', $product)],
                ['title' => $offer->name, 'url' => route('catalog.products.offers.show', ['product' => $product->id, 'offer' => $offer->offer_id])],
                ['title' => 'Редактирование']
            ],
        ])
    </div>

    <!-- Заголовок формы -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Редактирование предложения</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Товар: <strong>{{ $product->name }}</strong> | 
                ID предложения: <code>{{ $offer->offer_id }}</code>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('catalog.products.offers.index', $product) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад к списку
            </a>
            <a href="{{ route('catalog.products.offers.show', ['product' => $product->id, 'offer' => $offer->offer_id]) }}" 
               class="btn btn-outline-info">
                <i class="bi bi-eye"></i> Просмотр
            </a>
            <button type="button" class="btn btn-outline-danger" 
                    data-bs-toggle="modal" data-bs-target="#deleteOfferModal">
                <i class="bi bi-trash"></i> Удалить
            </button>
        </div>
    </div>

    <!-- Форма редактирования предложения -->
    <form action="{{ route('catalog.products.offers.update', ['product' => $product->id, 'offer' => $offer->offer_id]) }}" method="POST" id="editOfferForm">
        @csrf
        @method('PUT')
        
        <div class="row fade-in">
            <!-- Основные поля -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0"><i class="bi bi-tags me-2"></i> Основная информация</h6>
                        <span class="badge bg-primary">ID: {{ $offer->offer_id }}</span>
                    </div>
                    <div class="card-body">
                        <!-- Уникальный ID предложения (только для чтения) -->
                        <div class="mb-3">
                            <label for="offer_id" class="form-label required">Уникальный ID предложения</label>
                            <input type="text"
                                   class="form-control bg-light"
                                   id="offer_id"
                                   value="{{ $offer->offer_id }}" 
                                   readonly
                                   disabled>
                            <div class="form-text">
                                Уникальный идентификатор предложения. Не может быть изменен после создания.
                            </div>
                        </div>

                        <!-- Название предложения -->
                        <div class="mb-3">
                            <label for="name" class="form-label required">Название предложения</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $offer->name) }}" 
                                   required
                                   maxlength="255"
                                   placeholder="Введите название предложения (вариации)">
                            @error('name')
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
                                   value="{{ old('articul_supplier', $offer->articul_supplier) }}"
                                   maxlength="100"
                                   placeholder="Артикул предложения">
                            @error('articul_supplier')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Цены -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0"><i class="bi bi-currency-dollar me-2"></i> Цены</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addPriceBtn">
                            <i class="bi bi-plus-circle"></i> Добавить цену
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="pricesContainer">
                            @foreach($offer->prices as $index => $price)
                                <div class="price-item mb-3 border rounded p-3">
                                    <div class="row g-2">
                                        <div class="col-md-5">
                                            <label class="form-label small">Тип цены</label>
                                            <select class="form-select form-select-sm price-type" name="prices[{{ $index }}][type]" required>
                                                <option value="">Выберите тип</option>
                                                @foreach($priceTypes as $value => $label)
                                                    <option value="{{ $value }}" {{ $price->price_type == $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label small">Значение (₽)</label>
                                            <input type="text" class="form-control form-control-sm price-value" 
                                                   name="prices[{{ $index }}][value]" 
                                                   value="{{ number_format($price->price, 2, '.', '') }}"
                                                   placeholder="0.00" 
                                                   pattern="\d+(\.\d{1,2})?"
                                                   required>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-price">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="alert alert-info alert-sm mt-3">
                            <i class="bi bi-info-circle me-2"></i>
                            Основная цена (uprice) является обязательной для отображения товара на сайте.
                        </div>
                        @error('prices')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Атрибуты -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0"><i class="bi bi-list-check me-2"></i> Атрибуты</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addAttributeBtn">
                            <i class="bi bi-plus-circle"></i> Добавить атрибут
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="attributesContainer">
                            @foreach($offer->attributes as $index => $attribute)
                                <div class="attribute-item mb-3 border rounded p-3">
                                    <div class="row g-2">
                                        <div class="col-md-5">
                                            <label class="form-label small">Тип атрибута</label>
                                            <select class="form-select form-select-sm attribute-type" name="attributes[{{ $index }}][type]" required>
                                                <option value="">Выберите тип</option>
                                                @foreach($attributeTypes as $value => $label)
                                                    <option value="{{ $value }}" {{ $attribute->attributes_type == $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label small">Значение</label>
                                            <input type="text" class="form-control form-control-sm attribute-value" 
                                                   name="attributes[{{ $index }}][value]" 
                                                   value="{{ $attribute->attributes_value }}"
                                                   placeholder="Значение атрибута" 
                                                   maxlength="255"
                                                   required>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-attribute">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="alert alert-info alert-sm mt-3">
                            <i class="bi bi-info-circle me-2"></i>
                            Атрибуты помогают покупателям выбрать подходящую вариацию товара.
                        </div>
                        @error('attributes')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
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
                                   value="{{ old('meta_title', $offer->meta_title) }}"
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
                                      placeholder="Мета-описание для поисковых систем...">{{ old('meta_description', $offer->meta_description) }}</textarea>
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
                                   value="{{ old('meta_keywords', $offer->meta_keywords) }}"
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
                        <!-- Информация о предложении -->
                        <div class="mb-4">
                            <h6 class="small text-muted mb-2">Информация о предложении</h6>
                            <div class="d-flex align-items-center mb-2">
                                <div class="rounded bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center me-2"
                                     style="width: 40px; height: 40px;">
                                    <i class="bi bi-tags"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ Str::limit($offer->name, 25) }}</div>
                                    <div class="text-muted small">ID: {{ $offer->offer_id }}</div>
                                </div>
                            </div>
                            @if($offer->articul_supplier)
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small me-2">Артикул:</div>
                                    <div class="fw-semibold">{{ $offer->articul_supplier }}</div>
                                </div>
                            @endif
                        </div>

                        <!-- Статистика -->
                        <div class="mb-4">
                            <h6 class="small text-muted mb-2">Статистика</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="border rounded p-2 text-center">
                                        <div class="text-muted small">Цены</div>
                                        <div class="h5 mb-0">{{ $offer->prices->count() }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-2 text-center">
                                        <div class="text-muted small">Атрибуты</div>
                                        <div class="h5 mb-0">{{ $offer->attributes->count() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Основная информация -->
                        <div class="mb-3">
                            <h6 class="small text-muted mb-2">Основная информация</h6>
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-calendar text-muted me-2"></i>
                                <div class="small">
                                    Создано: {{ $offer->created_at->format('d.m.Y H:i') }}
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-arrow-clockwise text-muted me-2"></i>
                                <div class="small">
                                    Обновлено: {{ $offer->updated_at->format('d.m.Y H:i') }}
                                </div>
                            </div>
                        </div>

                        <!-- Предупреждение -->
                        <div class="alert alert-warning alert-sm mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <small>
                                При сохранении все существующие цены и атрибуты будут заменены.
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
                        <h6 class="card-title mb-0"><i class="bi bi-box-seam me-2"></i> Информация о товаре</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="rounded bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-2"
                                 style="width: 40px; height: 40px;">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ Str::limit($product->name, 25) }}</div>
                                <div class="text-muted small">ID: {{ $product->product_id }}</div>
                            </div>
                        </div>
                        <dl class="mb-0 small">
                            <dt class="text-muted">Бренд:</dt>
                            <dd class="mb-1">{{ $product->brand ?? '—' }}</dd>
                            
                            <dt class="text-muted">Модель:</dt>
                            <dd class="mb-1">{{ $product->model ?? '—' }}</dd>
                            
                            <dt class="text-muted">Сезон:</dt>
                            <dd>{{ $product->seazon ?? '—' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Счетчики символов
        const nameInput = document.getElementById('name');
        const nameCounter = document.getElementById('name-counter');
        const metaTitleInput = document.getElementById('meta_title');
        const metaDescriptionInput = document.getElementById('meta_description');
        const metaDescriptionCounter = document.getElementById('meta-description-counter');

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

        // Контейнеры для цен и атрибутов
        const pricesContainer = document.getElementById('pricesContainer');
        const attributesContainer = document.getElementById('attributesContainer');

        // Счетчики для индексов
        let priceIndex = {{ $offer->prices->count() }};
        let attributeIndex = {{ $offer->attributes->count() }};

        // Функция для создания HTML цены
        function createPriceHtml(index) {
            return `
                <div class="price-item mb-3 border rounded p-3">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <label class="form-label small">Тип цены</label>
                            <select class="form-select form-select-sm price-type" name="prices[${index}][type]" required>
                                <option value="">Выберите тип</option>
                                @foreach($priceTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small">Значение (₽)</label>
                            <input type="text" class="form-control form-control-sm price-value" 
                                   name="prices[${index}][value]" 
                                   placeholder="0.00" 
                                   pattern="\\d+(\\.\\d{1,2})?"
                                   required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-price">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        // Функция для создания HTML атрибута
        function createAttributeHtml(index) {
            return `
                <div class="attribute-item mb-3 border rounded p-3">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <label class="form-label small">Тип атрибута</label>
                            <select class="form-select form-select-sm attribute-type" name="attributes[${index}][type]" required>
                                <option value="">Выберите тип</option>
                                @foreach($attributeTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small">Значение</label>
                            <input type="text" class="form-control form-control-sm attribute-value" 
                                   name="attributes[${index}][value]" 
                                   placeholder="Значение атрибута" 
                                   maxlength="255"
                                   required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-attribute">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        // Добавление цены
        document.getElementById('addPriceBtn').addEventListener('click', function() {
            const priceItemHtml = createPriceHtml(priceIndex);
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = priceItemHtml;
            
            const priceItem = tempDiv.firstChild;
            pricesContainer.appendChild(priceItem);
            
            // Добавляем обработчик удаления для новой цены
            priceItem.querySelector('.remove-price').addEventListener('click', function() {
                priceItem.remove();
            });
            
            priceIndex++;
        });

        // Добавление атрибута
        document.getElementById('addAttributeBtn').addEventListener('click', function() {
            const attributeItemHtml = createAttributeHtml(attributeIndex);
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = attributeItemHtml;
            
            const attributeItem = tempDiv.firstChild;
            attributesContainer.appendChild(attributeItem);
            
            // Добавляем обработчик удаления для нового атрибута
            attributeItem.querySelector('.remove-attribute').addEventListener('click', function() {
                attributeItem.remove();
            });
            
            attributeIndex++;
        });

        // Удаление существующих цен и атрибутов
        document.querySelectorAll('.remove-price').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.price-item').remove();
            });
        });

        document.querySelectorAll('.remove-attribute').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.attribute-item').remove();
            });
        });

        // Валидация формы
        document.getElementById('editOfferForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            
            if (!name) {
                e.preventDefault();
                alert('Пожалуйста, заполните название предложения.');
                return;
            }
            
            // Проверяем, есть ли хотя бы одна цена
            const priceValues = document.querySelectorAll('.price-value');
            let hasPrices = false;
            priceValues.forEach(input => {
                if (input.value.trim()) {
                    hasPrices = true;
                }
            });
            
            if (!hasPrices) {
                e.preventDefault();
                alert('Пожалуйста, добавьте хотя бы одну цену.');
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
    
    .price-item .row > div,
    .attribute-item .row > div {
        margin-bottom: 0.5rem;
    }
}
</style>
@endsection

<!-- Модальное окно удаления предложения -->
<div class="modal fade" id="deleteOfferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Удаление предложения</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить предложение <strong>"{{ $offer->name }}"</strong>?</p>
                <div class="alert alert-danger alert-sm mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Внимание!</strong> Это действие невозможно отменить. Все связанные цены и атрибуты также будут удалены.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form action="{{ route('catalog.products.offers.destroy', ['product' => $product->id, 'offer' => $offer->offer_id]) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i> Удалить предложение
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>