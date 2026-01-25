@extends('admin::layouts.default')

@section('title', 'Создание типа меню | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Меню', 'url' => route('admin.menu.index')],
                ['title' => 'Типы меню', 'url' => route('admin.menu.types.index')],
                ['title' => 'Создание типа меню']
            ],
        ])
    </div>

    <!-- Заголовок формы -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Создание типа меню</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Заполните форму ниже для создания нового типа меню
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.menu.types.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    <!-- Форма создания типа меню -->
    <form action="{{ route('admin.menu.types.store') }}" method="POST" id="createMenuTypeForm">
        @csrf
        
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
                                   value="{{ old('name') }}" 
                                   required
                                   maxlength="100"
                                   placeholder="Например: Header, Footer, Main Navigation">
                            <div class="char-counter mt-1">
                                <span id="name-counter">0</span>/100 символов
                            </div>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Уникальное название для идентификации типа меню
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Боковая панель -->
            <div class="col-lg-4">
                <!-- Настройки типа меню -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-gear me-2"></i> Информация</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info alert-sm mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Тип меню используется для классификации меню по назначению.
                            После создания вы сможете выбирать этот тип при создании меню.
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i> Создать тип меню
                            </button>
                            <a href="{{ route('admin.menu.types.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i> Отмена
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Примеры типов меню -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-lightbulb me-2"></i> Примеры типов</h6>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0" style="font-size: 0.85rem;">
                            <li class="mb-2">
                                <strong>Header</strong> - меню в шапке сайта
                            </li>
                            <li class="mb-2">
                                <strong>Footer</strong> - меню в подвале сайта
                            </li>
                            <li class="mb-2">
                                <strong>Main Navigation</strong> - основная навигация
                            </li>
                            <li class="mb-2">
                                <strong>Sidebar</strong> - боковое меню
                            </li>
                            <li>
                                <strong>Mobile</strong> - мобильное меню
                            </li>
                        </ul>
                    </div>
                </div>
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
            const form = document.getElementById('createMenuTypeForm');
            
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