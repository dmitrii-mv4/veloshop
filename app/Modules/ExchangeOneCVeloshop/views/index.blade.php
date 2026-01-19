@extends('admin::layouts.default')

@section('title', 'Товары из 1С Veloshop | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => '1С обмен'], 
                ['title' => 'Товары из 1С']
            ],
        ])
    </div>

    <!-- Вкладки: Товары и Настройки -->
    <div class="d-flex mb-4 fade-in">
        <div class="btn-group" role="group">
            <a href="{{ route('exchange1c.exchange.products.view') }}" class="btn btn-primary">
                <i class="bi bi-box-seam me-1"></i> Товары из 1С
            </a>
            <a href="{{ route('exchange1c.exchange.settings') }}" class="btn btn-outline-primary">
                <i class="bi bi-gear me-1"></i> Настройки обмена
            </a>
            <a href="{{ route('exchange1c.exchange.check') }}" class="btn btn-outline-info">
                <i class="bi bi-wifi me-1"></i> Проверка связи
            </a>
        </div>
    </div>

    <!-- Действия с товарами -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Товары из 1С Veloshop</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Последнее обновление: {{ now()->format('d.m.Y H:i:s') }}
                @if($success && isset($total))
                    | Получено товаров: {{ $total }}
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary" onclick="refreshData()">
                <i class="bi bi-arrow-clockwise me-1"></i> Обновить
            </button>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-download me-1"></i> Импортировать выбранные
            </button>
        </div>
    </div>

    <!-- Статус соединения -->
    @if(!$success)
        <div class="alert alert-danger fade-in mb-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                <div>
                    <h5 class="alert-heading mb-2">Ошибка получения данных!</h5>
                    <p class="mb-0">{{ $message }}</p>
                    <div class="mt-2">
                        <a href="{{ route('exchange1c.exchange.check') }}" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-wifi me-1"></i> Проверить соединение
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Карточка с фильтрами -->
        <div class="card fade-in mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('exchange1c.exchange.products.view') }}" class="row g-2">
                    <!-- Поиск по названию -->
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search', '') }}" class="form-control"
                                placeholder="Поиск по названию или артикулу...">
                        </div>
                    </div>

                    <!-- Лимит товаров -->
                    <div class="col-md-3">
                        <select name="limit" class="form-select form-select-sm" onchange="this.form.submit()">
                            @foreach ([3, 10, 25, 50] as $count)
                                <option value="{{ $count }}" {{ request('limit', 3) == $count ? 'selected' : '' }}>
                                    {{ $count }} товаров
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Таймаут -->
                    <div class="col-md-3">
                        <select name="timeout" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="5" {{ request('timeout', 30) == 5 ? 'selected' : '' }}>Таймаут: 5 сек</option>
                            <option value="15" {{ request('timeout', 30) == 15 ? 'selected' : '' }}>Таймаут: 15 сек</option>
                            <option value="30" {{ request('timeout', 30) == 30 ? 'selected' : '' }}>Таймаут: 30 сек</option>
                            <option value="60" {{ request('timeout', 30) == 60 ? 'selected' : '' }}>Таймаут: 60 сек</option>
                        </select>
                    </div>

                    <!-- Кнопки фильтрации -->
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill">
                            <i class="bi bi-funnel me-1"></i> Применить
                        </button>
                        <a href="{{ route('exchange1c.exchange.products.view') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Список товаров -->
        <div class="card fade-in">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Список товаров из 1С</h5>
                    <div class="text-muted small">
                        Показано {{ count($products) }} товаров
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="50">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                    </div>
                                </th>
                                <th width="40%">
                                    <div class="text-decoration-none d-flex align-items-center">
                                        Название товара
                                    </div>
                                </th>
                                <th width="15%">Артикул</th>
                                <th width="15%">ID модели</th>
                                <th width="15%">ID предложения</th>
                                <th width="15%" class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input product-checkbox" type="checkbox" 
                                                   value="{{ $product['offer_id'] }}" 
                                                   data-model="{{ $product['model_id'] }}"
                                                   data-articul="{{ $product['articul'] }}"
                                                   data-name="{{ $product['name'] }}">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3"
                                                style="width: 40px; height: 40px;">
                                                <i class="bi bi-box-seam"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $product['name'] }}</div>
                                                <div class="text-muted small">Источник: 1С API</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <code class="small bg-light p-1 rounded">{{ $product['articul'] }}</code>
                                    </td>
                                    <td>
                                        <span class="small" title="{{ $product['model_id'] }}">
                                            {{ substr($product['model_id'], 0, 15) }}...
                                        </span>
                                    </td>
                                    <td>
                                        <span class="small">{{ $product['offer_id'] }}</span>
                                    </td>
                                    <td>
                                        <div class="table-actions justify-content-end">
                                            <button type="button" class="btn btn-outline-primary btn-sm me-1" 
                                                    title="Просмотреть детали"
                                                    onclick="showProductDetails('{{ $product['offer_id'] }}')">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-sm" 
                                                    title="Импортировать в каталог"
                                                    onclick="importSingleProduct('{{ $product['offer_id'] }}', '{{ $product['articul'] }}')">
                                                <i class="bi bi-download"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-box-seam fs-4"></i>
                                            <p class="mt-2">Товары не найдены</p>
                                            @if (request()->has('search'))
                                                <a href="{{ route('exchange1c.exchange.products.view') }}" class="btn btn-primary btn-sm mt-2">
                                                    <i class="bi bi-arrow-clockwise me-1"></i> Сбросить фильтры
                                                </a>
                                            @else
                                                <button class="btn btn-primary btn-sm mt-2" onclick="refreshData()">
                                                    <i class="bi bi-arrow-clockwise me-1"></i> Попробовать снова
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Выбранные для импорта -->
            @if(count($products) > 0)
                <div class="card-footer border-0 bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <span id="selectedCount">0</span> товаров выбрано для импорта
                        </div>
                        <div>
                            <button type="button" class="btn btn-primary btn-sm" 
                                    data-bs-toggle="modal" data-bs-target="#importModal"
                                    id="importSelectedBtn" disabled>
                                <i class="bi bi-download me-1"></i> Импортировать выбранные
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Информационная панель -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> О работе с 1С</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2" style="font-size: 0.85rem;">
                            В этом разделе вы можете просматривать товары из системы 1С Veloshop.
                        </p>
                        <ul class="mb-0" style="font-size: 0.85rem;">
                            <li>Данные обновляются при каждом обновлении страницы</li>
                            <li>Для импорта товаров используйте кнопку "Импортировать"</li>
                            <li>Таймаут соединения можно настроить в фильтрах</li>
                            <li>Для проверки соединения перейдите в "Проверка связи"</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card api-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0"><i class="bi bi-code-slash me-2"></i> API обмена</h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- API проверки соединения -->
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-grow-1">
                                <div class="fw-semibold mb-1" style="font-size: 0.85rem;">
                                    <i class="bi bi-wifi me-1"></i> Проверка соединения
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <code class="p-2 bg-light rounded small api-endpoint flex-grow-1">
                                        {{ route('exchange1c.exchange.check') }}
                                    </code>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('exchange1c.exchange.check') }}" target="_blank" 
                                           class="btn btn-outline-primary btn-sm copy-btn" 
                                           title="Открыть API в новой вкладке">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                        <button class="btn btn-outline-secondary btn-sm copy-btn" 
                                                data-clipboard-text="{{ route('exchange1c.exchange.check') }}"
                                                title="Копировать URL API">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- API получения товаров -->
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="fw-semibold mb-1" style="font-size: 0.85rem;">
                                    <i class="bi bi-box-seam me-1"></i> Получение товаров
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <code class="p-2 bg-light rounded small api-endpoint flex-grow-1">
                                        {{ route('exchange1c.exchange.products') }}
                                    </code>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('exchange1c.exchange.products') }}" target="_blank" 
                                           class="btn btn-outline-primary btn-sm copy-btn" 
                                           title="Открыть API в новой вкладке">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                        <button class="btn btn-outline-secondary btn-sm copy-btn" 
                                                data-clipboard-text="{{ route('exchange1c.exchange.products') }}"
                                                title="Копировать URL API">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

