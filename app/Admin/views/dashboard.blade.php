@extends('admin::layouts.default')

@section('title', admin_trans('app.dashboard') . ' | KotiksCMS')

@section('content')

    <!-- Карточки статистики -->
    <div class="stats-cards">
        <div class="stat-card fade-in delay-3">
            <div class="stat-icon" style="background-color: #fef3c7; color: #d97706;">
                <i class="bi bi-people"></i>
            </div>
            <h3>{{ $users_count }}</h3>
            <p>{{ admin_trans('app.user.users') }}</p>
        </div>
    </div>

    <!-- Основной контент в две колонки -->
    <div class="row">
        <div class="col-lg-4">
            <div class="card fade-in">
                <div class="card-header">
                    <h5 class="card-title mb-0">Активность системы</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex px-0 border-0">
                            <div class="flex-shrink-0 me-3">
                                <div
                                    class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-cart-check"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1" style="font-size: 0.85rem;">Новый заказ #ORD-7841</h6>
                                <p class="text-muted mb-0 small">2 минуты назад</p>
                            </div>
                        </div>
                        <div class="list-group-item d-flex px-0 border-0">
                            <div class="flex-shrink-0 me-3">
                                <div
                                    class="avatar-sm bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-person-check"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1" style="font-size: 0.85rem;">Новый пользователь зарегистрирован</h6>
                                <p class="text-muted mb-0 small">1 час назад</p>
                            </div>
                        </div>
                        <div class="list-group-item d-flex px-0 border-0">
                            <div class="flex-shrink-0 me-3">
                                <div
                                    class="avatar-sm bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1" style="font-size: 0.85rem;">Опубликована новая статья</h6>
                                <p class="text-muted mb-0 small">3 часа назад</p>
                            </div>
                        </div>
                        <div class="list-group-item d-flex px-0 border-0">
                            <div class="flex-shrink-0 me-3">
                                <div
                                    class="avatar-sm bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-arrow-repeat"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1" style="font-size: 0.85rem;">Резервное копирование выполнено</h6>
                                <p class="text-muted mb-0 small">5 часов назад</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
