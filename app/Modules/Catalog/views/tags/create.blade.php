@extends('admin::layouts.default')

@section('title', 'Создание тега | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Каталог', 'url' => route('catalog.index')],
                ['title' => 'Теги', 'url' => route('catalog.tags.index')],
                ['title' => 'Создание тега']
            ],
        ])
    </div>

    <!-- Заголовок формы -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Создание нового тега</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Заполните форму ниже для добавления нового тега в каталог
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('catalog.tags.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад к списку
            </a>
        </div>
    </div>

    <!-- Форма создания тега -->
    <form action="{{ route('catalog.tags.store') }}" method="POST" id="createTagForm">
        @csrf

        <div class="row fade-in">
            <!-- Основные поля -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-tags me-2"></i> Основная информация</h6>
                    </div>
                    <div class="card-body">
                        <!-- Название тега -->
                        <div class="mb-3">
                            <label for="name" class="form-label required">Название тега</label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required
                                   maxlength="100"
                                   placeholder="Введите название тега"
                                   autofocus>
                            <div class="form-text">
                                Название тега для отображения в интерфейсе. Максимум 100 символов.
                            </div>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Слаг -->
                        <div class="mb-3">
                            <label for="slug" class="form-label">
                                Слаг
                                <i class="bi bi-info-circle ms-1"
                                   data-bs-toggle="tooltip"
                                   title="Если не заполнен, будет сгенерирован автоматически из названия"></i>
                            </label>
                            <div class="input-group">
                                <input type="text"
                                       class="form-control @error('slug') is-invalid @enderror"
                                       id="slug"
                                       name="slug"
                                       value="{{ old('slug') }}"
                                       maxlength="100"
                                       placeholder="tag-slug">
                                <button type="button" class="btn btn-outline-secondary" id="generateSlug">
                                    <i class="bi bi-arrow-repeat"></i> Сгенерировать
                                </button>
                            </div>
                            <div class="form-text">
                                Уникальный идентификатор тега в URL. Латинские буквы, цифры и дефисы.
                            </div>
                            @error('slug')
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
                        <div class="mb-4">
                            <h6 class="small text-muted mb-2">Информация</h6>
                            <div class="alert alert-info alert-sm mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                Теги можно использовать для категоризации товаров и предложений.
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i> Создать тег
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Инициализация тултипов
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Генерация слага из названия
        document.getElementById('generateSlug').addEventListener('click', function() {
            const name = document.getElementById('name').value.trim();
            if (name) {
                // Простая транслитерация и замена пробелов на дефисы
                const slug = name.toLowerCase()
                    .replace(/[а-яё]/g, function(letter) {
                        const translit = {
                            'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd',
                            'е': 'e', 'ё': 'yo', 'ж': 'zh', 'з': 'z', 'и': 'i',
                            'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n',
                            'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't',
                            'у': 'u', 'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch',
                            'ш': 'sh', 'щ': 'sch', 'ъ': '', 'ы': 'y', 'ь': '',
                            'э': 'e', 'ю': 'yu', 'я': 'ya'
                        };
                        return translit[letter] || letter;
                    })
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                
                document.getElementById('slug').value = slug;
            } else {
                alert('Введите название тега для генерации слага');
            }
        });

        // Валидация формы перед отправкой
        document.getElementById('createTagForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();

            if (!name) {
                e.preventDefault();
                alert('Пожалуйста, заполните название тега.');
                return;
            }
        });

        // Автозаполнение слага при изменении названия (если слаг пуст)
        document.getElementById('name').addEventListener('input', function() {
            const slugInput = document.getElementById('slug');
            if (!slugInput.value || slugInput.value === '') {
                // Можно добавить автоматическую генерацию в реальном времени
            }
        });
    });
</script>

<style>
    .required::after {
        content: " *";
        color: #dc3545;
    }

    .alert-sm {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }
</style>
@endpush
