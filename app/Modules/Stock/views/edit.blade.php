@extends('admin::layouts.default')

@section('title', 'Редактирование акции | KotiksCMS')

@section('content')
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Акции', 'url' => route('admin.stock.index')],
                ['title' => 'Редактирование']
            ],
        ])
    </div>

    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Редактирование акции</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Измените данные акции
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.stock.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад к списку
            </a>
        </div>
    </div>

    <form action="{{ route('admin.stock.update', $stock) }}" method="POST" id="editStockForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row fade-in">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-newspaper me-2"></i> Основная информация</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label required">Заголовок акции</label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title', $stock->title) }}" 
                                   required
                                   maxlength="255">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- URL (Slug) -->
                        <div class="mb-3">
                            <label for="slug" class="form-label required">URL-адрес (slug)</label>
                            <div class="input-group">
                                <span class="input-group-text">/</span>
                                <input type="text" class="form-control " id="slug" name="slug" value="{{ old('title', $stock->slug) }}" required="" pattern="[a-z0-9-]+" maxlength="255" placeholder="url-stock" data-manual="true">
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

                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Отрывок</label>
                            <textarea class="form-control @error('excerpt') is-invalid @enderror" 
                                      id="excerpt" 
                                      name="excerpt" 
                                      rows="3">{{ old('excerpt', $stock->excerpt) }}</textarea>
                            @error('excerpt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Полное описание</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="6">{{ old('description', $stock->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Изображение</label>
                            @if($stock->image)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($stock->image) }}" alt="" style="max-height: 100px;" class="img-thumbnail">
                                </div>
                            @endif
                            <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                   id="image" name="image" accept="image/*">
                            <small class="form-text text-muted">Загрузите новое изображение для замены текущего.</small>
                            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-search me-2"></i> SEO-настройки</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="meta_title" class="form-label">Мета-заголовок (title)</label>
                            <input type="text" 
                                   class="form-control @error('meta_title') is-invalid @enderror" 
                                   id="meta_title" 
                                   name="meta_title" 
                                   value="{{ old('meta_title', $stock->meta_title) }}"
                                   maxlength="255">
                            @error('meta_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="meta_description" class="form-label">Мета-описание (description)</label>
                            <textarea class="form-control @error('meta_description') is-invalid @enderror" 
                                      id="meta_description" 
                                      name="meta_description" 
                                      rows="3"
                                      maxlength="500">{{ old('meta_description', $stock->meta_description) }}</textarea>
                            @error('meta_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="meta_keywords" class="form-label">Ключевые слова (keywords)</label>
                            <input type="text" 
                                   class="form-control @error('meta_keywords') is-invalid @enderror" 
                                   id="meta_keywords" 
                                   name="meta_keywords" 
                                   value="{{ old('meta_keywords', $stock->meta_keywords) }}"
                                   maxlength="500"
                                   placeholder="ключевое, слово, другое">
                            <div class="form-text">Указывайте через запятую. Максимум 500 символов.</div>
                            @error('meta_keywords')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-send me-2"></i> Действия</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="small text-muted mb-2">Информация об изменении</h6>
                            <div class="d-flex align-items-center mb-2">
                                <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center me-2"
                                     style="width: 32px; height: 32px;">
                                    <i class="bi bi-person text-muted"></i>
                                </div>
                                <div>
                                    <div class="small">Автор</div>
                                    <div class="fw-semibold">{{ $stock->author->name ?? 'Не указан' }}</div>
                                </div>
                            </div>
                            @if($stock->updater)
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center me-2"
                                     style="width: 32px; height: 32px;">
                                    <i class="bi bi-pencil text-muted"></i>
                                </div>
                                <div>
                                    <div class="small">Последнее обновление</div>
                                    <div class="fw-semibold">{{ $stock->updater->name }}</div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="mb-4">
                            <h6 class="small text-muted mb-2">Категории</h6>
                            <select multiple class="form-select @error('categories') is-invalid @enderror" 
                                    id="categories" name="categories[]" size="5">
                                @foreach($categories as $cat)
                                    @php $selected = old('categories', $stock->categories->pluck('id')->toArray()) @endphp
                                    <option value="{{ $cat->id }}" {{ in_array($cat->id, $selected) ? 'selected' : '' }}>
                                        {{ $cat->title }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Удерживайте Ctrl для выбора нескольких категорий.</small>
                            @error('categories')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i> Сохранить изменения
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
        // Автозаполнение SEO (опционально)
        document.getElementById('title').addEventListener('blur', function() {
            const title = this.value.trim();
            const metaTitle = document.getElementById('meta_title');
            const metaDescription = document.getElementById('meta_description');
            if (title && !metaTitle.value) {
                metaTitle.value = title;
            }
            if (title && !metaDescription.value) {
                metaDescription.value = title + '. Подробности читайте на нашем сайте.';
            }
        });
    });
</script>
@endsection