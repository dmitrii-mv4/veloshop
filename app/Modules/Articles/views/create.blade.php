@extends('admin::layouts.default')

@section('title', 'Создание статьи | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Статьи', 'url' => route('admin.articles.index')],
                ['title' => 'Создание статьи']
            ],
        ])
    </div>
articles
    <!-- Заголовок формы -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Создание статьи</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Заполните форму ниже для добавления новой статьи
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.articles.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад к списку
            </a>
        </div>
    </div>

    <!-- Форма создания -->
    <form action="{{ route('admin.articles.store') }}" method="POST" id="createArticlesForm" enctype="multipart/form-data">
        @csrf
        
        <div class="row fade-in">
            <!-- Основные поля -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-newspaper me-2"></i> Основная информация</h6>
                    </div>
                    <div class="card-body">
                        <!-- Название (заголовок) -->
                        <div class="mb-3">
                            <label for="title" class="form-label required">Заголовок</label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}" 
                                   required
                                   maxlength="255"
                                   placeholder="Введите заголовок">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- URL (Slug) -->
                        <div class="mb-3">
                            <label for="slug" class="form-label required">URL-адрес (slug)</label>
                            <div class="input-group">
                                <span class="input-group-text">/</span>
                                <input type="text" class="form-control " id="slug" name="slug" value="" required="" pattern="[a-z0-9-]+" maxlength="255" placeholder="url-articles" data-manual="true">
                                <button type="button" class="btn btn-outline-secondary" id="generate-slug">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                Только латинские буквы в нижнем регистре, цифры и дефисы
                            </div>
                            <div class="slug-preview mt-2">
                                <strong>Предпросмотр:</strong> 
                                <span id="slug-preview" class="text-muted">/url-stranicy</span>
                            </div>
                        </div>

                        <!-- Отрывок (краткое описание) -->
                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Отрывок (краткое описание)</label>
                            <textarea class="form-control @error('excerpt') is-invalid @enderror" 
                                      id="excerpt" 
                                      name="excerpt" 
                                      rows="3"
                                      placeholder="Краткое содержание статьи...">{{ old('excerpt') }}</textarea>
                            @error('excerpt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Полное описание -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Полное описание</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="6"
                                      placeholder="Полный текст статьи...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Изображение -->
                        <div class="mb-3">
                            <label for="image" class="form-label">Изображение</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                   id="image" name="image" accept="image/*">
                            <small class="form-text text-muted">Загрузите изображение для статьи.</small>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Мета-информация (SEO) -->
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

                        <!-- Категории -->
                        <div class="mb-4">
                            <h6 class="small text-muted mb-2">Категории</h6>
                            <select multiple class="form-select @error('categories') is-invalid @enderror" 
                                    id="categories" name="categories[]" size="5">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ in_array($cat->id, old('categories', [])) ? 'selected' : '' }}>
                                        {{ $cat->title }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Удерживайте Ctrl для выбора нескольких категорий.</small>
                            @error('categories')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i> Создать статью
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Инициализация тултипов
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Счетчики символов (если нужны, можно добавить)
        // Автозаполнение SEO полей из заголовка
        document.getElementById('title').addEventListener('blur', function() {
            const title = this.value.trim();
            const metaTitle = document.getElementById('meta_title');
            const metaDescription = document.getElementById('meta_description');
            const metaKeywords = document.getElementById('meta_keywords');
            
            if (title && !metaTitle.value) {
                metaTitle.value = title;
            }
            if (title && !metaDescription.value) {
                metaDescription.value = title + '. Подробности читайте на нашем сайте.';
            }
            if (title && !metaKeywords.value) {
                metaKeywords.value = title.toLowerCase().replace(/\s+/g, ', ') + ', статьи';
            }
        });
    });
</script>

<style>
.required::after {
    content: " *";
    color: #dc3545;
}

.form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0,0,0,.125);
}

.card-title {
    color: #495057;
    font-weight: 600;
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
}
</style>
@endsection