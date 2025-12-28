<!DOCTYPE html>
<html lang="ru" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '| Современная Headless-система Kotiks CMS')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts (Inter + Orbitron для космического стиля) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Orbitron:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Kotiks CMS стили -->
    <link rel="stylesheet" href="/layouts/admin/default/css/kotiks.css">
    <link rel="stylesheet" href="/layouts/admin/default/css/auth.css">
</head>

<body>

    <!-- Контейнер настроек (правый верхний угол) -->
    <div class="settings-container">
        <!-- Переключатель темы -->
        <div class="theme-toggle" id="themeToggle">
            <i class="fas fa-sun"></i>
            <span id="themeText">Светлая</span>
        </div>
        
        <!-- Переключатель языка -->
        <div class="language-toggle" id="languageToggle">
            <i class="fas fa-globe"></i>
            <span id="languageText">Русский</span>
            <i class="fas fa-chevron-down" style="font-size: 0.8rem;"></i>
        </div>
        
        <!-- Выпадающий список языков -->
        <div class="language-dropdown" id="languageDropdown">
            <div class="language-option active" data-lang="ru">
                <!-- Флаг России в base64 -->
                <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA5IDYiPjxyZWN0IHdpZHRoPSI5IiBoZWlnaHQ9IjIiIGZpbGw9IiNmZmYiLz48cmVjdCB5PSIyIiB3aWR0aD0iOSIgaGVpZ2h0PSIyIiBmaWxsPSIjMDAzOGE3Ii8+PHJlY3QgeT0iNCIgd2lkdGg9IjkiIGhlaWdodD0iMiIgZmlsbD0iI2Q1MDE1MSIvPjwvc3ZnPg==" class="flag-icon" alt="Русский">
                <span>Русский</span>
            </div>
            <div class="language-option" data-lang="en">
                <!-- Флаг Великобритании в base64 -->
                <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA2MCAzMCI+PHJlY3Qgd2lkdGg9IjYwIiBoZWlnaHQ9IjMwIiBmaWxsPSIjMDEyMTY5Ii8+PHBhdGggZD0iTTAgMEw2MCAzMEgweiIgZmlsbD0iI2ZmZiIvPjxwYXRoIGQ9Ik02MCAwTDAgMzBWNHoiIGZpbGw9IiNmZmYiLz48cGF0aCBkPSJNMjQgMEw2MCAzMEgzNnoiIGZpbGw9IiNDODEwMkUiLz48cGF0aCBkPSJNMzYgMEw2MCAzMEg0OHoiIGZpbGw9IiNmZmYiLz48cGF0aCBkPSJNMCAwTDM2IDMwVjE4eiIgZmlsbD0iI0M4MTAyRSIvPjxwYXRoIGQ9Ik0wIDEyTDI0IDMwSDEyeiIgZmlsbD0iI2ZmZiIvPjxwYXRoIGQ9Ik0wIDBMNjAgMzBWNTh6IiBmaWxsPSIjQzgxMDJFIi8+PHBhdGggZD0iTTAgMEMyNCAzMCAwIDI0eiIgZmlsbD0iI2ZmZiIvPjwvc3ZnPg==" class="flag-icon" alt="English">
                <span>English</span>
            </div>
        </div>
    </div>

    @yield('content')

    <!-- Космический фон -->
    <div class="space-background" id="spaceBackground">
        <!-- Звёзды будут сгенерированы через JS -->
    </div>

    <!-- jQuery -->
    <script src="/layouts/admin/lib/js/jquery.min.js"></script>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="/layouts/admin/default/js/auth.js"></script>
</body>
</html>