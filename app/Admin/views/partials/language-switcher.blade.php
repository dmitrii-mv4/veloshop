@php
    use App\Admin\Services\LanguageService;
    $languageService = app(LanguageService::class);
    $currentLocale = $languageService->getCurrentLocale();
    $availableLocales = $languageService->getAvailableLanguages();
@endphp

<div class="language-switcher dropdown" id="languageSwitcher">
    <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" 
            type="button" 
            id="languageDropdown" 
            data-bs-toggle="dropdown" 
            aria-expanded="false"
            aria-label="{{ admin_trans('app.language_switcher') ?? 'Выбор языка' }}">
        <span class="language-flag me-1">{{ $availableLocales[$currentLocale]['flag'] ?? '🌐' }}</span>
        <span class="language-code d-none d-md-inline">
            {{ strtoupper($currentLocale) }}
        </span>
    </button>
    
    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="languageDropdown">
        @foreach($availableLocales as $locale)
            @if($locale['enabled'] ?? true)
                <li>
                    <form action="{{ route('admin.language.switch') }}" 
                          method="POST" 
                          class="language-form">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="locale" value="{{ $locale['code'] }}">
                        
                        <button type="submit" 
                                class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ $currentLocale === $locale['code'] ? 'active' : '' }}"
                                @if($currentLocale === $locale['code']) aria-current="true" @endif>
                            <div class="d-flex align-items-center">
                                <span class="language-flag me-2">{{ $locale['flag'] }}</span>
                                <span class="language-name">
                                    {{ $locale['native_name'] }}
                                </span>
                            </div>
                            @if($currentLocale === $locale['code'])
                                <span class="check-mark text-success">
                                    <i class="fas fa-check"></i>
                                </span>
                            @endif
                        </button>
                    </form>
                </li>
            @endif
        @endforeach
    </ul>
</div>

<style>
    /* Основные стили для переключателя */
    .language-switcher .btn {
        border-color: var(--bs-border-color);
        background: var(--bs-body-bg);
        padding: 0.375rem 0.75rem;
        transition: all 0.2s ease;
        min-height: 38px;
        border-radius: 0.375rem;
    }
    
    .language-switcher .btn:hover,
    .language-switcher .btn:focus {
        background-color: var(--bs-light);
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    }
    
    .language-switcher .btn:active {
        transform: translateY(1px);
    }
    
    .language-switcher .dropdown-menu {
        min-width: 180px;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.5rem;
        margin-top: 0.5rem;
    }
    
    .language-form {
        margin: 0;
    }
    
    .language-form button {
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        cursor: pointer;
        transition: background-color 0.15s ease;
        border-radius: 0.25rem;
        margin: 0 0.25rem;
    }
    
    .language-form button:hover {
        background-color: var(--bs-light);
        color: var(--bs-primary);
    }
    
    .language-form button.active {
        background-color: var(--bs-primary-bg-subtle);
        color: var(--bs-primary);
        font-weight: 500;
    }
    
    .language-flag {
        font-size: 1.0em;
        line-height: 1;
        width: 24px;
        text-align: center;
    }
    
    .language-name {
        font-size: 0.9375rem;
        line-height: 1.4;
    }
    
    .check-mark {
        font-size: 0.875rem;
    }
    
    /* Адаптивность */
    @media (max-width: 768px) {
        .language-switcher .btn {
            padding: 0.25rem 0.5rem;
            min-height: 36px;
        }
        
        .language-flag {
            font-size: 1.0em;
        }
        
        .language-code {
            font-size: 0.875rem;
        }
        
        .language-switcher .dropdown-menu {
            min-width: 160px;
        }
    }
    
    @media (max-width: 576px) {
        .language-switcher .btn .language-code {
            display: none !important;
        }
        
        .language-switcher .btn {
            min-width: 44px;
            justify-content: center;
        }
    }
    
    /* Анимация */
    @keyframes languageSwitch {
        0% { opacity: 0.5; transform: scale(0.95); }
        100% { opacity: 1; transform: scale(1); }
    }
    
    .language-form button:active {
        animation: languageSwitch 0.2s ease;
    }
    
    /* Темная тема */
    @media (prefers-color-scheme: dark) {
        .language-switcher .btn {
            border-color: var(--bs-border-color-translucent);
            background: var(--bs-dark-bg-subtle);
            color: var(--bs-body-color);
        }
        
        .language-switcher .btn:hover {
            background-color: var(--bs-dark-border-subtle);
        }
        
        .language-form button.active {
            background-color: var(--bs-primary-bg-subtle);
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const languageForms = document.querySelectorAll('.language-form');
    
    languageForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const button = this.querySelector('button');
            const formData = new FormData(this);
            const originalHtml = button.innerHTML;
            
            // Добавляем индикатор загрузки
            button.innerHTML = `
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="d-flex align-items-center">
                        <span class="language-flag me-2 fs-5">⏳</span>
                        <span class="language-name">${button.querySelector('.language-name').textContent}</span>
                    </div>
                    <span class="spinner-border spinner-border-sm text-primary"></span>
                </div>
            `;
            button.disabled = true;
            button.classList.add('disabled');
            
            // Отправляем AJAX запрос
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Показываем успешное сообщение
                    showToast(data.message || 'Язык изменен', 'success');
                    
                    // Перезагружаем страницу через небольшую задержку
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                } else {
                    // Восстанавливаем кнопку
                    button.innerHTML = originalHtml;
                    button.disabled = false;
                    button.classList.remove('disabled');
                    
                    // Показываем ошибку
                    showToast(data.message || 'Ошибка при смене языка', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Восстанавливаем кнопку
                button.innerHTML = originalHtml;
                button.disabled = false;
                button.classList.remove('disabled');
                
                // Пробуем отправить форму обычным способом (fallback)
                showToast('Пробуем стандартный метод...', 'info');
                setTimeout(() => {
                    this.submit();
                }, 1000);
            });
        });
    });
    
    // Функция для показа уведомлений
    function showToast(message, type = 'info') {
        // Создаем контейнер для тостов, если его нет
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                max-width: 350px;
            `;
            document.body.appendChild(toastContainer);
        }
        
        // Создаем тост
        const toastId = 'toast-' + Date.now();
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = 'toast align-items-center border-0 show';
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        const bgColor = type === 'success' ? 'bg-success' : 
                       type === 'error' ? 'bg-danger' : 
                       type === 'warning' ? 'bg-warning' : 'bg-info';
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body text-white ${bgColor} rounded">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        // Автоматически скрываем через 3 секунды
        setTimeout(() => {
            const toastElement = document.getElementById(toastId);
            if (toastElement) {
                toastElement.remove();
            }
        }, 3000);
    }
    
    // Обработка клавиатуры для доступности
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const dropdown = document.querySelector('#languageDropdown');
            if (dropdown && dropdown.getAttribute('aria-expanded') === 'true') {
                dropdown.click();
            }
        }
    });
});
</script>