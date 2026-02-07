@extends('admin::layouts.default')

@section('title', 'Редактирование склада | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Каталог', 'url' => route('catalog.index')],
                ['title' => 'Склады', 'url' => route('catalog.warehouses.index')],
                ['title' => 'Редактирование: ' . $warehouse->title],
            ],
        ])
    </div>

    <!-- Вкладки: Товары, Предложения, Склады -->
    <div class="d-flex mb-4 fade-in">
        <div class="btn-group" role="group">
            <a href="{{ route('catalog.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-box-seam me-1"></i> Товары
            </a>
            <a href="{{ route('catalog.warehouses.index') }}" class="btn btn-primary">
                <i class="bi bi-house-door me-1"></i> Склады
            </a>
        </div>
    </div>

    <!-- Основной контент -->
    <div class="row fade-in">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-house-door-gear me-2"></i> Редактирование склада
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('catalog.warehouses.update', $warehouse) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Название склада -->
                        <div class="mb-4">
                            <label for="title" class="form-label required">
                                Название склада
                            </label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title', $warehouse->title) }}"
                                   placeholder="Введите название склада"
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Укажите уникальное название склада для идентификации
                            </div>
                        </div>

                        <!-- Внешний ID склада -->
                        <div class="mb-4">
                            <label for="title" class="form-label required">
                                Внешний ID склада из 1С
                            </label>
                            <input type="text" 
                                   class="form-control @error('warehouse_id') is-invalid @enderror" 
                                   id="warehouse_id" 
                                   name="warehouse_id" 
                                   value="{{ old('warehouse_id', $warehouse->warehouse_id) }}"
                                   placeholder="Введите внешний id склада"
                                   required>
                            @error('warehouse_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Описание склада -->
                        <div class="mb-4">
                            <label for="description" class="form-label">
                                Описание склада
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4"
                                      placeholder="Введите описание склада (адрес, особенности и т.д.)">{{ old('description', $warehouse->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Дополнительная информация о складе
                            </div>
                        </div>

                        <!-- Контактная информация -->
                        <div class="mb-4">
                            <label for="contacts" class="form-label">
                                Контактная информация
                            </label>
                            <textarea class="form-control @error('contacts') is-invalid @enderror" 
                                      id="contacts" 
                                      name="contacts" 
                                      rows="3"
                                      placeholder="Введите контактную информацию (телефоны, ответственные лица и т.д.)">{{ old('contacts', $warehouse->contacts) }}</textarea>
                            @error('contacts')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Контакты для связи по вопросам склада
                            </div>
                        </div>

                        <!-- Сортировка -->
                        <div class="mb-4">
                            <label for="title" class="form-label">
                                Сортировка
                            </label>
                            <input type="number" 
                                   class="form-control @error('sort_order') is-invalid @enderror" 
                                   id="sort_order" 
                                   name="sort_order" 
                                   value="{{ old('sort_order', $warehouse->sort_order) }}">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Статус активности -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input @error('is_active') is-invalid @enderror" 
                                       type="checkbox" 
                                       role="switch" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $warehouse->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Склад активен
                                </label>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">
                                Неактивные склады не отображаются при выборе в заказах
                            </div>
                        </div>

                        <!-- Информация о создании/обновлении -->
                        <div class="card bg-light mb-4">
                            <div class="card-body py-2">
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="bi bi-person-plus me-1"></i>
                                            Создал: 
                                            @if($warehouse->creator)
                                                {{ $warehouse->creator->name }}
                                            @else
                                                Система
                                            @endif
                                            ({{ $warehouse->created_at->format('d.m.Y H:i') }})
                                        </small>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <small class="text-muted">
                                            <i class="bi bi-person-check me-1"></i>
                                            Обновил: 
                                            @if($warehouse->editor)
                                                {{ $warehouse->editor->name }}
                                            @else
                                                Не обновлялся
                                            @endif
                                            @if($warehouse->updated_at->gt($warehouse->created_at))
                                                ({{ $warehouse->updated_at->format('d.m.Y H:i') }})
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Кнопки формы -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <a href="{{ route('catalog.warehouses.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> Назад к списку
                                </a>
                                <button type="button" class="btn btn-outline-danger ms-2" 
                                        data-bs-toggle="modal" data-bs-target="#deleteWarehouseModal">
                                    <i class="bi bi-trash me-1"></i> Удалить
                                </button>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i> Сохранить изменения
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Боковая панель с информацией -->
        <div class="col-lg-4">
            <!-- Информация о складе -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i> Информация о складе
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                            <i class="bi bi-house-door fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">{{ $warehouse->title }}</div>
                            <div class="text-muted small">ID: {{ $warehouse->id }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted mb-1">Статус</div>
                        <span class="badge bg-{{ $warehouse->is_active ? 'success' : 'secondary' }}">
                            <i class="bi bi-{{ $warehouse->is_active ? 'check-circle' : 'x-circle' }} me-1"></i>
                            {{ $warehouse->is_active ? 'Активен' : 'Неактивен' }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted mb-1">Товаров на складе</div>
                        <div class="fw-semibold">{{ $warehouse->unique_offers_count }} позиций</div>
                        <div class="text-muted small">{{ $warehouse->total_products_count }} единиц</div>
                    </div>

                    @if($warehouse->description)
                        <div class="mb-3">
                            <div class="small text-muted mb-1">Описание</div>
                            <div class="small">{{ $warehouse->description }}</div>
                        </div>
                    @endif

                    @if($warehouse->contacts)
                        <div class="mb-0">
                            <div class="small text-muted mb-1">Контакты</div>
                            <div class="small">{{ $warehouse->contacts }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Быстрые действия -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-lightning me-2"></i> Быстрые действия
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('catalog.warehouses.toggle-status', $warehouse) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-{{ $warehouse->is_active ? 'warning' : 'success' }} w-100 mb-2">
                            <i class="bi bi-toggle-{{ $warehouse->is_active ? 'off' : 'on' }} me-1"></i>
                            {{ $warehouse->is_active ? 'Деактивировать' : 'Активировать' }} склад
                        </button>
                    </form>

                    <a href="{{ route('catalog.warehouses.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-list me-1"></i> К списку складов
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Обработка удаления склада
        const deleteForm = document.getElementById('deleteWarehouseForm');
        const warehouseTitleSpan = document.getElementById('warehouseTitleToDelete');
        
        // Устанавливаем данные для модального окна удаления
        warehouseTitleSpan.textContent = '{{ $warehouse->title }}';
        deleteForm.action = '{{ route("catalog.warehouses.destroy", $warehouse) }}';
    });
</script>
@endsection

<!-- Модальное окно подтверждения удаления -->
<div class="modal fade" id="deleteWarehouseModal" tabindex="-1" aria-labelledby="deleteWarehouseModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteWarehouseModalLabel">Удаление склада</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить склад <strong id="warehouseTitleToDelete"></strong>?</p>
                <div class="alert alert-danger alert-sm mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Внимание! Это действие невозможно отменить. Все данные о количестве товаров на этом складе будут удалены.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form id="deleteWarehouseForm" method="POST" class="d-inline">
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