@extends('admin::layouts.default')

@section('title', 'Редактирование типа меню | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Меню', 'url' => route('admin.menu.index')],
                ['title' => 'Типы меню', 'url' => route('admin.menu.types.index')],
                ['title' => 'Редактирование: ' . $menutype->name]
            ],
        ])
    </div>

    <!-- Заголовок формы -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Редактирование типа меню</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Обновление типа меню "{{ $menutype->name }}"
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.menu.types.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    <!-- Форма редактирования типа меню -->
    <form action="{{ route('admin.menu.types.update', $menutype) }}" method="POST" id="editMenuTypeForm">
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
                        <!-- Название типа меню -->
                        <div class="mb-3">
                            <label for="name" class="form-label required">Название типа меню</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $menutype->name) }}" 
                                   required
                                   maxlength="100"
                                   placeholder="Например: Header, Footer, Main Navigation">
                            <div class="char-counter mt-1">
                                <span id="name-counter">{{ Str::length(old('name', $menutype->name)) }}</span>/100 символов
                            </div>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Боковая панель -->
            <div class="col-lg-4">
                <!-- Информация о типе меню -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> Информация о типе</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-2">
                                <i class="bi bi-hash text-muted me-2"></i>
                                <strong>ID:</strong> {{ $menutype->id }}
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-menu-button-wide text-muted me-2"></i>
                                <strong>Используется в:</strong> 
                                @if($menutype->menus_count > 0)
                                    <a href="{{ route('admin.menu.index', ['type_id' => $menutype->id]) }}" 
                                       class="text-decoration-none">
                                        {{ $menutype->menus_count }} меню
                                    </a>
                                @else
                                    <span class="text-muted">Не используется</span>
                                @endif
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-calendar-plus text-muted me-2"></i>
                                <strong>Создано:</strong> {{ $menutype->created_at->format('d.m.Y H:i') }}
                                @if($menutype->creator)
                                    <br><small class="text-muted">{{ $menutype->creator->name }}</small>
                                @endif
                            </li>
                            <li>
                                <i class="bi bi-calendar-check text-muted me-2"></i>
                                <strong>Обновлено:</strong> {{ $menutype->updated_at->format('d.m.Y H:i') }}
                                @if($menutype->updater)
                                    <br><small class="text-muted">{{ $menutype->updater->name }}</small>
                                @endif
                            </li>
                        </ul>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i> Сохранить изменения
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Предупреждение -->
                @if($menutype->menus_count > 0)
                    <div class="card border-warning">
                        <div class="card-header bg-warning bg-opacity-10">
                            <h6 class="card-title mb-0"><i class="bi bi-exclamation-triangle me-2"></i> Внимание</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0" style="font-size: 0.85rem;">
                                Этот тип меню используется в {{ $menutype->menus_count }} меню. 
                                Изменение названия может повлиять на отображение в административной панели и front-end части.
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Счетчик символов
            const nameInput = document.getElementById('name');
            const nameCounter = document.getElementById('name-counter');
            
            // Обновление счетчика символов
            function updateCharCounter() {
                nameCounter.textContent = nameInput.value.length;
            }
            
            // Инициализация счетчика
            updateCharCounter();
            
            // Отслеживание ввода
            nameInput.addEventListener('input', updateCharCounter);
            
            // Валидация формы
            const form = document.getElementById('editMenuTypeForm');
            
            form.addEventListener('submit', function(e) {
                let valid = true;
                
                if (!nameInput.value.trim()) {
                    nameInput.classList.add('is-invalid');
                    valid = false;
                }
                
                if (!valid) {
                    e.preventDefault();
                    // Прокрутка к ошибке
                    nameInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    nameInput.focus();
                }
            });
            
            // Снятие ошибки при вводе
            nameInput.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.classList.remove('is-invalid');
                }
            });
        });
    </script>
@endsection