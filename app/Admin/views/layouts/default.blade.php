<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'KotiksCMS')</title>

    <meta name="robots" content="index, follow">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Select2 для красивых селектов -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <link rel="stylesheet" id="css-main" href="/layouts/admin/default/css/kotiks.css">
</head>

<body>

    <!-- Оверлей для мобильного меню -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="wrapper">
        <!-- Боковое меню -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <div class="logo-icon">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <span class="logo-text">{{ $settings['name_site'] }}</span>
                </div>
            </div>

            <div class="nav-container">
                <!-- Раздел 1: Дашборд и переход на сайт -->
                <div class="nav-section">
                    <div class="nav-section-title">{{ trans('app.navigation') }}</div>
                    <ul class="nav flex-column sidebar-nav">
                        <li class="nav-item">
                            <a class="nav-link active" href="/">
                                <i class="bi bi-speedometer2 nav-icon"></i>
                                <span>{{ trans('app.dashboard') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ $settings['url_site'] }}" target="_blank">
                                <i class="bi bi-globe nav-icon"></i>
                                <span>{{ trans('app.go_to_the_site') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Раздел 2: Системные модули -->
                <div class="nav-section">
                    <div class="nav-section-title">Системные модули</div>
                    <ul class="nav flex-column sidebar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.page.index') }}">
                                <i class="bi bi-layout-text-window nav-icon"></i>
                                <span>{{ trans('app.page.site_pages') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.media') }}">
                                <i class="bi-images nav-icon"></i>
                                <span>{{ trans('app.media_library.name') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="collapse" href="#userCollapse" role="button">
                                <i class="bi bi-people nav-icon"></i>
                                <span>{{ trans('app.user.users') }}</span>
                            </a>
                            <div class="collapse" id="userCollapse">
                                <ul class="nav flex-column submenu">
                                    <li class="nav-item">
                                        <a class="nav-link"
                                            href="{{ route('admin.users') }}">
                                            <i class="bi bi-people nav-icon"></i> 
                                            {{ trans('app.user.all_users') }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link"
                                            href="{{ route('admin.roles') }}">
                                            <i class="bi-shield-check nav-icon"></i> 
                                            {{ trans('app.role.roles') }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Раздел 2: Пользовательские модули -->
                <div class="nav-section">
                    <div class="nav-section-title">Пользовательские модули</div>

                    <ul class="nav flex-column sidebar-nav">
                        @forelse($modules as $module)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.' . $module['code_module'] . '.index') }}">
                                    <i class="bi-box nav-icon"></i>
                                    <span>{{ $module['code_module'] }}</span>
                                </a>
                            </li>
                        @empty
                            <li class="nav-item">
                                <div class="nav-link">
                                    <i class="bi bi-info-circle nav-icon"></i>
                                    <span>Модули ещё не созданы</span>
                                </div>
                            </li>
                        @endforelse

                        <!-- Ссылка на создание нового модуля -->
                        <li class="nav-item mt-2">
                            <a class="nav-link text-success" href="{{ route('admin.module_generator.create') }}">
                                <i class="bi bi-plus-circle nav-icon"></i>
                                <span>Создать новый модуль</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Раздел 4: Настройки -->
                <div class="nav-section">
                    <div class="nav-section-title">Настройки</div>

                    <ul class="nav flex-column sidebar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.module_generator.index') }}">
                                <i class="bi-magic nav-icon"></i>
                                <span>{{ trans('app.modules.module_generator') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.integration.index') }}">
                                <i class="bi-plug nav-icon"></i>
                                <span>Интеграция</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.settings') }}">
                                <i class="bi bi-gear nav-icon"></i>
                                <span>{{ trans('app.settings') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Основной контент -->
        <div class="main-content">
            <!-- Верхняя панель -->
            <header class="topbar">
                <!-- Кнопка меню будет скрыта на десктопе -->
                <button class="menu-toggle" id="menuToggle">
                    <i class="bi bi-list"></i>
                </button>

                <div class="search-box position-relative">
                    <i class="bi bi-search position-absolute"></i>
                    <input type="text" class="form-control" placeholder="Поиск...">
                </div>

                <div class="topbar-actions">
                    <!-- Переключатель языков -->
                    <div class="language-switcher">
                        <button class="language-btn" type="button" data-bs-toggle="dropdown">
                            <div class="language-flag">
                                <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMTUiIHZpZXdCb3g9IjAgMCAyMCAxNSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjIwIiBoZWlnaHQ9IjE1IiBmaWxsPSIjZmZmIi8+CjxyZWN0IHdpZHRoPSIyMCIgaGVpZ2h0PSI1IiBmaWxsPSIjMDAzOEFBIi8+CjxyZWN0IHk9IjEwIiB3aWR0aD0iMjAiIGhlaWdodD0iNSIgZmlsbD0iIzAwMzhBQSIvPgo8cmVjdCB5PSI1IiB3aWR0aD0iMjAiIGhlaWdodD0iNSIgZmlsbD0iI0Q4MkMyQiIvPgo8L3N2Zz4K"
                                            alt="RU Flag">
                            </div>
                            <span class="language-name">Русский</span>
                            <i class="bi bi-chevron-down ms-1"></i>
                        </button>
                        <ul class="dropdown-menu language-dropdown">
                            <li>
                                <a class="dropdown-item language-item active" href="#">
                                    <div class="language-flag">
                                        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMTUiIHZpZXdCb3g9IjAgMCAyMCAxNSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjIwIiBoZWlnaHQ9IjE1IiBmaWxsPSIjZmZmIi8+CjxyZWN0IHdpZHRoPSIyMCIgaGVpZ2h0PSI1IiBmaWxsPSIjMDAzOEFBIi8+CjxyZWN0IHk9IjEwIiB3aWR0aD0iMjAiIGhlaWdodD0iNSIgZmlsbD0iIzAwMzhBQSIvPgo8cmVjdCB5PSI1IiB3aWR0aD0iMjAiIGhlaWdodD0iNSIgZmlsbD0iI0Q4MkMyQiIvPgo8L3N2Zz4K"
                                            alt="RU Flag">
                                    </div>
                                    Русский
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item language-item" href="#">
                                    <div class="language-flag">
                                        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMTUiIHZpZXdCb3g9IjAgMCAyMCAxNSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjIwIiBoZWlnaHQ9IjE1IiBmaWxsPSIjZmZmIi8+CjxyZWN0IHdpZHRoPSIyMCIgaGVpZ2h0PSI1IiBmaWxsPSIjMDAzOEFBIi8+CjxyZWN0IHk9IjEwIiB3aWR0aD0iMjAiIGhlaWdodD0iNSIgZmlsbD0iIzAwMzhBQSIvPgo8L3N2Zz4K"
                                            alt="US Flag">
                                    </div>
                                    English
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Уведомления -->
                    <button class="notification-badge">
                        <i class="bi bi-bell"></i>
                        <span class="notification-count">3</span>
                    </button>

                    <!-- Блок пользователя -->
                    <div class="dropdown">
                        <button class="user-info-dropdown" type="button" data-bs-toggle="dropdown">
                            <div class="user-avatar-small">AD</div>
                            <div class="user-details-small">
                                <div class="user-name-small">Alexandra</div>
                                <div class="user-role-small">PRO</div>
                            </div>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end user-dropdown-menu">
                            <li class="dropdown-header-user">
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar-small me-2">AD</div>
                                    <div>
                                        <div class="fw-medium">{{ auth()->user()->name }}</div>
                                        <small class="text-muted">{{ auth()->user()->email }}</small>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item dropdown-item-user" href="{{ route('admin.users.edit', auth()->user()->id) }}"><i
                                        class="bi bi-person me-2"></i>{{ trans('app.user.profile') }}</a></li>
                            <li><a class="dropdown-item dropdown-item-user" href="{{ route('admin.users.edit', auth()->user()->id) }}"><i
                                        class="bi bi-gear me-2"></i>{{ trans('app.settings') }}</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <li><a class="dropdown-item dropdown-item-user text-danger" href="#"><i
                                        class="bi bi-box-arrow-right me-2"></i>{{ trans('app.user.exit') }}</a></li>
                            </form>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Область контента -->
            <main class="content-area">
                @yield('content')
            </main>

            <!-- Футер -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            © {{ date('Y') }} kotiksCMS. Все права защищены.
                        </div>
                        <div class="col-md-6 text-md-end">
                            <span class="text-muted">Версия 1.0.0</span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="/layouts/admin/lib/js/jquery.min.js"></script>

    <script src="/layouts/admin/default/js/kotiks.js"></script>
    <script src="/layouts/admin/default/js/modules_generator.js"></script>
    <script src="/layouts/admin/default/js/modules_generator_create.js"></script>
    <script src="/layouts/admin/default/js/integrator.js"></script>
    <script src="/layouts/admin/default/js/users.js"></script>
</body>

</html>
