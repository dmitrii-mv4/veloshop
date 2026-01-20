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
                                    <option value="{{ $id }}" {{ old('section_id', $good->section_id) == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                <small>
                                    @if($good->section)
                                        Текущий раздел: <strong>{{ $good->section->name }}</strong>
                                    @else
                                        Товар не привязан к разделу
                                    @endif
                                </small>
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
                                value="{{ old('meta_title', $good->meta_title) }}"
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
                                    placeholder="Мета-описание для поисковых систем...">{{ old('meta_description', $good->meta_description) }}</textarea>
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
                                value="{{ old('meta_keywords', $good->meta_keywords) }}"
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
                        
                        <!-- Информация о разделе -->
                        @if($good->section)
                        <div class="mb-3 pt-3 border-top">
                            <label class="form-label small">Информация о разделе</label>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center me-2"
                                     style="width: 32px; height: 32px;">
                                    <i class="bi bi-folder"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $good->section->name }}</div>
                                    <small class="text-muted">ID: {{ $good->section->id }}</small>
                                </div>
                            </div>
                            @if($good->section->parent)
                            <div class="mt-2 small text-muted">
                                <i class="bi bi-arrow-return-right me-1"></i>
                                Родительский раздел: {{ $good->section->parent->name }}
                            </div>
                            @endif
                        </div>
                        @endif
                        
                        <!-- Информация об авторе -->
                        @if($good->created_by)
                        <div class="mb-3 pt-3 border-top">
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
                                @if($good->section)
                                <a href="{{ route('catalog.sections.edit', $good->section) }}" class="btn btn-outline-info btn-sm">
                                    <i class="bi bi-folder me-1"></i> К разделу
                                </a>
                                @endif
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
        const titleCounter = document.getElementById('title-counter');
        
        function updateCounter(input, counter) {
            counter.textContent = input.value.length;
        }
        
        titleInput.addEventListener('input', () => updateCounter(titleInput, titleCounter));
        
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

        // Обработка удаления (остается без изменений)
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