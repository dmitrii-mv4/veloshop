$(document).ready(function () {
    // Устанавливаем CSRF токен для всех AJAX запросов
        $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Тексты для перевода
    const translations = {
        ru: {
            theme: "Светлая",
            language: "Русский",
            badge: "HEADLESS CMS PLATFORM",
            subtitle: "Модульная headless-система нового поколения. Создавайте, управляйте и доставляйте контент в любую точку цифровой вселенной.",
            feature1Title: "Быстрый запуск",
            feature1Text: "Мгновенное развертывание, готовность к работе за минуты",
            feature2Title: "Масштабируемость",
            feature2Text: "Растёт вместе с вашим проектом без ограничений",
            feature3Title: "Безопасность",
            feature3Text: "Enterprise-уровень защиты данных и API",
            status: "Система активна",
            formTitle: "Вход в систему",
            formSubtitle: "Используйте ваши учетные данные для доступа к панели управления",
            emailLabel: "Email адрес",
            passwordLabel: "Пароль",
            forgotPassword: "Забыли пароль?",
            rememberLabel: "Сохранить сессию на этом устройстве",
            submitText: "Авторизоваться",
            errorTitle: "Ошибка авторизации",
            errorText: "Проверьте правильность введённых данных",
            helpText: "Нужна помощь?",
            supportLink: "Свяжитесь с поддержкой",
            footerText: `© ${new Date().getFullYear()} Kotiks CMS • Все права защищены`
        },
        en: {
            theme: "Light",
            language: "English",
            badge: "HEADLESS CMS PLATFORM",
            subtitle: "Next-generation modular headless system. Create, manage and deliver content to any point in the digital universe.",
            feature1Title: "Quick Start",
            feature1Text: "Instant deployment, ready to work in minutes",
            feature2Title: "Scalability",
            feature2Text: "Grows with your project without limits",
            feature3Title: "Security",
            feature3Text: "Enterprise-level data and API protection",
            status: "System active",
            formTitle: "Sign In",
            formSubtitle: "Use your credentials to access the control panel",
            emailLabel: "Email address",
            passwordLabel: "Password",
            forgotPassword: "Forgot password?",
            rememberLabel: "Keep me signed in",
            submitText: "Sign In",
            errorTitle: "Authentication Error",
            errorText: "Please check your credentials",
            helpText: "Need help?",
            supportLink: "Contact support",
            footerText: `© ${new Date().getFullYear()} Kotiks CMS • All rights reserved`
        }
    };

    // Генерация звёздного фона
    function createStars() {
        const spaceBg = $('#spaceBackground');
        const starCount = 150;

        for (let i = 0; i < starCount; i++) {
            const star = $('<div class="star"></div>');
            const size = Math.random() * 3;
            const x = Math.random() * 100;
            const y = Math.random() * 100;
            const duration = 2 + Math.random() * 3;

            star.css({
                left: x + '%',
                top: y + '%',
                width: size + 'px',
                height: size + 'px',
                '--duration': duration + 's',
                animationDelay: Math.random() * 5 + 's'
            });

            if (size < 1) star.addClass('small');
            else if (size < 2) star.addClass('medium');
            else star.addClass('large');

            spaceBg.append(star);
        }
    }

    // Проверка сохраненной темы - ПО УМОЛЧАНИЮ ТЁМНАЯ
    function checkSavedTheme() {
        const savedTheme = localStorage.getItem('kotiks-theme');

        // Если есть сохраненная тема - используем её
        if (savedTheme === 'light') {
            enableLightTheme();
        } else {
            // По умолчанию - тёмная тема
            enableDarkTheme();
        }
    }

    // Включение светлой темы
    function enableLightTheme() {
        $('html').attr('data-bs-theme', 'light');
        $('#themeToggle i').removeClass('fa-moon').addClass('fa-sun');
        const lang = getCurrentLanguage();
        // В светлой теме кнопка переключается на "Темная"
        $('#themeText').text(lang === 'ru' ? 'Тёмная' : 'Dark');
        localStorage.setItem('kotiks-theme', 'light');

        // Адаптация туманностей для светлой темы
        $('.nebula').css('opacity', 0.08);
    }

    // Включение темной темы
    function enableDarkTheme() {
        $('html').attr('data-bs-theme', 'dark');
        $('#themeToggle i').removeClass('fa-sun').addClass('fa-moon');
        const lang = getCurrentLanguage();
        // В темной теме кнопка переключается на "Светлая"
        $('#themeText').text(lang === 'ru' ? 'Светлая' : 'Light');
        localStorage.setItem('kotiks-theme', 'dark');

        // Адаптация туманностей для темной темы
        $('.nebula').css('opacity', 0.15);
    }

    // Переключение темы
    $('#themeToggle').click(function () {
        const currentTheme = $('html').attr('data-bs-theme');
        if (currentTheme === 'dark') {
            enableLightTheme();
        } else {
            enableDarkTheme();
        }
    });

    // Получение текущего языка
    function getCurrentLanguage() {
        const cookieLang = document.cookie.replace(/(?:(?:^|.*;\s*)kotiks-locale\s*=\s*([^;]*).*$)|^.*$/, "$1");
        if (cookieLang && translations[cookieLang]) {
            return cookieLang;
        }

        const browserLang = navigator.language.split('-')[0];
        return translations[browserLang] ? browserLang : 'ru';
    }

    // Установка языка
    function setLanguage(lang) {
        if (!translations[lang]) return;

        // Сохраняем в cookie на 365 дней
        const expires = new Date();
        expires.setTime(expires.getTime() + 365 * 24 * 60 * 60 * 1000);
        document.cookie = `kotiks-locale=${lang}; expires=${expires.toUTCString()}; path=/`;

        // Обновляем тексты
        const t = translations[lang];

        // Левая панель
        $('#badgeText').text(t.badge);
        $('#cosmicSubtitle').text(t.subtitle);
        $('#feature1Title').text(t.feature1Title);
        $('#feature1Text').text(t.feature1Text);
        $('#feature2Title').text(t.feature2Title);
        $('#feature2Text').text(t.feature2Text);
        $('#feature3Title').text(t.feature3Title);
        $('#feature3Text').text(t.feature3Text);
        $('#statusText').text(t.status);

        // Правая панель
        $('#formTitle').text(t.formTitle);
        $('#formSubtitle').text(t.formSubtitle);
        $('#emailLabel').text(t.emailLabel);
        $('#passwordLabel').text(t.passwordLabel);
        $('#forgotPassword span').text(t.forgotPassword);
        $('#rememberLabel').text(t.rememberLabel);
        $('#submitText').text(t.submitText);
        $('#errorTitle').text(t.errorTitle);
        $('#errorText').text(t.errorText);
        $('#helpText').text(t.helpText);
        $('#supportLink').text(t.supportLink);
        $('#footerText').html(t.footerText);

        // Переключатели
        $('#languageText').text(t.language);
        // Обновляем текст темы на текущем языке
        const currentTheme = $('html').attr('data-bs-theme');
        if (currentTheme === 'dark') {
            $('#themeText').text(lang === 'ru' ? 'Светлая' : 'Light');
        } else {
            $('#themeText').text(lang === 'ru' ? 'Тёмная' : 'Dark');
        }

        // Обновляем активный язык в dropdown
        $('.language-option').removeClass('active');
        $(`.language-option[data-lang="${lang}"]`).addClass('active');

        // Обновляем placeholder для email
        $('#email').attr('placeholder', lang === 'ru' ? 'admin@kotiks-cms.ru' : 'admin@kotiks-cms.com');
        $('#password').attr('placeholder', lang === 'ru' ? 'Введите ваш пароль' : 'Enter your password');
    }

    // Управление dropdown языка
    $('#languageToggle').click(function (e) {
        e.stopPropagation();
        $('#languageDropdown').toggleClass('active');
    });

    // Выбор языка
    $('.language-option').click(function () {
        const lang = $(this).data('lang');
        setLanguage(lang);
        $('#languageDropdown').removeClass('active');
    });

    // Закрытие dropdown при клике вне его
    $(document).click(function () {
        $('#languageDropdown').removeClass('active');
    });

    // Переключение видимости пароля
    $('#togglePassword').click(function () {
        const passwordInput = $('#password');
        const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
        passwordInput.attr('type', type);

        const icon = $(this).find('i');
        icon.toggleClass('fa-eye fa-eye-slash');

        $(this).css('transform', 'scale(0.9)');
        setTimeout(() => {
            $(this).css('transform', '');
        }, 150);
    });

    // Анимация при фокусе на полях ввода
    $('.input-group-cosmic input').focus(function () {
        $(this).closest('.input-group-cosmic').css({
            'transform': 'translateY(-2px)',
            'box-shadow': 'var(--glow-primary)'
        });
    }).blur(function () {
        $(this).closest('.input-group-cosmic').css({
            'transform': '',
            'box-shadow': ''
        });
    });

    // Обработка отправки формы
    $('#loginForm').submit(function (e) {
        const email = $('#email').val().trim();
        const password = $('#password').val().trim();
        const submitBtn = $('#submitBtn');

        if (!email || !password) {
            e.preventDefault();

            if (!email) {
                showValidationError('email', getCurrentLanguage() === 'ru'
                    ? 'Поле Email обязательно для заполнения'
                    : 'Email field is required');
            }
            if (!password) {
                showValidationError('password', getCurrentLanguage() === 'ru'
                    ? 'Поле Пароль обязательно для заполнения'
                    : 'Password field is required');
            }

            submitBtn.css('animation', 'shake 0.5s ease-in-out');
            setTimeout(() => {
                submitBtn.css('animation', '');
            }, 500);

            return false;
        }

        const originalHtml = submitBtn.html();
        const loadingText = getCurrentLanguage() === 'ru'
            ? 'Выполняется вход...'
            : 'Signing in...';
        submitBtn.html(`<i class="fas fa-spinner fa-spin me-2"></i> ${loadingText}`);
        submitBtn.prop('disabled', true);

        setTimeout(() => {
            submitBtn.html(originalHtml);
            submitBtn.prop('disabled', false);
        }, 10000);
    });

    // Функция показа ошибок валидации
    function showValidationError(fieldId, message) {
        const field = $('#' + fieldId);
        const inputGroup = field.closest('.input-group-cosmic');

        inputGroup.addClass('border-danger');

        const errorDiv = $('<div class="text-danger small mt-2"></div>')
            .html('<i class="fas fa-exclamation-circle me-1"></i> ' + message);

        inputGroup.after(errorDiv);

        field.on('input', function () {
            inputGroup.removeClass('border-danger');
            errorDiv.remove();
        });
    }

    // Автофокус на поле email
    setTimeout(() => {
        $('#email').focus();
    }, 500);

    // Эффект при наведении на feature items
    $('.feature-item').hover(
        function () {
            $(this).css('box-shadow', '0 10px 25px rgba(0, 0, 0, 0.2)');
        },
        function () {
            $(this).css('box-shadow', '');
        }
    );

    // Инициализация
    createStars();
    checkSavedTheme(); // Запускаем проверку темы
    setLanguage(getCurrentLanguage());

    // Добавляем CSS анимацию тряски
    const style = $('<style>')
        .text('@keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }');
    $('head').append(style);

    // Адаптация для мобильных устройств
    function handleMobileLayout() {
        if ($(window).width() <= 768) {
            $('.settings-container').addClass('mobile-layout');
        } else {
            $('.settings-container').removeClass('mobile-layout');
        }
    }

    handleMobileLayout();
    $(window).resize(handleMobileLayout);
});