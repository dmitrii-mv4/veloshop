@extends('admin::layouts.default')

@section('title', 'Редактирование страницы | KotiksCMS')

@section('styles')
<style>
    .editor-container {
        min-height: 400px;
    }
    .slug-preview {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        color: #6c757d;
    }
    .image-preview {
        max-width: 200px;
        max-height: 150px;
        object-fit: cover;
        border: 2px dashed #dee2e6;
        border-radius: 0.5rem;
        padding: 5px;
    }
    .image-preview.empty {
        display: none;
    }
    .char-counter {
        font-size: 0.75rem;
        text-align: right;
    }
    .char-counter.warning {
        color: #ffc107;
    }
    .char-counter.danger {
        color: #dc3545;
    }
    .history-badge {
        font-size: 0.75rem;
    }
</style>
@endsection

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Страницы', 'url' => route('admin.page.index')],
                ['title' => 'Редактирование: ' . $page->title]
            ],
        ])
    </div>

    <!-- Заголовок формы -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Редактирование страницы</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Редактируйте информацию на странице
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.page.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад
            </a>
            <a href="{{ url('/' . $page->slug) }}" target="_blank" class="btn btn-outline-primary">
                <i class="bi bi-eye"></i> Просмотр
            </a>
        </div>
    </div>

    <!-- История изменений (если есть) -->
    @if($page->created_at != $page->updated_at)
    <div class="alert alert-info alert-sm fade-in mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-clock-history me-2"></i>
                <strong>История изменений:</strong>
                Создано {{ $page->created_at->format('d.m.Y H:i') }} | 
                Обновлено {{ $page->updated_at->format('d.m.Y H:i') }}
            </div>
            <div class="history-badge">
                <span class="badge bg-primary bg-opacity-10 text-primary">
                    {{ $page->updated_at->diffForHumans() }}
                </span>
            </div>
        </div>
    </div>
    @endif

    <!-- Форма редактирования страницы -->
    <form action="{{ route('admin.page.update', $page) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        
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
                                   value="{{ old('title', $page->title) }}" 
                                   required
                                   maxlength="255"
                                   placeholder="Введите заголовок страницы">
                            <div class="char-counter mt-1">
                                <span id="title-counter">{{ strlen(old('title', $page->title)) }}</span>/255 символов
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
                                       value="{{ old('slug', $page->slug) }}" 
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
                                <span id="slug-preview" class="text-muted">/{{ old('slug', $page->slug) }}</span>
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
                                      placeholder="Краткое описание страницы для анонса...">{{ old('excerpt', $page->excerpt) }}</textarea>
                            <div class="char-counter mt-1">
                                <span id="excerpt-counter">{{ strlen(old('excerpt', $page->excerpt ?? '')) }}</span>/500 символов
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
                                          rows="15">{{ old('content', $page->content) }}</textarea>
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
                                   value="{{ old('meta_title', $page->meta_title) }}"
                                   maxlength="255"
                                   placeholder="Мета-заголовок для SEO">
                            <div class="char-counter mt-1">
                                <span id="meta-title-counter">{{ strlen(old('meta_title', $page->meta_title ?? '')) }}</span>/255 символов
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
                                      placeholder="Мета-описание для поисковых систем...">{{ old('meta_description', $page->meta_description) }}</textarea>
                            <div class="char-counter mt-1">
                                <span id="meta-description-counter">{{ strlen(old('meta_description', $page->meta_description ?? '')) }}</span>/500 символов
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
                                   value="{{ old('meta_keywords', $page->meta_keywords) }}"
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
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0"><i class="bi bi-send me-2"></i> Публикация</h6>
                            <span class="badge bg-{{ $page->status == 'published' ? 'success' : ($page->status == 'draft' ? 'warning' : 'secondary') }}">
                                {{ $page->status == 'published' ? 'Опубликовано' : ($page->status == 'draft' ? 'Черновик' : 'Приватная') }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Статус -->
                        <div class="mb-3">
                            <label for="status" class="form-label required">Статус</label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status" 
                                    required>
                                <option value="draft" {{ old('status', $page->status) == 'draft' ? 'selected' : '' }}>Черновик</option>
                                <option value="published" {{ old('status', $page->status) == 'published' ? 'selected' : '' }}>Опубликовать</option>
                                <option value="private" {{ old('status', $page->status) == 'private' ? 'selected' : '' }}>Приватная</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Дата публикации -->
                        <div class="mb-3" id="published-at-field">
                            <label for="published_at" class="form-label">Дата публикации</label>
                            <input type="datetime-local" 
                                   class="form-control @error('published_at') is-invalid @enderror" 
                                   id="published_at" 
                                   name="published_at"
                                   value="{{ old('published_at', $page->published_at ? $page->published_at->format('Y-m-d\TH:i') : '') }}">
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
                                    @if($parent->id != $page->id)
                                    <option value="{{ $parent->id }}" {{ old('parent_id', $page->parent_id) == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->title }}
                                    </option>
                                    @endif
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
                                   value="{{ old('order', $page->order) }}"
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
                                <i class="bi bi-save me-2"></i> Сохранить изменения
                            </button>
                            <div class="d-flex gap-2 mt-2">
                                <a href="{{ route('admin.page.index') }}" class="btn btn-outline-secondary flex-fill">
                                    Отмена
                                </a>
                                <button type="button" class="btn btn-outline-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deletePageModal">
                                    Удалить
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Информация о странице -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> Информация</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted d-block">Автор:</small>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-2"
                                     style="width: 24px; height: 24px; font-size: 0.75rem;">
                                    <i class="bi bi-person"></i>
                                </div>
                                <span>{{ $page->author->name ?? 'Неизвестно' }}</span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Создано:</small>
                            <span>{{ $page->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Обновлено:</small>
                            <span>{{ $page->updated_at->format('d.m.Y H:i') }}</span>
                        </div>
                        @if($page->published_at)
                        <div class="mb-2">
                            <small class="text-muted d-block">Опубликовано:</small>
                            <span>{{ $page->published_at->format('d.m.Y H:i') }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Информационная панель -->
    <div class="row mt-4 fade-in">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-lightbulb me-2"></i> Советы по редактированию</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li class="mb-2">Регулярно обновляйте контент для актуальности</li>
                        <li class="mb-2">Проверяйте ссылки и изображения на работоспособность</li>
                        <li class="mb-2">Обновляйте мета-данные при изменении содержания</li>
                        <li class="mb-2">Используйте предпросмотр перед публикацией</li>
                        <li class="mb-2">Сохраняйте предыдущие версии важных изменений</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-graph-up me-2"></i> Статистика страницы</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="h4 mb-1">{{ strlen($page->content) }}</div>
                            <small class="text-muted">Символов</small>
                        </div>
                        <div class="col-4">
                            <div class="h4 mb-1">{{ str_word_count(strip_tags($page->content)) }}</div>
                            <small class="text-muted">Слов</small>
                        </div>
                        <div class="col-4">
                            <div class="h4 mb-1">{{ substr_count($page->content, "\n") + 1 }}</div>
                            <small class="text-muted">Строк</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modals')
    <!-- Модальное окно удаления страницы -->
    <div class="modal fade" id="deletePageModal" tabindex="-1" aria-labelledby="deletePageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deletePageModalLabel">Удаление страницы</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Вы уверены, что хотите удалить страницу <strong>"{{ $page->title }}"</strong>?</p>
                    <div class="alert alert-warning alert-sm mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Страница будет перемещена в корзину и доступна для восстановления в течение 30 дней
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <form action="{{ route('admin.page.destroy', $page) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i> Удалить в корзину
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
