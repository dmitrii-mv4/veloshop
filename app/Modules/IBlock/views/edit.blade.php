@extends('admin::layouts.default')

@section('title', 'Редактирование информационного блока | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Информационные блоки', 'url' => route('admin.iblock.index')],
                ['title' => 'Редактирование: ' . $iblock->title],
            ],
        ])
    </div>

    <div class="row fade-in">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-pencil me-2"></i> Редактирование информационного блока</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.iblock.update', $iblock) }}" method="POST" id="editIBlockForm">
                        @csrf
                        @method('PUT') <!-- Убедитесь, что это PUT -->

                        <!-- Поле: Название блока -->
                        <div class="mb-4">
                            <label for="title" class="form-label required">Название блока</label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title', $iblock->title) }}"
                                   placeholder="Введите название блока"
                                   maxlength="255"
                                   required>
                            <div class="char-counter">
                                <span id="titleCharCount">{{ mb_strlen(old('title', $iblock->title)) }}</span>/255
                            </div>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Поле: Содержимое блока -->
                        <div class="mb-4">
                            <label for="content" class="form-label required">Содержимое блока</label>
                            <textarea class="form-control @error('content') is-invalid @enderror content-editor" 
                                      id="content" 
                                      name="content" 
                                      rows="12"
                                      placeholder="Введите содержимое информационного блока..."
                                      required>{{ old('content', $iblock->content) }}</textarea>
                            <div class="char-counter">
                                <span id="contentCharCount">{{ mb_strlen(old('content', $iblock->content)) }}</span> символов
                            </div>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Кнопки действий -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <a href="{{ route('admin.iblock.index') }}" class="btn btn-outline-secondary me-2">
                                    <i class="bi bi-arrow-left me-1"></i> Назад
                                </a>
                                <button type="button" class="btn btn-outline-danger" id="deleteBtn"
                                        data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="bi bi-trash me-1"></i> В корзину
                                </button>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary" id="previewBtn">
                                    <i class="bi bi-eye me-1"></i> Предпросмотр
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Сохранить изменения
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Боковая панель с информацией -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> Информация о блоке</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>ID блока:</strong>
                        <div class="text-muted">{{ $iblock->id }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Статус:</strong>
                        <div>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i> Активен
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Дата создания:</strong>
                        <div class="text-muted">{{ $iblock->created_at->format('d.m.Y H:i') }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Дата обновления:</strong>
                        <div class="text-muted">{{ $iblock->updated_at->format('d.m.Y H:i') }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Автор:</strong>
                        <div class="text-muted">
                            @if($iblock->author)
                                {{ $iblock->author->name }}
                            @else
                                Система
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно предпросмотра -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewModalLabel">Предпросмотр информационного блока</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="previewContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно удаления в корзину -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Перемещение в корзину</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Вы уверены, что хотите переместить информационный блок <strong>"{{ $iblock->title }}"</strong> в корзину?</p>
                    <div class="alert alert-info alert-sm mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Блок будет доступен в корзине для восстановления в течение 30 дней
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <form action="{{ route('admin.iblock.destroy', $iblock) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i> В корзину
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
