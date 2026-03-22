@extends('admin::layouts.default')

@section('title', 'Редактирование тега | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Каталог', 'url' => route('catalog.index')],
                ['title' => 'Теги', 'url' => route('catalog.tags.index')],
                ['title' => 'Редактирование тега']
            ],
        ])
    </div>

    <!-- Заголовок формы -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Редактирование тега</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                ID: <code>{{ $tag->id }}</code> |
                Создан: {{ $tag->created_at->format('d.m.Y H:i') }} |
                Обновлен: {{ $tag->updated_at->format('d.m.Y H:i') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('catalog.tags.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад к списку
            </a>
            <button type="button" class="btn btn-outline-danger"
                    data-bs-toggle="modal" data-bs-target="#deleteTagModal">
                <i class="bi bi-trash"></i> Удалить
            </button>
        </div>
    </div>

    <!-- Форма редактирования тега -->
    <form action="{{ route('catalog.tags.update', $tag->id) }}" method="POST" id="editTagForm">
        @csrf
        @method('PUT')

        <div class="row fade-in">
            <!-- Основные поля -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0"><i class="bi bi-tags me-2"></i> Основная информация</h6>
                        <span class="badge bg-primary">ID: {{ $tag->id }}</span>
                    </div>
                    <div class="card-body">
                        <!-- Название тега -->
                        <div class="mb-3">
                            <label for="name" class="form-label required">Название тега</label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $tag->name) }}"
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
                                       value="{{ old('slug', $tag->slug) }}"
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

                <!-- Информация об использовании -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-link-45deg me-2"></i> Использование тега</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $productsCount = $tag->products()->count();
                            $offersCount = $tag->offers()->count();
                            $totalCount = $productsCount + $offersCount;
                        @endphp
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded p-3 text-center">
                                    <div class="text-muted small">Товаров</div>
                                    <div class="h4 mb-0 {{ $productsCount > 0 ? 'text-primary' : 'text-muted' }}">
                                        {{ $productsCount }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 text-center">
                                    <div class="text-muted small">Предложений</div>
                                    <div class="h4 mb-0 {{ $offersCount > 0 ? 'text-primary' : 'text-muted' }}">
                                        {{ $offersCount }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($totalCount > 0)
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                Тег используется в {{ $totalCount }} записях. При удалении все связи будут потеряны.
                            </div>
                        @else
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Тег не используется ни в одной записи.
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
                        <div class="mb-4">
                            <h6 class="small text-muted mb-2">Информация об изменении</h6>
                            <div class="d-flex align-items-center mb-2">
                                <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center me-2"
                                     style="width: 32px; height: 32px;">
                                    <i class="bi bi-person text-muted"></i>
                                </div>
                                <div>
                                    <div class="small">Редактор</div>
                                    <div class="fw-semibold">{{ auth()->user()->name ?? 'Администратор' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Статистика -->
                        <div class="mb-3">
                            <h6 class="small text-muted mb-2">Статистика</h6>
                            <dl class="mb-0" style="font-size: 0.85rem;">
                                <dt class="text-muted">Дата создания:</dt>
                                <dd class="mb-2">{{ $tag->created_at->format('d.m.Y H:i') }}</dd>
                                <dt class="text-muted">Дата обновления:</dt>
                                <dd>{{ $tag->updated_at->format('d.m.Y H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i> Сохранить изменения
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise me-2"></i> Отменить изменения
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Модальное окно подтверждения удаления -->
    <div class="modal fade" id="deleteTagModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Подтверждение удаления</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Вы уверены, что хотите удалить тег <strong>{{ $tag->name }}</strong>?</p>
                    @if($totalCount > 0)
                        <div class="alert alert-danger mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Тег используется в {{ $totalCount }} записях! Все связи будут потеряны.
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Это действие нельзя отменить.
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <form action="{{ route('catalog.tags.destroy', $tag->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Удалить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
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
        document.getElementById('editTagForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();

            if (!name) {
                e.preventDefault();
                alert('Пожалуйста, заполните название тега.');
                return;
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

    dl dt {
        font-weight: 600;
        color: #6c757d;
    }

    dl dd {
        margin-bottom: 0.5rem;
    }
</style>
@endpush
