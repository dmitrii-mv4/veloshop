@extends('admin::layouts.default')

@section('title', 'Пункты меню | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Меню', 'url' => route('admin.menu.index')],
                ['title' => $menu->name]
            ],
        ])
    </div>

    <!-- Действия с пунктами меню -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">{{ $menu->name }}</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Тип меню: <span class="badge bg-info">{{ $menu->type }}</span> | 
                Пунктов: {{ $items->count() }} | 
                Статус: 
                @if($menu->is_active)
                    <span class="badge bg-success">Активно</span>
                @else
                    <span class="badge bg-secondary">Не активно</span>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.menu.edit', $menu) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-1"></i> Настройки меню
            </a>
            <a href="{{ route('admin.menu.items.create', $menu) }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Добавить пункт
            </a>
        </div>
    </div>

    <!-- Информация о меню -->
    <div class="row mb-4 fade-in">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-info-circle text-primary fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            @if($menu->description)
                                <p class="mb-0" style="font-size: 0.9rem;">{{ $menu->description }}</p>
                            @else
                                <p class="mb-0 text-muted" style="font-size: 0.9rem;">Описание отсутствует</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Всего пунктов</div>
                            <div class="h4 mb-0">{{ $items->count() }}</div>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-list-ul fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Древовидный список пунктов меню -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Пункты меню</h5>
                <div class="text-muted small">
                    @if($items->count() > 0)
                        Отсортировано по порядку
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @if($items->isEmpty())
                <div class="text-center py-5">
                    <div class="text-muted">
                        <i class="bi bi-list-check fs-1"></i>
                        <p class="mt-3">В этом меню еще нет пунктов</p>
                        <a href="{{ route('admin.menu.items.create', $menu) }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Добавить первый пункт
                        </a>
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="35%">Пункт меню</th>
                                <th width="20%">URL</th>
                                <th width="15%">Статус</th>
                                <th width="15%">Сортировка</th>
                                <th width="10%" class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                @include('menu::menuitem.partials.item-row', [
                                    'item' => $item, 
                                    'level' => 0, 
                                    'menu' => $menu
                                ])
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if($items->count() > 10)
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Всего {{ $items->count() }} пунктов
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Справка -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-question-circle me-2"></i> Управление пунктами</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li>Используйте иконки Bootstrap для визуального оформления</li>
                        <li>Создавайте вложенные пункты через поле "Родительский пункт"</li>
                        <li>Порядок сортировки: меньшее число = выше в списке</li>
                        <li>URL можно указывать как абсолютные, так и относительные</li>
                        <li>SEO заголовок используется для метатега title ссылки</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-lightning-charge me-2"></i> Быстрые действия</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.menu.items.create', $menu) }}?parent_id=0" class="btn btn-outline-primary">
                            <i class="bi bi-plus-lg me-2"></i> Добавить корневой пункт
                        </a>
                        <button type="button" class="btn btn-outline-secondary" onclick="window.location.reload()">
                            <i class="bi bi-arrow-clockwise me-2"></i> Обновить список
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modals')
    <!-- Модальное окно подтверждения удаления пункта -->
    <div class="modal fade" id="deleteMenuItemModal" tabindex="-1" aria-labelledby="deleteMenuItemModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteMenuItemModalLabel">Удаление пункта меню</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Вы уверены, что хотите удалить пункт меню <strong id="menuItemTitleToDelete"></strong>?</p>
                    <div class="alert alert-danger alert-sm mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Внимание! При удалении пункта будут удалены все его дочерние пункты. Это действие нельзя отменить.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <form id="deleteMenuItemForm" method="POST" class="d-inline">
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

@section('styles')
<style>
    .toast-container {
        z-index: 1060;
    }
    
    .table-actions {
        display: flex;
        gap: 0.25rem;
        justify-content: flex-end;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    /* Адаптивные стили для таблицы */
    @media (max-width: 768px) {
        .table-responsive {
            margin: 0 -1rem;
            width: calc(100% + 2rem);
        }
        
        .table th,
        .table td {
            padding: 0.5rem;
            font-size: 0.875rem;
        }
        
        .table-actions {
            flex-direction: column;
            gap: 0.125rem;
        }
        
        .table-actions .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .table-actions .btn .bi {
            margin: 0 !important;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Инициализируем обработчики для модального окна удаления
        const deleteModal = document.getElementById('deleteMenuItemModal');
        
        if (deleteModal) {
            // Обработчик показа модального окна
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget; // Кнопка, которая открыла модальное окно
                const itemId = button.getAttribute('data-item-id');
                const itemTitle = button.getAttribute('data-item-title');
                const deleteUrl = button.getAttribute('data-delete-url');
                
                // Обновляем содержимое модального окна
                const modalTitle = deleteModal.querySelector('#menuItemTitleToDelete');
                const deleteForm = deleteModal.querySelector('#deleteMenuItemForm');
                
                if (modalTitle) {
                    modalTitle.textContent = itemTitle;
                }
                
                if (deleteForm) {
                    deleteForm.action = deleteUrl;
                }
            });
        }
        
        // Обработка отправки формы удаления с индикацией загрузки
        const deleteForms = document.querySelectorAll('form[action*="menuitem"]');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitButton = this.querySelector('button[type="submit"]');
                if (submitButton) {
                    const originalText = submitButton.innerHTML;
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Удаление...';
                    
                    // Восстановить кнопку, если форма не отправится
                    setTimeout(() => {
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalText;
                    }, 5000);
                }
            });
        });
    });
</script>
@endsection