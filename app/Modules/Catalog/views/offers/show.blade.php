@extends('admin::layouts.default')

@section('title', 'Просмотр предложения | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Каталог', 'url' => route('catalog.index')],
                ['title' => $product->name, 'url' => route('catalog.products.show', $product)],
                ['title' => 'Предложения', 'url' => route('catalog.products.offers.index', $product)],
                ['title' => $offer->name]
            ],
        ])
    </div>

    <!-- Навигация -->
    <div class="d-flex mb-4 fade-in">
        <div class="btn-group" role="group">
            <a href="{{ route('catalog.products.offers.index', $product) }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Назад к списку
            </a>
            <a href="{{ route('catalog.products.offers.edit', ['product' => $product->id, 'offer' => $offer->offer_id]) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-1"></i> Редактировать
            </a>
            <button type="button" class="btn btn-outline-danger" 
                    data-bs-toggle="modal" data-bs-target="#deleteOfferModal">
                <i class="bi bi-trash me-1"></i> Удалить
            </button>
        </div>
    </div>

    <!-- Основная информация -->
    <div class="row fade-in">
        <!-- Левая колонка -->
        <div class="col-lg-8">
            <!-- Карточка с основной информацией -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ $offer->name }}</h5>
                    <span class="badge bg-primary">ID: {{ $offer->offer_id }}</span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <dl>
                                <dt class="text-muted">Артикул поставщика:</dt>
                                <dd class="mb-3">
                                    @if($offer->articul_supplier)
                                        <span class="badge bg-info">{{ $offer->articul_supplier }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </dd>
                                
                                <dt class="text-muted">Товар:</dt>
                                <dd class="mb-3">
                                    <a href="{{ route('catalog.products.show', $product) }}" class="text-decoration-none">
                                        {{ $product->name }}
                                    </a>
                                </dd>
                                
                                <dt class="text-muted">ID товара:</dt>
                                <dd class="mb-3"><code>{{ $product->product_id }}</code></dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl>
                                <dt class="text-muted">Дата создания:</dt>
                                <dd class="mb-3">{{ $offer->created_at->format('d.m.Y H:i') }}</dd>
                                
                                <dt class="text-muted">Дата обновления:</dt>
                                <dd class="mb-3">{{ $offer->updated_at->format('d.m.Y H:i') }}</dd>
                                
                                <dt class="text-muted">Статус:</dt>
                                <dd>
                                    <span class="badge bg-success">Активно</span>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Цены -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-currency-dollar me-2"></i> Цены</h6>
                </div>
                <div class="card-body">
                    @if($offer->prices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Тип цены</th>
                                        <th>Значение</th>
                                        <th>Описание</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($offer->prices as $price)
                                        <tr>
                                            <td>
                                                @php
                                                    $priceTypes = [
                                                        'uprice' => 'Основная цена',
                                                        'price_marketplace' => 'Цена на маркетплейсе',
                                                        'price_wholesale' => 'Оптовая цена',
                                                        'price_discount' => 'Цена со скидкой',
                                                        'price_special' => 'Специальная цена'
                                                    ];
                                                @endphp
                                                <span class="badge bg-primary">
                                                    {{ $priceTypes[$price->price_type] ?? $price->price_type }}
                                                </span>
                                            </td>
                                            <td class="fw-semibold">{{ number_format($price->price, 2, '.', ' ') }} ₽</td>
                                            <td class="text-muted">
                                                @switch($price->price_type)
                                                    @case('uprice')
                                                        Основная цена для отображения на сайте
                                                        @break
                                                    @case('price_marketplace')
                                                        Цена для продажи на маркетплейсах
                                                        @break
                                                    @case('price_wholesale')
                                                        Цена для оптовых покупателей
                                                        @break
                                                    @default
                                                        Дополнительная цена
                                                @endswitch
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-currency-dollar fs-4 text-muted"></i>
                            <p class="mt-2 text-muted">Цены не указаны</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Атрибуты -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-list-check me-2"></i> Атрибуты</h6>
                </div>
                <div class="card-body">
                    @if($offer->attributes->count() > 0)
                        <div class="row">
                            @foreach($offer->attributes as $attribute)
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3">
                                        <div class="small text-muted mb-1">
                                            @php
                                                $attributeTypes = [
                                                    'color' => 'Цвет',
                                                    'size' => 'Размер',
                                                    'weight' => 'Вес',
                                                    'material' => 'Материал',
                                                    'dimensions' => 'Габариты',
                                                    'storage' => 'Объем памяти',
                                                    'screen' => 'Экран',
                                                    'cpu' => 'Процессор',
                                                    'ram' => 'Оперативная память'
                                                ];
                                            @endphp
                                            {{ $attributeTypes[$attribute->attributes_type] ?? $attribute->attributes_type }}
                                        </div>
                                        <div class="fw-semibold">{{ $attribute->attributes_value }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-list-check fs-4 text-muted"></i>
                            <p class="mt-2 text-muted">Атрибуты не указаны</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Правая колонка -->
        <div class="col-lg-4">
            <!-- SEO информация -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-search me-2"></i> SEO информация</h6>
                </div>
                <div class="card-body">
                    <dl>
                        <dt class="text-muted small">Мета-заголовок:</dt>
                        <dd class="mb-3">
                            @if($offer->meta_title)
                                {{ $offer->meta_title }}
                            @else
                                <span class="text-muted">Не указан</span>
                            @endif
                        </dd>
                        
                        <dt class="text-muted small">Мета-описание:</dt>
                        <dd class="mb-3">
                            @if($offer->meta_description)
                                {{ Str::limit($offer->meta_description, 100) }}
                            @else
                                <span class="text-muted">Не указано</span>
                            @endif
                        </dd>
                        
                        <dt class="text-muted small">Ключевые слова:</dt>
                        <dd>
                            @if($offer->meta_keywords)
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach(explode(',', $offer->meta_keywords) as $keyword)
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                            {{ trim($keyword) }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted">Не указаны</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Информация о товаре -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-box-seam me-2"></i> Информация о товаре</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-2"
                             style="width: 40px; height: 40px;">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">{{ $product->name }}</div>
                            <div class="text-muted small">ID: {{ $product->product_id }}</div>
                        </div>
                    </div>
                    
                    <dl class="small mb-0">
                        <dt class="text-muted">Бренд:</dt>
                        <dd class="mb-1">{{ $product->brand ?? '—' }}</dd>
                        
                        <dt class="text-muted">Модель:</dt>
                        <dd class="mb-1">{{ $product->model ?? '—' }}</dd>
                        
                        <dt class="text-muted">Сезон:</dt>
                        <dd class="mb-1">{{ $product->seazon ?? '—' }}</dd>
                        
                        <dt class="text-muted">Группа:</dt>
                        <dd>{{ $product->proup_name ?? '—' }}</dd>
                    </dl>
                </div>
            </div>

            <!-- Быстрые действия -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-lightning me-2"></i> Быстрые действия</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('catalog.products.offers.edit', ['product' => $product->id, 'offer' => $offer->offers_id]) }}" 
                           class="btn btn-primary">
                            <i class="bi bi-pencil me-2"></i> Редактировать предложение
                        </a>
                        <a href="{{ route('catalog.products.edit', $product) }}" 
                           class="btn btn-outline-secondary">
                            <i class="bi bi-pencil-square me-2"></i> Редактировать товар
                        </a>
                        <a href="{{ route('catalog.products.offers.index', $product) }}" 
                           class="btn btn-outline-info">
                            <i class="bi bi-list me-2"></i> Все предложения
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                <form action="{{ route('catalog.products.offers.destroy', ['product' => $product->id, 'offer' => $offer->offers_id]) }}" method="POST" class="d-inline">
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