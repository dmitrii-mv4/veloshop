@extends('admin::layouts.default')

@section('title', 'Редактировать пользователя | KotiksCMS')

@section('content')

    <!-- Заголовок страницы -->
    <div class="page-header fade-in">

        <!-- Подключаем breadcrumb -->
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Пользователи', 'url' => route('admin.users')],
                ['title' => 'Редактировать пользователя'],
            ],
        ])
    </div>

    <!-- Действия -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Редактировать пользователя</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">Измените данные пользователя {{ $user->name }}</p>
        </div>
        <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Вернуться к списку
        </a>
    </div>

    <!-- Форма редактирования -->
    <div class="row fade-in">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Основная информация</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}" id="editUserForm">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <!-- Имя пользователя -->
                            <div class="col-md-6">
                                <label for="name" class="form-label required">Имя пользователя</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                    class="form-control @error('name') is-invalid @enderror"
                                    placeholder="Введите имя пользователя" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label for="email" class="form-label required">Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                    class="form-control @error('email') is-invalid @enderror" placeholder="user@example.com"
                                    required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Пароль (опционально) -->
                            <div class="col-md-6">
                                <label for="password" class="form-label">Новый пароль</label>
                                <input type="password" name="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Оставьте пустым, чтобы не менять">
                                <small class="text-muted">Минимум 8 символов</small>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Подтверждение пароля (только если меняем) -->
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Подтверждение пароля</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control" placeholder="Повторите новый пароль">
                            </div>

                            <!-- Роль -->
                            <div class="col-md-6">
                                <label for="role_id" class="form-label required">Роль</label>
                                <select name="role_id" id="role_id"
                                    class="form-select @error('role_id') is-invalid @enderror" required>
                                    <option value="">Выберите роль</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}"
                                            {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Статус локализации -->
                            <div class="col-md-6">
                                <label for="is_lang" class="form-label required">Язык интерфейса</label>
                                <select name="is_lang" 
                                        id="is_lang"
                                        class="form-select @error('is_lang') is-invalid @enderror" 
                                        required>
                                    <option value="ru" {{ old('is_lang', $user->is_lang ?? 'ru') == 'ru' ? 'selected' : '' }}>Русский</option>
                                    <option value="en" {{ old('is_lang', $user->is_lang ?? 'ru') == 'en' ? 'selected' : '' }}>Английский</option>
                                </select>
                                @error('is_lang')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Телефон -->
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Телефон</label>
                                <input type="tel" name="phone" id="phone"
                                    value="{{ old('phone', $user->phone) }}"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    placeholder="+7 (XXX) XXX-XX-XX">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Должность -->
                            <div class="col-md-6">
                                <label for="position" class="form-label">Должность</label>
                                <input type="text" name="position" id="position"
                                    value="{{ old('position', $user->position) }}"
                                    class="form-control @error('position') is-invalid @enderror"
                                    placeholder="Например, Администратор">
                                @error('position')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Статус активности -->
                            <div class="col-md-6">
                                <div class="form-check mt-4 pt-2">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" id="is_active" value="1"
                                        {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                        class="form-check-input @error('is_active') is-invalid @enderror">
                                    <label for="is_active" class="form-check-label">Активный пользователь</label>
                                    <small class="text-muted d-block mt-1">Неактивные пользователи не могут входить в
                                        систему</small>
                                    @error('is_active')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Описание (биография) -->
                            <div class="col-12">
                                <label for="bio" class="form-label">Биография</label>
                                <textarea name="bio" id="bio" class="form-control @error('bio') is-invalid @enderror" rows="3"
                                    placeholder="Краткая информация о пользователе">{{ old('bio', $user->bio) }}</textarea>
                                @error('bio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Информация о пользователе -->
                            <div class="col-12">
                                <div class="card bg-light border-0">
                                    <div class="card-body py-2">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <small class="text-muted">ID пользователя:</small>
                                                <div class="fw-semibold">#{{ $user->id }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">Дата создания:</small>
                                                <div class="fw-semibold">{{ $user->created_at->format('d.m.Y H:i') }}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">Последнее обновление:</small>
                                                <div class="fw-semibold">{{ $user->updated_at->format('d.m.Y H:i') }}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">Последний вход:</small>
                                                <div class="fw-semibold">
                                                    @if ($user->last_login_at)
                                                        {{ $user->last_login_at->format('d.m.Y H:i') }}
                                                    @else
                                                        <span class="text-muted">Еще не заходил</span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if ($user->is_system)
                                                <div class="col-12 mt-2">
                                                    <span class="badge bg-warning">
                                                        <i class="bi bi-shield-check me-1"></i> Системный пользователь
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Кнопки формы -->
                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i> Сохранить изменения
                            </button>
                            <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary ms-2">
                                Отмена
                            </a>

                            <!-- Кнопка удаления (если можно) -->
                            @if (auth()->user()->hasPermission('users_delete') && !$user->is_system && $user->id !== auth()->id())
                                <button type="button" class="btn btn-outline-danger ms-2" data-bs-toggle="modal"
                                    data-bs-target="#deleteUserModal">
                                    <i class="bi bi-trash me-2"></i> Удалить
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Боковая панель с информацией -->
        <div class="col-md-4">
            <!-- Карточка с информацией о пользователе -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-person-circle me-2"></i> Профиль пользователя</h6>
                </div>
                <div class="card-body text-center">
                    <!-- Аватар -->
                    <div class="mb-3">
                        @if ($user->avatar)
                            <img src="{{ url(Storage::url($user->avatar)) }}" alt="{{ $user->name }}"
                                class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center mx-auto"
                                style="width: 120px; height: 120px;">
                                <i class="bi bi-person" style="font-size: 3rem;"></i>
                            </div>
                        @endif
                    </div>

                    <!-- Основная информация -->
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    @if ($user->position)
                        <p class="text-muted mb-2">{{ $user->position }}</p>
                    @endif

                    <!-- Статус -->
                    <div class="mb-3">
                        @if ($user->is_active)
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i> Активен
                            </span>
                        @else
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle me-1"></i> Неактивен
                            </span>
                        @endif

                        @if ($user->is_system)
                            <span class="badge bg-warning mt-1 d-inline-block">
                                <i class="bi bi-shield-check me-1"></i> Системный
                            </span>
                        @endif
                    </div>

                    <!-- Контактная информация -->
                    <div class="text-start">
                        <div class="mb-2">
                            <i class="bi bi-envelope me-2 text-muted"></i>
                            <a href="mailto:{{ $user->email }}" class="text-decoration-none">{{ $user->email }}</a>
                        </div>
                        @if ($user->phone)
                            <div class="mb-2">
                                <i class="bi bi-telephone me-2 text-muted"></i>
                                {{ $user->phone }}
                            </div>
                        @endif
                        @if ($user->role)
                            <div class="mb-0">
                                <i class="bi bi-person-badge me-2 text-muted"></i>
                                <span class="badge bg-primary">{{ $user->role->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Карточка с предупреждениями -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-exclamation-triangle me-2"></i> Важно</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning alert-sm mb-3">
                        <i class="bi bi-key me-2"></i>
                        <strong>Пароль</strong> меняется только если вы заполните поле
                    </div>
                    <div class="alert alert-info alert-sm mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Изменения вступают в силу сразу после сохранения
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно подтверждения удаления -->
    @if (auth()->user()->hasPermission('users_delete') && !$user->is_system && $user->id !== auth()->id())
        <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteUserModalLabel">Подтверждение удаления</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Вы уверены, что хотите удалить пользователя <strong>{{ $user->name }}</strong>?</p>
                        <div class="alert alert-danger alert-sm mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Это действие нельзя отменить. Все данные пользователя будут удалены.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
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
    @endif

@endsection
