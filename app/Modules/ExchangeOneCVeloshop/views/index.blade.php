@extends('admin::layouts.default')

@section('title', '1С обмен | KotiksCMS')

@section('content')
    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => '1С обмен'],
            ],
        ])
    </div>

    <!-- Вкладки: Товары и Настройки -->
    {{--<div class="d-flex mb-4 fade-in">
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
    </div>--}}

    <!-- Действия с товарами -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Товары из 1С Veloshop</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Последнее обновление: {{ now()->format('d.m.Y H:i:s') }}
                {{--@if($success && isset($total))--}}
                    | Получено товаров: {{--{{ $total }}--}}
                {{--@endif--}}
            </p>
        </div>
        <div class="d-flex gap-2">
            {{--<button type="button" class="btn btn-outline-primary" onclick="refreshData()">
                <i class="bi bi-arrow-clockwise me-1"></i> Обновить
            </button>--}}
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-download me-1"></i> Запустить импорт
            </button>
        </div>
    </div>

    <!-- Статус соединения -->
    {{--@if(!$connectionHealth)
        <div class="alert alert-danger fade-in mb-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                <div>
                    <h5 class="alert-heading mb-2">API 1С в данный момент недоступен</h5>
                    <div class="mt-2">
                        <a href="{{ route('exchange1c.index') }}" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-wifi me-1"></i> Перепроверить соединение
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info fade-in mb-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                <div>
                    <h5 class="alert-heading mb-2">API 1С доступен</h5>
                    <div class="mt-2">
                        <a href="{{ route('exchange1c.index') }}" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-wifi me-1"></i> Перепроверить соединение
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif--}}
@endsection

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

    });

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
</script>
@endpush
