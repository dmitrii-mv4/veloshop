@extends('admin::layouts.default')

@section('title', 'Создание страницы | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Страницы', 'url' => route('admin.page.index')],
                ['title' => 'Создание страницы']
            ],
        ])
    </div>

    <!-- Заголовок формы -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Создание новой страницы</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Заполните форму ниже для создания новой страницы
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.page.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    <!-- Форма создания страницы -->
    <form action="{{ route('admin.page.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="row fade-in">
            <!-- Основные поля -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-pencil-square me-2"></i> Основное содержание</h6>
                    </div>
                    <div class="card-body">
                        <!-- Заголовок -->
                        <div class="mb-3">
                            <label for="title" class="form-label required">Заголовок страницы</label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}" 
                                   required
                                   maxlength="255"
                                   placeholder="Введите заголовок страницы">
                            <div class="char-counter mt-1">
                                <span id="title-counter">0</span>/255 символов
                            </div>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- URL (Slug) -->
                        <div class="mb-3">
                            <label for="slug" class="form-label required">URL-адрес (slug)</label>
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
                                       placeholder="url-stranicy">
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
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Краткое описание -->
                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Краткое описание</label>
                            <textarea class="form-control @error('excerpt') is-invalid @enderror" 
                                      id="excerpt" 
                                      name="excerpt" 
                                      rows="3"
                                      maxlength="500"
                                      placeholder="Краткое описание страницы для анонса...">{{ old('excerpt') }}</textarea>
                            <div class="char-counter mt-1">
                                <span id="excerpt-counter">0</span>/500 символов
                            </div>
                            @error('excerpt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Основной контент -->
                        <div class="mb-3">
                            <label for="content" class="form-label">Содержание страницы</label>
                            <div class="editor-container">
                                <textarea class="form-control @error('content') is-invalid @enderror" 
                                          id="content" 
                                          name="content" 
                                          rows="15">{{ old('content') }}</textarea>
                            </div>
                            @error('content')
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
                                   value="{{ old('meta_title') }}"
                                   maxlength="255"
                                   placeholder="Мета-заголовок для SEO">
                            <div class="char-counter mt-1">
                                <span id="meta-title-counter">0</span>/255 символов
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
                            <div class="char-counter mt-1">
                                <span id="meta-description-counter">0</span>/500 символов
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
                                   maxlength="255"
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
                <!-- Публикация -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-send me-2"></i> Публикация</h6>
                    </div>
                    <div class="card-body">
                        <!-- Статус -->
                        <div class="mb-3">
                            <label for="status" class="form-label required">Статус</label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status" 
                                    required>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Черновик</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Опубликовать</option>
                                <option value="private" {{ old('status') == 'private' ? 'selected' : '' }}>Приватная</option>
                            </select>
                            <div class="form-text mt-2">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bi bi-file-text text-warning me-2"></i>
                                    <small class="text-muted">Черновик: виден только в админке</small>
                                </div>
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bi bi-globe text-success me-2"></i>
                                    <small class="text-muted">Опубликовать: доступен всем</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-lock text-secondary me-2"></i>
                                    <small class="text-muted">Приватная: доступ по ссылке</small>
                                </div>
                            </div>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Дата публикации (будет скрыто/показано в зависимости от статуса) -->
                        <div class="mb-3" id="published-at-field" style="display: none;">
                            <label for="published_at" class="form-label">Дата публикации</label>
                            <input type="datetime-local" 
                                   class="form-control @error('published_at') is-invalid @enderror" 
                                   id="published_at" 
                                   name="published_at"
                                   value="{{ old('published_at', date('Y-m-d\TH:i')) }}">
                            @error('published_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Родительская страница -->
                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Родительская страница</label>
                            <select class="form-select @error('parent_id') is-invalid @enderror" 
                                    id="parent_id" 
                                    name="parent_id">
                                <option value="">Без родителя (корневая)</option>
                                @foreach($parentPages as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Порядок -->
                        <div class="mb-3">
                            <label for="order" class="form-label">Порядок сортировки</label>
                            <input type="number" 
                                   class="form-control @error('order') is-invalid @enderror" 
                                   id="order" 
                                   name="order" 
                                   value="{{ old('order', 0) }}"
                                   min="0"
                                   step="1">
                            <div class="form-text">
                                Меньшее значение - выше в списке
                            </div>
                            @error('order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i> Сохранить страницу
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
