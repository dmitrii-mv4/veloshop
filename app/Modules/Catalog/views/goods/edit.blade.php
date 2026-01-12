@extends('admin::layouts.default')

@section('title', 'Редактирование товара | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Каталог', 'url' => route('catalog.index')],
                ['title' => 'Товары', 'url' => route('catalog.goods.index')],
                ['title' => 'Редактирование товара']
            ],
        ])
    </div>

    <!-- Заголовок формы -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Редактирование товара</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                ID: {{ $good->id }} | Создан: {{ $good->created_at->format('d.m.Y H:i') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('catalog.goods.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    <!-- Форма редактирования товара -->
    <form action="{{ route('catalog.goods.update', $good) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row fade-in">
            <!-- Основные поля -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-pencil-square me-2"></i> Редактирование товара
                        </h6>
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
                                   value="{{ old('title', $good->title) }}" 
                                   required
                                   maxlength="255"
                                   placeholder="Введите полное название товара">
                            <div class="char-counter mt-1">
                                <span id="title-counter">{{ strlen(old('title', $good->title)) }}</span>/255 символов
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
                                   value="{{ old('articul', $good->articul) }}" 
                                   required
                                   maxlength="100"
                                   placeholder="Введите уникальный артикул товара">
                            <div class="char-counter mt-1">
                                <span id="articul-counter">{{ strlen(old('articul', $good->articul)) }}</span>/100 символов
                            </div>
                            @error('articul')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Информация о товаре -->
                <div class="card fade-in">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> Информация о товаре</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label small">Дата создания</label>
                                    <div class="text-muted">
                                        {{ $good->created_at->format('d.m.Y H:i') }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label small">Последнее обновление</label>
                                    <div class="text-muted">
                                        {{ $good->updated_at->format('d.m.Y H:i') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($good->author)
                        <div class="mb-3">
                            <label class="form-label small">Автор</label>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-2"
                                     style="width: 32px; height: 32px;">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $good->author->name }}</div>
                                    <small class="text-muted">{{ $good->author->email }}</small>
                                </div>
                            </div>
                        </div>
                        @endif
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
                        <div class="alert alert-warning alert-sm mb-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <small>Изменения будут применены ко всем связанным данным</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small">Текущий автор изменений</label>
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

                        <!-- Быстрые действия -->
                        <div class="mb-3 pt-3 border-top">
                            <label class="form-label small">Быстрые действия</label>
                            <div class="d-grid gap-2">
                                <a href="{{ route('catalog.goods.index') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-list-ul me-1"></i> К списку товаров
                                </a>
                                <a href="{{ route('catalog.goods.trash.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-trash me-1"></i> В корзину
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i> Сохранить изменения
                            </button>
                            <a href="{{ route('catalog.goods.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i> Отмена
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Модальное окно удаления -->
    <div class="modal fade" id="deleteGoodsModal" tabindex="-1" aria-labelledby="deleteGoodsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteGoodsModalLabel">Перемещение в корзину</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Вы уверены, что хотите переместить товар <strong id="goodsTitleToDelete"></strong> в корзину?</p>
                    <div class="alert alert-info alert-sm mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Товар будет доступен в корзине для восстановления в течение 30 дней
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <form id="deleteGoodsForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i> В корзину
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
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
        
        // Обработка удаления
        const deleteModal = document.getElementById('deleteGoodsModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const goodsId = button.getAttribute('data-goods-id');
                const goodsTitle = button.getAttribute('data-goods-title');
                const deleteUrl = '{{ route("catalog.goods.destroy", ":id") }}'.replace(':id', goodsId);
                
                document.getElementById('goodsTitleToDelete').textContent = goodsTitle;
                document.getElementById('deleteGoodsForm').action = deleteUrl;
            });
        }
    });
</script>
@endpush