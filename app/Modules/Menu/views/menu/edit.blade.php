@extends('admin::layouts.default')

@section('title', 'Редактирование меню | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Меню', 'url' => route('admin.menu.index')],
                ['title' => 'Редактирование: ' . $menu->name]
            ],
        ])
    </div>

    <!-- Заголовок формы -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Редактирование меню</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Обновление настроек меню "{{ $menu->name }}"
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.menu.index', $menu) }}" class="btn btn-outline-success">
                <i class="bi bi-list-ul me-1"></i> Пункты меню
            </a>
            <a href="{{ route('admin.menu.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    <!-- Форма редактирования меню -->
    <form action="{{ route('admin.menu.update', $menu) }}" method="POST" id="editMenuForm">
        @csrf
        @method('PUT')
        
        <div class="row fade-in">
            <!-- Основные поля -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-pencil-square me-2"></i> Основные настройки</h6>
                    </div>
                    <div class="card-body">
                        <!-- Название меню -->
                        <div class="mb-3">
                            <label for="name" class="form-label required">Название меню</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $menu->name) }}" 
                                   required
                                   maxlength="255"
                                   placeholder="Например: Основное меню сайта">
                            <div class="char-counter mt-1">
                                <span id="name-counter">{{ Str::length(old('name', $menu->name)) }}</span>/255 символов
                            </div>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Описание -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3"
                                      maxlength="1000"
                                      placeholder="Краткое описание назначения меню...">{{ old('description', $menu->description) }}</textarea>
                            <div class="char-counter mt-1">
                                <span id="description-counter">{{ Str::length(old('description', $menu->description)) }}</span>/1000 символов
                            </div>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Боковая панель -->
            <div class="col-lg-4">
                <!-- Настройки меню -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-gear me-2"></i> Настройки меню</h6>
                    </div>
                    <div class="card-body">
                        <!-- Тип меню -->
                        <div class="mb-3">
                            <label for="menu_type_id" class="form-label">Тип меню</label>
                            <select class="form-select @error('menu_type_id') is-invalid @enderror" 
                                    id="menu_type_id" 
                                    name="menu_type_id">
                                <option value="">Выберите тип меню</option>
                                @foreach($menuTypes as $menuType)
                                    <option value="{{ $menuType->id }}" {{ old('menu_type_id', $menu->menu_type_id) == $menuType->id ? 'selected' : '' }}>
                                        {{ $menuType->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('menu_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text mt-2">
                                <a href="{{ route('admin.menu.types.create') }}" class="small">
                                    <i class="bi bi-plus-circle me-1"></i> Создать новый тип меню
                                </a>
                            </div>
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
                                       {{ old('is_active', $menu->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">Меню активно</label>
                            </div>
                        </div>

                        <!-- Информация о меню -->
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="small text-uppercase text-muted mb-3">Информация о меню</h6>
                            <ul class="list-unstyled mb-0 small">
                                <li class="mb-2">
                                    <i class="bi bi-hash text-muted me-2"></i>
                                    <strong>ID:</strong> {{ $menu->id }}
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-list-ul text-muted me-2"></i>
                                    <strong>Пунктов:</strong> {{ $menu->items_count ?? 0 }}
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-calendar-plus text-muted me-2"></i>
                                    <strong>Создано:</strong> {{ $menu->created_at->format('d.m.Y H:i') }}
                                </li>
                                <li>
                                    <i class="bi bi-calendar-check text-muted me-2"></i>
                                    <strong>Обновлено:</strong> {{ $menu->updated_at->format('d.m.Y H:i') }}
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

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Счетчики символов
            const nameInput = document.getElementById('name');
            const descriptionInput = document.getElementById('description');
            const nameCounter = document.getElementById('name-counter');
            const descriptionCounter = document.getElementById('description-counter');
            
            // Обновление счетчиков
            function updateCharCounter(input, counter) {
                counter.textContent = input.value.length;
            }
            
            // Инициализация счетчиков
            updateCharCounter(nameInput, nameCounter);
            updateCharCounter(descriptionInput, descriptionCounter);
            
            // Отслеживание ввода
            nameInput.addEventListener('input', () => updateCharCounter(nameInput, nameCounter));
            descriptionInput.addEventListener('input', () => updateCharCounter(descriptionInput, descriptionCounter));
            
            // Валидация формы
            const form = document.getElementById('editMenuForm');
            const typeSelect = document.getElementById('menu_type_id');
            
            form.addEventListener('submit', function(e) {
                let valid = true;
                
                if (!nameInput.value.trim()) {
                    nameInput.classList.add('is-invalid');
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
            nameInput.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.classList.remove('is-invalid');
                }
            });
            
            typeSelect.addEventListener('change', function() {
                if (this.value) {
                    this.classList.remove('is-invalid');
                }
            });
        });
    </script>
@endsection