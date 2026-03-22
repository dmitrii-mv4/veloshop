@extends('admin::layouts.default')

@section('title', 'Создание заказа | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Каталог', 'url' => route('catalog.index')],
                ['title' => 'Заказы', 'url' => route('catalog.orders.index')],
                ['title' => 'Создание заказа']
            ],
        ])
    </div>

    <!-- Заголовок формы -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Создание нового заказа</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Заполните форму ниже для добавления нового заказа
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('catalog.orders.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    <!-- Форма создания заказа -->
    <form action="{{ route('catalog.orders.store') }}" method="POST" id="order-form">
        @csrf
        
        <div class="row fade-in">
            <!-- Основные поля -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-pencil-square me-2"></i> Основная информация</h6>
                    </div>
                    <div class="card-body">
                        <!-- Номер заказа -->
                        <div class="mb-4">
                            <label for="order_number" class="form-label required">
                                Номер заказа
                            </label>
                            <input type="text" 
                                   class="form-control @error('order_number') is-invalid @enderror" 
                                   id="order_number" 
                                   name="order_number" 
                                   value="{{ old('order_number', $orderNumber) }}" 
                                   required
                                   maxlength="100"
                                   placeholder="Введите номер заказа">
                            <div class="form-text">
                                <small>Уникальный номер заказа для идентификации</small>
                            </div>
                            @error('order_number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Покупатель -->
                        <div class="mb-3">
                            <label for="customer_id" class="form-label required">
                                Покупатель
                            </label>
                            <select class="form-select @error('customer_id') is-invalid @enderror" 
                                    id="customer_id" 
                                    name="customer_id"
                                    required>
                                <option value="">— Выберите покупателя —</option>
                                @foreach($customers as $id => $name)
                                    <option value="{{ $id }}" {{ old('customer_id') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Сумма заказа -->
                        <div class="mb-3">
                            <label for="total_amount" class="form-label required">
                                Сумма заказа
                            </label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control @error('total_amount') is-invalid @enderror" 
                                       id="total_amount" 
                                       name="total_amount" 
                                       value="{{ old('total_amount', 0) }}" 
                                       required
                                       min="0"
                                       step="0.01"
                                       placeholder="0.00">
                                <span class="input-group-text">₽</span>
                            </div>
                            @error('total_amount')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Ответственный -->
                        <div class="mb-3">
                            <label for="responsible_id" class="form-label">
                                Ответственный
                            </label>
                            <select class="form-select @error('responsible_id') is-invalid @enderror" 
                                    id="responsible_id" 
                                    name="responsible_id">
                                <option value="">— Не назначен —</option>
                                @foreach($responsibles as $id => $name)
                                    <option value="{{ $id }}" {{ old('responsible_id') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('responsible_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Комментарий -->
                        <div class="mb-3">
                            <label for="comment" class="form-label">
                                Комментарий
                            </label>
                            <textarea class="form-control @error('comment') is-invalid @enderror" 
                                      id="comment" 
                                      name="comment" 
                                      rows="3"
                                      maxlength="2000"
                                      placeholder="Дополнительная информация о заказе">{{ old('comment') }}</textarea>
                            <div class="char-counter mt-1">
                                <span id="comment-counter">0</span>/2000 символов
                            </div>
                            @error('comment')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Статусы заказа -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-flag me-2"></i> Статусы заказа</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Оплачен -->
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_paid" 
                                           name="is_paid" 
                                           value="1"
                                           {{ old('is_paid') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_paid">
                                        <i class="bi bi-credit-card text-success me-1"></i> Оплачен
                                    </label>
                                </div>
                            </div>

                            <!-- Отменён -->
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_cancelled" 
                                           name="is_cancelled" 
                                           value="1"
                                           {{ old('is_cancelled') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_cancelled">
                                        <i class="bi bi-x-circle text-danger me-1"></i> Отменён
                                    </label>
                                </div>
                            </div>

                            <!-- Проблема -->
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="has_problem" 
                                           name="has_problem" 
                                           value="1"
                                           {{ old('has_problem') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="has_problem">
                                        <i class="bi bi-exclamation-triangle text-warning me-1"></i> Проблема
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Блок: Причина отмены -->
                        <div class="cancellation-reason-field">
                            <label for="cancellation_reason" class="form-label">
                                <i class="bi bi-chat-left-text me-1"></i> Причина отмены заказа
                            </label>
                            <textarea class="form-control @error('cancellation_reason') is-invalid @enderror" 
                                      id="cancellation_reason" 
                                      name="cancellation_reason" 
                                      rows="4"
                                      maxlength="2000"
                                      placeholder="Подробно укажите причину отмены заказа...">{{ old('cancellation_reason') }}</textarea>
                            <div class="char-counter mt-1">
                                <span id="cancellation-reason-counter">0</span>/2000 символов
                            </div>
                            <div class="form-text">
                                <small>Заполните, если заказ отменён</small>
                            </div>
                            @error('cancellation_reason')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Блок: Описание проблемы -->
                        <div class="problem-description-field mt-3">
                            <label for="problem_description" class="form-label">
                                <i class="bi bi-exclamation-octagon me-1"></i> Описание проблемы с заказом
                            </label>
                            <textarea class="form-control @error('problem_description') is-invalid @enderror" 
                                      id="problem_description" 
                                      name="problem_description" 
                                      rows="4"
                                      maxlength="2000"
                                      placeholder="Подробно опишите проблему с заказом...">{{ old('problem_description') }}</textarea>
                            <div class="char-counter mt-1">
                                <span id="problem-description-counter">0</span>/2000 символов
                            </div>
                            <div class="form-text">
                                <small>Заполните, если есть проблемы с заказом</small>
                            </div>
                            @error('problem_description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
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
                        <div class="alert alert-info alert-sm mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <small>Заказ будет создан от вашего имени как автора</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small">Автор</label>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-2"
                                     style="width: 32px; height: 32px;">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ auth()->user()->name }}</div>
                                    <small class="text-muted">{{ auth()->user()->email }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- Статистика покупателей -->
                        <div class="mb-3 pt-3 border-top">
                            <label class="form-label small">Статистика покупателей</label>
                            <div class="small text-muted">
                                <div class="d-flex justify-content-between">
                                    <span>Всего покупателей:</span>
                                    <span class="fw-semibold">{{ $customers->count() }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Активных ответственных:</span>
                                    <span class="fw-semibold text-success">{{ $responsibles->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i> Сохранить заказ
                            </button>
                            <button type="reset" class="btn btn-outline-secondary" id="reset-form">
                                <i class="bi bi-arrow-clockwise me-2"></i> Очистить форму
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('styles')
<style>
.char-counter {
    font-size: 0.75rem;
    color: #6c757d;
    text-align: right;
}

.char-counter span {
    font-weight: 600;
}

.cancellation-reason-field,
.problem-description-field {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
    border-left: 4px solid #dc3545;
}

.problem-description-field {
    border-left-color: #ffc107;
}

.required:after {
    content: " *";
    color: #dc3545;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Счетчики символов
    const commentInput = document.getElementById('comment');
    const cancellationReasonInput = document.getElementById('cancellation_reason');
    const problemDescriptionInput = document.getElementById('problem_description');
    
    const commentCounter = document.getElementById('comment-counter');
    const cancellationReasonCounter = document.getElementById('cancellation-reason-counter');
    const problemDescriptionCounter = document.getElementById('problem-description-counter');
    
    function updateCounter(input, counter) {
        if (input && counter) {
            counter.textContent = input.value.length;
        }
    }
    
    if (commentInput && commentCounter) {
        commentInput.addEventListener('input', () => updateCounter(commentInput, commentCounter));
        updateCounter(commentInput, commentCounter);
    }
    
    if (cancellationReasonInput && cancellationReasonCounter) {
        cancellationReasonInput.addEventListener('input', () => updateCounter(cancellationReasonInput, cancellationReasonCounter));
        updateCounter(cancellationReasonInput, cancellationReasonCounter);
    }
    
    if (problemDescriptionInput && problemDescriptionCounter) {
        problemDescriptionInput.addEventListener('input', () => updateCounter(problemDescriptionInput, problemDescriptionCounter));
        updateCounter(problemDescriptionInput, problemDescriptionCounter);
    }
    
    // Очистка формы
    const resetButton = document.getElementById('reset-form');
    if (resetButton) {
        resetButton.addEventListener('click', function() {
            setTimeout(() => {
                if (commentInput && commentCounter) updateCounter(commentInput, commentCounter);
                if (cancellationReasonInput && cancellationReasonCounter) updateCounter(cancellationReasonInput, cancellationReasonCounter);
                if (problemDescriptionInput && problemDescriptionCounter) updateCounter(problemDescriptionInput, problemDescriptionCounter);
                
                // Сброс выбора покупателя и ответственного
                const customerSelect = document.getElementById('customer_id');
                const responsibleSelect = document.getElementById('responsible_id');
                if (customerSelect) customerSelect.selectedIndex = 0;
                if (responsibleSelect) responsibleSelect.selectedIndex = 0;
                
                // Сброс чекбоксов
                const isCancelledCheckbox = document.getElementById('is_cancelled');
                const hasProblemCheckbox = document.getElementById('has_problem');
                const isPaidCheckbox = document.getElementById('is_paid');
                
                if (isCancelledCheckbox) isCancelledCheckbox.checked = false;
                if (hasProblemCheckbox) hasProblemCheckbox.checked = false;
                if (isPaidCheckbox) isPaidCheckbox.checked = false;
            }, 0);
        });
    }
});
</script>
@endpush