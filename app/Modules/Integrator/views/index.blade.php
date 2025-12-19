@extends('admin::layouts.default')

@section('title', 'Интеграции с внешними сервисами | KotiksCMS')

@section('content')

    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        
        <!-- Подключаем breadcrumb -->
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Интеграции с внешними сервисами']
            ]
        ])
    </div>

    <!-- Действия с модулями -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Интеграции с внешними сервисами</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">Управление подключениями к внешним API сервисам</p>
        </div>
        <a href="{{ route('admin.integration.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Создать интеграцию
        </a>
    </div>

    <!-- Список модулей -->
    <div class="card fade-in">
        <div class="card-header">
            <div class="modules-list-header">
                <h5 class="card-title mb-0">Список модулей</h5>
                <div class="modules-count">Показано 1 из 1 модулей</div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="modules-table">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="25%">Название интеграции</th>
                            <th width="10%">Модуль</th>
                            <th width="10%">Тип сервиса</th>
                            <th width="10%">Статус</th>
                            <th width="15%">Последнее обновление</th>
                            <th width="10%">Дата создания</th>
                            <th width="25%" class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- @forelse($modules as $module) --}}
                            <tr>
                                <td>
                                    <div class="module-name">Интеграция с 1С</div>
                                    <div class="module-description"></div>
                                </td>
                                <td>
                                    <span class="badge badge-violet"
                                        style="font-size: 0.85rem;">Каталог</span>
                                </td>
                                <td>
                                    <span class="badge badge-blue">
                                        Telegram
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                        <i class="fas fa-check-circle me-1"></i>Активна
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted"
                                        style="font-size: 0.85rem;">15.11.2023 14:30</span>
                                </td>
                                <td>
                                    <span class="text-muted"
                                        style="font-size: 0.85rem;">15.11.2023 14:30</span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href=""
                                            class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-box-arrow-in-right me-1"></i> Перейти
                                        </a>
                                        <form action="" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm delete-module-btn">
                                                <i class="bi bi-trash me-1"></i> Удалить
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        {{-- @empty --}}
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox fs-4"></i>
                                        <p class="mt-2">Ещё пока нет интеграций</p>
                                        <a href="{{ route('admin.integration.create') }}" class="btn btn-primary btn-sm mt-2">
                                            <i class="bi bi-plus-circle me-1"></i> Создать первую интеграцию
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        {{-- @endforelse --}}
                    </tbody>
                </table>
                <!-- Пагинация -->
                <div class="card-footer border-0 bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Показано 1 из 1 модулей
                        </div>
                        {{-- <div>
                            <nav aria-label="Навигация по страницам">
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" tabindex="-1">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Информационная панель -->
    <div class="row mt-4 fade-in">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i>О интеграциях</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2" style="font-size: 0.85rem;">Интеграции позволяют подключить ваш сайт к внешним сервисам через API. Вы можете настроить автоматический обмен данными, синхронизацию и другие взаимодействия. Каждая интеграция имеет уникальные настройки и может быть активирована или деактивирована в любое время.</p>
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li>Создавайте новые интеграции</li>
                        <li>Управляйте существующими интеграциями</li>
                        <li>Активируйте и деактивируйте по необходимости</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    




    <!--- END ---->

    {{-- </div> --}}

    <!-- Модальное окно подтверждения удаления (пример для интеграции 1) -->
    <div class="modal fade" id="deleteModal1" tabindex="-1" aria-labelledby="deleteModalLabel1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Подтверждение удаления
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body py-4">
                    <p>Вы уверены, что хотите удалить интеграцию <strong>"Telegram Bot API"</strong>?</p>
                    <p class="text-muted small mb-0">Это действие нельзя будет отменить. Все настройки и данные этой интеграции будут удалены.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete1">Удалить</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно подтверждения удаления (пример для интеграции 2) -->
    <div class="modal fade" id="deleteModal2" tabindex="-1" aria-labelledby="deleteModalLabel2" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Подтверждение удаления
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body py-4">
                    <p>Вы уверены, что хотите удалить интеграцию <strong>"Email рассылка"</strong>?</p>
                    <p class="text-muted small mb-0">Это действие нельзя будет отменить. Все настройки и данные этой интеграции будут удалены.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete2">Удалить</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно подтверждения удаления (пример для интеграции 3) -->
    <div class="modal fade" id="deleteModal3" tabindex="-1" aria-labelledby="deleteModalLabel3" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Подтверждение удаления
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body py-4">
                    <p>Вы уверены, что хотите удалить интеграцию <strong>"Платежная система"</strong>?</p>
                    <p class="text-muted small mb-0">Это действие нельзя будет отменить. Все настройки и данные этой интеграции будут удалены.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete3">Удалить</button>
                </div>
            </div>
        </div>
    </div>

@endsection