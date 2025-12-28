@extends('admin::layouts.auth')

@section('title', 'Вход в Kotiks CMS')

@section('content')
    
    <!-- Основной контейнер -->
    <div class="main-container">
        <div class="auth-card">
            <!-- Левая панель - космическая тематика -->
            <div class="cosmic-panel">
                <div class="cms-badge">
                    <i class="fas fa-satellite mb-1"></i>
                    <span id="badgeText" class="mb-1">HEADLESS CMS PLATFORM</span>
                </div>
                
                <h1 class="cosmic-title">KOTIKS CMS</h1>
                <p class="cosmic-subtitle" id="cosmicSubtitle">
                    Модульная headless-система нового поколения. 
                    Создавайте, управляйте и доставляйте контент в любую точку цифровой вселенной.
                </p>
                
                <div class="features-grid">
                    <div class="feature-item floating" style="animation-delay: 0s;">
                        <div class="feature-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <div>
                            <h5 class="mb-1" id="feature1Title">Быстрый запуск</h5>
                            <p class="mb-0 small text-comet" id="feature1Text">Мгновенное развертывание, готовность к работе за минуты</p>
                        </div>
                    </div>
                    
                    <div class="feature-item floating" style="animation-delay: 1s;">
                        <div class="feature-icon">
                            <i class="fas fa-infinity"></i>
                        </div>
                        <div>
                            <h5 class="mb-1" id="feature2Title">Масштабируемость</h5>
                            <p class="mb-0 small text-comet" id="feature2Text">Растёт вместе с вашим проектом без ограничений</p>
                        </div>
                    </div>
                    
                    <div class="feature-item floating" style="animation-delay: 2s;">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div>
                            <h5 class="mb-1" id="feature3Title">Безопасность</h5>
                            <p class="mb-0 small text-comet" id="feature3Text">Enterprise-уровень защиты данных и API</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Правая панель - форма авторизации -->
            <div class="form-panel">
                <!-- Заголовок формы -->
                <div class="form-header">
                    <h1 class="form-title" id="formTitle">Вход в систему</h1>
                    <p class="form-subtitle" id="formSubtitle">
                        Используйте ваши учетные данные для доступа к панели управления
                    </p>
                </div>
                
                <!-- Форма авторизации -->
                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    <!-- Явный CSRF токен для двойной проверки -->
                    <input type="hidden" name="_token" id="csrf_token" value="{{ csrf_token() }}">
                    
                    <!-- Поле Email -->
                    <div class="form-group">
                        <label for="email" class="form-label" id="emailLabel">Email адрес</label>
                        <div class="input-group-cosmic">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">
                                    <i class="fas fa-user-astronaut"></i>
                                </span>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       placeholder="admin@kotiks-cms.ru" 
                                       value="{{ old('email') }}" 
                                       required 
                                       autocomplete="email" 
                                       autofocus>
                            </div>
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block mt-2">
                                <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    
                    <!-- Поле Пароль -->
                    <div class="form-group">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="password" class="form-label" id="passwordLabel">Пароль</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="link-cosmic small" id="forgotPassword">
                                    <i class="fas fa-key me-1"></i><span>Забыли пароль?</span>
                                </a>
                            @endif
                        </div>
                        <div class="input-group-cosmic">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Введите ваш пароль" 
                                       required 
                                       autocomplete="current-password">
                                <button class="btn btn-link text-decoration-none" 
                                        type="button" 
                                        id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block mt-2">
                                <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    
                    <!-- Чекбокс "Запомнить меня" -->
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="remember" 
                                   id="remember" 
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember" id="rememberLabel">
                                Сохранить сессию на этом устройстве
                            </label>
                        </div>
                    </div>
                    
                    <!-- Кнопка входа -->
                    <div class="form-group">
                        <button type="submit" class="btn btn-cosmic" id="submitBtn">
                            <i class="fas fa-sign-in-alt me-2"></i> <span id="submitText">Авторизоваться</span>
                        </button>
                    </div>
                    
                    <!-- Сообщения об ошибках -->
                    @if($errors->any() && !$errors->has('email') && !$errors->has('password'))
                        <div class="alert alert-danger border-0" role="alert" style="background: rgba(220, 38, 38, 0.1); border-left: 4px solid #DC2626;">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-3"></i>
                                <div>
                                    <strong class="d-block" id="errorTitle">Ошибка авторизации</strong>
                                    <span class="small" id="errorText">Проверьте правильность введённых данных</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </form>
                
                <!-- Дополнительная информация -->
                {{-- <div class="mt-5 pt-4 border-top border-secondary border-opacity-25">
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <p class="small text-comet mb-0">
                                <i class="fas fa-question-circle me-1"></i>
                                <span id="helpText">Нужна помощь?</span>
                                <a href="#" class="link-cosmic" id="supportLink">Свяжитесь с поддержкой</a>
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="small text-comet mb-0" id="footerText">
                                © {{ date('Y') }} Kotiks CMS 
                                <span class="mx-1">•</span>
                                Все права защищены
                            </p>
                        </div>
                    </div>
                </div> --}}
            </div>
        </div>
    </div>

@endsection
