@extends('admin::layouts.default')

@section('title', 'Редактирование пункта меню | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Меню', 'url' => route('admin.menu.index')],
                ['title' => $menu->name, 'url' => route('admin.menu.items.index', $menu)],
                ['title' => 'Редактирование: ' . $menuitem->title]
            ],
        ])
    </div>

    <!-- Заголовок формы -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Редактирование пункта меню</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Меню: "{{ $menu->name }}" ({{ $menu->type }})
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.menu.items.index', $menu) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    <!-- Форма редактирования пункта меню -->
    <form action="{{ route('admin.menu.items.update', [$menu, $menuitem]) }}" method="POST" id="editMenuItemForm">
        @csrf
        @method('PUT')
        
        <input type="hidden" name="menu_id" value="{{ $menu->id }}">

        <div class="row fade-in">
            <!-- Основные поля -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-pencil-square me-2"></i> Основные данные</h6>
                    </div>
                    <div class="card-body">
                        <!-- Название пункта -->
                        <div class="mb-3">
                            <label for="title" class="form-label required">Название пункта</label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title', $menuitem->title) }}" 
                                   required
                                   maxlength="255"
                                   placeholder="Например: Главная страница">
                            <div class="char-counter mt-1">
                                <span id="title-counter">{{ Str::length(old('title', $menuitem->title)) }}</span>/255 символов
                            </div>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- URL -->
                        <div class="mb-3">
                            <label for="url" class="form-label required">URL адрес</label>
                            <div class="input-group">
                                <span class="input-group-text">/</span>
                                <input type="text" 
                                       class="form-control @error('url') is-invalid @enderror" 
                                       id="url" 
                                       name="url" 
                                       value="{{ old('url', $menuitem->url) }}" 
                                       required
                                       maxlength="500"
                                       placeholder="page/about или https://example.com">
                            </div>
                            <div class="form-text">
                                Можно указать относительный (/page) или абсолютный (https://) URL
                            </div>
                            <div class="url-preview mt-2">
                                <strong>Предпросмотр:</strong> 
                                <span id="url-preview" class="text-muted">{{ url('/') }}/</span>
                            </div>
                            @error('url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- SEO заголовок -->
                        <div class="mb-3">
                            <label for="seo_title" class="form-label">SEO заголовок (title)</label>
                            <input type="text" 
                                   class="form-control @error('seo_title') is-invalid @enderror" 
                                   id="seo_title" 
                                   name="seo_title" 
                                   value="{{ old('seo_title', $menuitem->seo_title) }}"
                                   maxlength="255"
                                   placeholder="SEO заголовок для метатега title">
                            <div class="char-counter mt-1">
                                <span id="seo-title-counter">{{ Str::length(old('seo_title', $menuitem->seo_title)) }}</span>/255 символов
                            </div>
                            @error('seo_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Используется для метатега title ссылки, если отличается от названия
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Боковая панель -->
            <div class="col-lg-4">
                <!-- Настройки пункта -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-gear me-2"></i> Настройки пункта</h6>
                    </div>
                    <div class="card-body">
                        <!-- Родительский пункт -->
                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Родительский пункт</label>
                            <select class="form-select @error('parent_id') is-invalid @enderror" 
                                    id="parent_id" 
                                    name="parent_id">
                                <option value="">Без родителя (корневой)</option>
                                @foreach($parentItems as $parent)
                                    <option value="{{ $parent['id'] }}" 
                                            {{ old('parent_id', $menuitem->parent_id) == $parent['id'] ? 'selected' : '' }}>
                                        {{ str_repeat('— ', $parent['level'] ?? 0) }}{{ $parent['title'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Иконка -->
                        <div class="mb-3">
                            <label for="icon" class="form-label">Иконка Bootstrap</label>
                            <div class="input-group">
                                <span class="input-group-text">bi bi-</span>
                                <input type="text" 
                                       class="form-control @error('icon') is-invalid @enderror" 
                                       id="icon" 
                                       name="icon" 
                                       value="{{ old('icon', $menuitem->icon) }}"
                                       maxlength="100"
                                       placeholder="house, person, gear">
                                <span class="input-group-text" id="iconPreview">
                                    <i class="bi" id="iconPreviewIcon"></i>
                                </span>
                            </div>
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <a href="https://icons.getbootstrap.com/" target="_blank" class="small">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Посмотреть все иконки
                                </a>
                            </div>
                        </div>

                        <!-- Порядок сортировки -->
                        <div class="mb-3">
                            <label for="order" class="form-label">Порядок сортировки</label>
                            <input type="number" 
                                   class="form-control @error('order') is-invalid @enderror" 
                                   id="order" 
                                   name="order" 
                                   value="{{ old('order', $menuitem->order) }}"
                                   min="0"
                                   step="1">
                            @error('order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Меньшее значение = выше в списке
                            </small>
                        </div>

                        <!-- Активность -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <!-- Скрытое поле для значения 0, когда чекбокс не отмечен -->
                                <input type="hidden" name="is_active" value="0">
                                
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       role="switch" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', $menuitem->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">Пункт активен</label>
                            </div>
                            <small class="form-text text-muted">
                                Неактивные пункты не отображаются на сайте
                            </small>
                        </div>

                        <!-- Информация о пункте -->
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="small text-uppercase text-muted mb-3">Информация о пункте</h6>
                            <ul class="list-unstyled mb-0 small">
                                <li class="mb-2">
                                    <i class="bi bi-hash text-muted me-2"></i>
                                    <strong>ID:</strong> {{ $menuitem->id }}
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-list-ul text-muted me-2"></i>
                                    <strong>Дочерних пунктов:</strong> {{ $menuitem->children()->count() }}
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-calendar-plus text-muted me-2"></i>
                                    <strong>Создано:</strong> {{ $menuitem->created_at->format('d.m.Y H:i') }}
                                </li>
                                <li>
                                    <i class="bi bi-calendar-check text-muted me-2"></i>
                                    <strong>Обновлено:</strong> {{ $menuitem->updated_at->format('d.m.Y H:i') }}
                                </li>
                            </ul>
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

@section('modals')
    <!-- Модальное окно подтверждения удаления -->
    <div class="modal fade" id="deleteMenuItemModal" tabindex="-1" aria-labelledby="deleteMenuItemModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteMenuItemModalLabel">Удаление пункта меню</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Вы уверены, что хотите удалить пункт меню <strong>{{ $menuitem->title }}</strong>?</p>
                    <div class="alert alert-danger alert-sm mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Внимание! При удалении пункта будут удалены все его дочерние пункты. Это действие нельзя отменить.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <form action="{{ route('admin.menu.items.destroy', [$menu, $menuitem]) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i> Удалить
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const titleInput = document.getElementById('title');
            const urlInput = document.getElementById('url');
            const iconInput = document.getElementById('icon');
            const seoTitleInput = document.getElementById('seo_title');
            const titleCounter = document.getElementById('title-counter');
            const seoTitleCounter = document.getElementById('seo-title-counter');
            const urlPreview = document.getElementById('url-preview');
            const iconPreviewIcon = document.getElementById('iconPreviewIcon');
            
            // Обновление счетчиков символов
            function updateCharCounter(input, counter) {
                counter.textContent = input.value.length;
            }
            
            // Обновление превью URL
            function updateUrlPreview() {
                const url = urlInput.value.trim();
                if (url.startsWith('http://') || url.startsWith('https://')) {
                    urlPreview.textContent = url;
                } else if (url.startsWith('/')) {
                    urlPreview.textContent = '{{ url("") }}' + url;
                } else {
                    urlPreview.textContent = '{{ url("") }}/' + url;
                }
            }
            
            // Обновление превью иконки
            function updateIconPreview() {
                const icon = iconInput.value.trim();
                if (icon) {
                    iconPreviewIcon.className = 'bi bi-' + icon;
                } else {
                    iconPreviewIcon.className = 'bi';
                }
            }
            
            // Инициализация
            updateCharCounter(titleInput, titleCounter);
            updateCharCounter(seoTitleInput, seoTitleCounter);
            updateUrlPreview();
            updateIconPreview();
            
            // Отслеживание изменений
            titleInput.addEventListener('input', () => updateCharCounter(titleInput, titleCounter));
            seoTitleInput.addEventListener('input', () => updateCharCounter(seoTitleInput, seoTitleCounter));
            urlInput.addEventListener('input', updateUrlPreview);
            iconInput.addEventListener('input', updateIconPreview);
            
            // Валидация формы
            const form = document.getElementById('editMenuItemForm');
            
            form.addEventListener('submit', function(e) {
                let valid = true;
                
                if (!titleInput.value.trim()) {
                    titleInput.classList.add('is-invalid');
                    valid = false;
                }
                
                if (!urlInput.value.trim()) {
                    urlInput.classList.add('is-invalid');
                    valid = false;
                }
                
                if (!valid) {
                    e.preventDefault();
                    // Прокрутка к первой ошибке
                    const firstInvalid = form.querySelector('.is-invalid');
                    if (firstInvalid) {
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstInvalid.focus();
                    }
                }
            });
            
            // Снятие ошибки при вводе
            titleInput.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.classList.remove('is-invalid');
                }
            });
            
            urlInput.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.classList.remove('is-invalid');
                }
            });
        });
    </script>
@endsection