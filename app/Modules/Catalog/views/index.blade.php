@extends('admin::layouts.default')

@section('title', 'Каталог товаров | KotiksCMS')

@section('content')

    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [['title' => 'Каталог товаров']],
        ])
    </div>

    <!-- Действия с каталогом -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Каталог товаров</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                Всего: 0 | В наличии: 0 | С ценами: 0
            </p>
        </div>
        <button id="catalogSyncBtn" class="btn btn-primary catalog-sync-btn">
            <span class="spinner spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <i class="bi bi-cloud-download"></i> Выгрузить из 1С
        </button>
    </div>

    <!-- Список товаров -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Список товаров</h5>
                <div class="text-muted small">
                    Показано 0 из 0 товаров
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <tbody>
                       
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3"
                                            style="width: 36px; height: 36px;">
                                            <i class="bi bi-box" style="font-size: 1rem;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold" title="">
                                                Название
                                            </div>
                                            <div class="text-muted small">
                                                
                                                    Номенкл.: 
                                               
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="mb-1">
                                        <code>Не указан</code>
                                    </div>
                                </td>
                                <td>
                                    Цена
                                </td>
                                <td>
                                    <div class="fw-semibold">
                                        33 шт.
                                    </div>
                                </td>
                                <td>
                                    
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                            <i class="bi bi-check-circle me-1"></i> В наличии
                                        </span>
                                    
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                            <i class="bi bi-x-circle me-1"></i> Нет в наличии
                                        </span>
                             
                                </td>
                                <td>
                                    <div class="catalog-table-actions justify-content-end">
                                        <button type="button" class="btn btn-outline-info btn-sm me-1 catalog-view-details-btn"
                                            title="Просмотреть детали" data-bs-toggle="modal">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                  
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-box fs-4"></i>
                                        <p class="mt-2">Товары не найдены</p>
                                        @if (request()->hasAny(['search', 'in_stock', 'min_price', 'max_price']))
                                            <a href="{{ route('catalog.index') }}" class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-arrow-clockwise me-1"></i> Сбросить фильтры
                                            </a>
                                        @else
                                            <button id="catalogSyncBtnEmpty" class="btn btn-primary btn-sm mt-2 catalog-sync-btn">
                                                <span class="spinner spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                <i class="bi bi-cloud-download me-1"></i> Выгрузить товары из 1С
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
           
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Пагинация -->
 
            <div class="card-footer border-0 bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Показано 0 - 0 из 0 товаров
                    </div>
                    <div>
                        
                    </div>
                </div>
            </div>
    </div>

@endsection

<!-- Модальное окно деталей товара -->
<div class="modal fade" id="productDetailsModal" tabindex="-1" aria-labelledby="productDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productDetailsModalLabel">Детальная информация о товаре</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Основная информация</h6>
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <th width="40%">Название:</th>
                                    <td id="modal-name"></td>
                                </tr>
                                <tr>
                                    <th>Код ИМ:</th>
                                    <td id="modal-code"></td>
                                </tr>
                                <tr>
                                    <th>Код номенклатуры:</th>
                                    <td id="modal-ncode"></td>
                                </tr>
                                <tr>
                                    <th>Цена:</th>
                                    <td id="modal-price"></td>
                                </tr>
                                <tr>
                                    <th>Старая цена:</th>
                                    <td id="modal-old-price"></td>
                                </tr>
                                <tr>
                                    <th>Количество:</th>
                                    <td id="modal-stock"></td>
                                </tr>
                                <tr>
                                    <th>Наличие:</th>
                                    <td id="modal-in-stock"></td>
                                </tr>
                                <tr>
                                    <th>Обновлено:</th>
                                    <td id="modal-synced"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Дополнительная информация</h6>
                        <div id="modal-properties" class="mb-3">
                            <h6 class="small text-muted">Свойства:</h6>
                            <div id="properties-list"></div>
                        </div>
                        <div id="modal-warehouses">
                            <h6 class="small text-muted">Остатки по складам:</h6>
                            <div id="warehouses-list"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Каталог: уникальные стили */
    .catalog-sync-btn {
        position: relative;
    }
    .catalog-sync-btn .spinner {
        display: none;
    }
    .catalog-sync-btn.catalog-syncing .spinner {
        display: inline-block;
    }
    .catalog-sync-btn.catalog-syncing i {
        display: none;
    }
    .catalog-view-details-btn:hover {
        background-color: #0dcaf0;
        border-color: #0dcaf0;
        color: white;
    }
    .catalog-table-actions {
        display: flex;
        gap: 0.25rem;
    }
    .catalog-notification {
        margin-top: 1rem;
        z-index: 9999;
        position: fixed;
        top: 20px;
        right: 20px;
        max-width: 400px;
    }
    
    /* Каталог: мобильная адаптация */
    @media (max-width: 768px) {
        .catalog-filters-form .col-md-3,
        .catalog-filters-form .col-md-2,
        .catalog-filters-form .col-md-1 {
            margin-bottom: 10px;
        }
        
        .catalog-product-table {
            font-size: 14px;
        }
        
        .catalog-product-table th,
        .catalog-product-table td {
            padding: 8px 5px;
        }
    }
</style>