<!-- Модальное окно деталей товара -->
<div class="modal fade" id="productDetailsModal" tabindex="-1" aria-labelledby="productDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productDetailsModalLabel">Детали товара</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="productDetailsContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Загрузка...</span>
                        </div>
                        <p class="mt-3">Загрузка данных о товаре...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" id="importFromModalBtn">
                    <i class="bi bi-download me-2"></i> Импортировать товар
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно импорта -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Импорт товаров из 1С</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="importSelectionInfo">
                    <p>Вы собираетесь импортировать <strong id="importCount">0</strong> товаров из 1С в каталог KotiksCMS.</p>
                    <div class="alert alert-info alert-sm">
                        <i class="bi bi-info-circle me-2"></i>
                        Товары будут созданы как новые позиции в каталоге
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label">Категория для импорта</label>
                    <select class="form-select form-select-sm" id="importCategory">
                        <option value="">-- Выберите категорию --</option>
                        <option value="new">Создать новую категорию</option>
                        <option value="default">Категория по умолчанию</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="startImport()">
                    <i class="bi bi-download me-2"></i> Начать импорт
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Индикатор загрузки -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 bg-transparent shadow-none">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Загрузка...</span>
                </div>
                <p class="mt-3 text-white bg-dark bg-opacity-75 p-2 rounded" id="loadingMessage">Обновление данных...</p>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .api-endpoint {
        font-family: 'Courier New', monospace;
        font-size: 0.8rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .table-actions .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .page-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding: 1rem 0;
        border-bottom: 1px solid var(--bs-border-color);
    }
    
    .fade-in {
        animation: fadeIn 0.5s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Выбор всех чекбоксов
        const selectAll = document.getElementById('selectAll');
        const productCheckboxes = document.querySelectorAll('.product-checkbox');
        
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                productCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAll.checked;
                });
                updateSelectedCount();
            });
        }
        
        // Обновление счетчика выбранных
        productCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedCount);
        });
        
        // Инициализация счетчика
        updateSelectedCount();
        
        // Копирование в буфер обмена
        const copyButtons = document.querySelectorAll('.copy-btn[data-clipboard-text]');
        copyButtons.forEach(button => {
            button.addEventListener('click', function() {
                const text = this.getAttribute('data-clipboard-text');
                navigator.clipboard.writeText(text).then(() => {
                    const originalHTML = this.innerHTML;
                    this.innerHTML = '<i class="bi bi-check"></i>';
                    this.classList.remove('btn-outline-secondary');
                    this.classList.add('btn-success');
                    
                    setTimeout(() => {
                        this.innerHTML = originalHTML;
                        this.classList.remove('btn-success');
                        this.classList.add('btn-outline-secondary');
                    }, 2000);
                });
            });
        });
    });
    
    // Обновление счетчика выбранных товаров
    function updateSelectedCount() {
        const selected = document.querySelectorAll('.product-checkbox:checked');
        const count = selected.length;
        
        document.getElementById('selectedCount').textContent = count;
        document.getElementById('importCount').textContent = count;
        
        const importBtn = document.getElementById('importSelectedBtn');
        if (importBtn) {
            importBtn.disabled = count === 0;
        }
        
        // Обновление списка выбранных для модального окна
        updateImportSelection();
    }
    
    // Обновление информации о выбранных товарах для импорта
    function updateImportSelection() {
        const selected = document.querySelectorAll('.product-checkbox:checked');
        const list = [];
        
        selected.forEach(checkbox => {
            list.push({
                offerId: checkbox.value,
                articul: checkbox.getAttribute('data-articul'),
                name: checkbox.getAttribute('data-name'),
                model: checkbox.getAttribute('data-model')
            });
        });
        
        // Сохраняем в глобальную переменную
        window.selectedProducts = list;
    }
    
    // Обновление данных
    function refreshData() {
        const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
        const loadingMessage = document.getElementById('loadingMessage');
        
        loadingMessage.textContent = 'Обновление данных из 1С...';
        loadingModal.show();
        
        // Добавляем параметры из формы фильтров
        const search = document.querySelector('input[name="search"]')?.value || '';
        const limit = document.querySelector('select[name="limit"]')?.value || 3;
        const timeout = document.querySelector('select[name="timeout"]')?.value || 30;
        
        // Обновляем страницу с параметрами
        window.location.href = '{{ route("exchange1c.exchange.products.view") }}' + 
                               '?search=' + encodeURIComponent(search) + 
                               '&limit=' + limit + 
                               '&timeout=' + timeout + 
                               '&refresh=' + Date.now();
    }
    
    // Показать детали товара
    function showProductDetails(offerId) {
        const modal = new bootstrap.Modal(document.getElementById('productDetailsModal'));
        const content = document.getElementById('productDetailsContent');
        
        // Загрузка данных
        content.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Загрузка...</span>
                </div>
                <p class="mt-3">Загрузка данных о товаре...</p>
            </div>
        `;
        
        // Здесь можно добавить AJAX запрос для получения деталей товара
        // Пример:
        // fetch('/api/exchange1c.exchange/product/' + offerId)
        //     .then(response => response.json())
        //     .then(data => {
        //         content.innerHTML = renderProductDetails(data);
        //     });
        
        // Заглушка
        setTimeout(() => {
            content.innerHTML = `
                <div>
                    <h6>Товар ID: ${offerId}</h6>
                    <p>Детальная информация о товаре будет доступна в следующей версии.</p>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Функция детального просмотра находится в разработке.
                    </div>
                </div>
            `;
        }, 500);
        
        modal.show();
    }
    
    // Импорт одного товара
    function importSingleProduct(offerId, articul) {
        if (confirm(`Вы уверены, что хотите импортировать товар ${articul}?`)) {
            const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
            const loadingMessage = document.getElementById('loadingMessage');
            
            loadingMessage.textContent = `Импорт товара ${articul}...`;
            loadingModal.show();
            
            // Здесь можно добавить AJAX запрос для импорта
            // Пример:
            // fetch('/api/exchange1c.exchange/import/' + offerId, { method: 'POST' })
            //     .then(response => response.json())
            //     .then(data => {
            //         loadingModal.hide();
            //         showImportResult(data);
            //     });
            
            // Заглушка
            setTimeout(() => {
                loadingModal.hide();
                alert(`Товар ${articul} успешно импортирован!\nФункция импорта будет доступна в следующей версии.`);
            }, 1500);
        }
    }
    
    // Начать импорт выбранных товаров
    function startImport() {
        const selected = window.selectedProducts || [];
        
        if (selected.length === 0) {
            alert('Выберите товары для импорта');
            return;
        }
        
        const category = document.getElementById('importCategory').value;
        if (!category) {
            alert('Выберите категорию для импорта');
            return;
        }
        
        if (confirm(`Импортировать ${selected.length} товаров в категорию "${category}"?`)) {
            const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
            const loadingMessage = document.getElementById('loadingMessage');
            
            loadingMessage.textContent = `Импорт ${selected.length} товаров...`;
            loadingModal.show();
            
            // Здесь можно добавить AJAX запрос для массового импорта
            // Пример:
            // fetch('/api/exchange1c.exchange/import/batch', {
            //     method: 'POST',
            //     headers: {'Content-Type': 'application/json'},
            //     body: JSON.stringify({ products: selected, category: category })
            // })
            
            // Заглушка
            setTimeout(() => {
                loadingModal.hide();
                const modal = bootstrap.Modal.getInstance(document.getElementById('importModal'));
                modal.hide();
                
                alert(`Успешно импортировано ${selected.length} товаров!\nФункция импорта будет доступна в следующей версии.`);
                
                // Сброс выбора
                document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = false);
                document.getElementById('selectAll').checked = false;
                updateSelectedCount();
            }, 2000);
        }
    }
    
    // Автоматическое обновление каждые 5 минут
    setTimeout(function() {
        if ({{ $success ? 'true' : 'false' }} && confirm('Прошло 5 минут. Обновить данные из 1С?')) {
            refreshData();
        }
    }, 300000); // 5 минут
</script>
@endpush