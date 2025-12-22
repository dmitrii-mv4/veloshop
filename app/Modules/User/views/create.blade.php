@extends('admin::layouts.default')

@section('title', 'Добавить пользователя | KotiksCMS')

@section('content')

    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        
        <!-- Подключаем breadcrumb -->
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['title' => 'Пользователи', 'url' => route('admin.users')],
                ['title' => 'Добавить пользователя']
            ]
        ])
    </div>

    <!-- Действия -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Добавить нового пользователя</h1>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">Заполните форму для создания новой учетной записи</p>
        </div>
        <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Вернуться к списку
        </a>
    </div>

    <!-- Форма создания -->
    <div class="row fade-in">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Основная информация</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.store') }}" id="createUserForm">
                        @csrf
                        
                        <div class="row g-3">
                            <!-- Имя пользователя -->
                            <div class="col-md-6">
                                <label for="name" class="form-label required">Имя пользователя</label>
                                <input type="text" 
                                       name="name" 
                                       id="name" 
                                       value="{{ old('name') }}"
                                       class="form-control @error('name') is-invalid @enderror" 
                                       placeholder="Введите имя пользователя"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Email -->
                            <div class="col-md-6">
                                <label for="email" class="form-label required">Email</label>
                                <input type="email" 
                                       name="email" 
                                       id="email" 
                                       value="{{ old('email') }}"
                                       class="form-control @error('email') is-invalid @enderror" 
                                       placeholder="user@example.com"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Пароль -->
                            <div class="col-md-6">
                                <label for="password" class="form-label required">Пароль</label>
                                <input type="password" 
                                       name="password" 
                                       id="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       placeholder="Минимум 6 символов"
                                       required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Подтверждение пароля -->
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label required">Подтверждение пароля</label>
                                <input type="password" 
                                       name="password_confirmation" 
                                       id="password_confirmation" 
                                       class="form-control" 
                                       placeholder="Повторите пароль"
                                       required>
                            </div>
                            
                            <!-- Роль -->
                            <div class="col-md-6">
                                <label for="role_id" class="form-label required">Роль</label>
                                <select name="role_id" 
                                        id="role_id" 
                                        class="form-select role-select @error('role_id') is-invalid @enderror" 
                                        required>
                                    <option value="">Выберите роль</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
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
                                    <option value="ru" {{ old('is_lang', 'ru') == 'ru' ? 'selected' : '' }}>Русский</option>
                                    <option value="en" {{ old('is_lang', 'ru') == 'en' ? 'selected' : '' }}>Английский</option>
                                </select>
                                @error('is_lang')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Телефон -->
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Телефон</label>
                                <input type="tel" 
                                       name="phone" 
                                       id="phone" 
                                       value="{{ old('phone') }}"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       placeholder="+7 (XXX) XXX-XX-XX">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Должность -->
                            <div class="col-md-6">
                                <label for="position" class="form-label">Должность</label>
                                <input type="text" 
                                       name="position" 
                                       id="position" 
                                       value="{{ old('position') }}"
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
                                    <input type="checkbox" 
                                           name="is_active" 
                                           id="is_active" 
                                           value="1"
                                           {{ old('is_active', true) ? 'checked' : '' }}
                                           class="form-check-input @error('is_active') is-invalid @enderror">
                                    <label for="is_active" class="form-check-label">Активный пользователь</label>
                                    <small class="text-muted d-block mt-1">Неактивные пользователи не могут входить в систему</small>
                                    @error('is_active')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Описание (биография) -->
                            <div class="col-12">
                                <label for="bio" class="form-label">Биография</label>
                                <textarea name="bio" 
                                          id="bio" 
                                          class="form-control @error('bio') is-invalid @enderror" 
                                          rows="3"
                                          placeholder="Краткая информация о пользователе">{{ old('bio') }}</textarea>
                                @error('bio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Кнопки формы -->
                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i> Создать пользователя
                            </button>
                            <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary ms-2">
                                Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Боковая панель с информацией -->
        <div class="col-md-4">
            <!-- Карточка с подсказками -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> Информация</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info alert-sm mb-3">
                        <i class="bi bi-key me-2"></i>
                        <strong>Пароль</strong> должен содержать минимум 6 символов
                    </div>
                    <div class="alert alert-warning alert-sm mb-3">
                        <i class="bi bi-shield-exclamation me-2"></i>
                        <strong>Роль пользователя</strong> определяет его права доступа в системе
                    </div>
                    <div class="alert alert-light alert-sm mb-0">
                        <i class="bi bi-toggle-on me-2"></i>
                        <strong>Статус "Активен"</strong> позволяет пользователю входить в систему
                    </div>
                </div>
            </div>
            
            <!-- Карточка с системными требованиями -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="bi bi-exclamation-triangle me-2"></i> Важно</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0" style="font-size: 0.85rem;">
                        <li>Пароль должен быть надежным</li>
                        <li>Email должен быть уникальным</li>
                        <li>Роль влияет на доступ к функциям системы</li>
                        <li>После создания можно изменить все данные</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

@endsection
