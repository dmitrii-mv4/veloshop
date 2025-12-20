document.addEventListener('DOMContentLoaded', function() {
    // Валидация формы
    const form = document.getElementById('settings-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const nameSite = document.getElementById('name_site');
            const urlSite = document.getElementById('url_site');
            const langAdmin = document.getElementById('lang_admin');
            
            let isValid = true;
            
            // Проверка названия сайта
            if (!nameSite.value.trim()) {
                isValid = false;
                nameSite.classList.add('is-invalid');
                showToast('Пожалуйста, введите название сайта', 'error');
            }
            
            // Проверка URL сайта
            if (!urlSite.value.trim()) {
                isValid = false;
                urlSite.classList.add('is-invalid');
                showToast('Пожалуйста, введите URL сайта', 'error');
            } else if (!isValidUrl(urlSite.value)) {
                isValid = false;
                urlSite.classList.add('is-invalid');
                showToast('Пожалуйста, введите корректный URL', 'error');
            }
            
            // Проверка языка
            if (!langAdmin.value) {
                isValid = false;
                langAdmin.classList.add('is-invalid');
                showToast('Пожалуйста, выберите язык админ-панели', 'error');
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        // Сброс ошибок при вводе
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });
            
            input.addEventListener('change', function() {
                this.classList.remove('is-invalid');
            });
        });
        
        // Обработка кнопки сброса
        const resetBtn = form.querySelector('button[type="reset"]');
        if (resetBtn) {
            resetBtn.addEventListener('click', function(e) {
                if (!confirm('Вы уверены, что хотите сбросить все изменения?')) {
                    e.preventDefault();
                }
            });
        }
    }
    
    // Функция проверки URL
    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }
    
    // Функция для показа уведомлений
    function showToast(message, type = 'info') {
        // Можно использовать Bootstrap Toast или простой alert
        alert(message);
    }
    
    // Добавляем превью URL
    const urlInput = document.getElementById('url_site');
    const urlPreview = document.createElement('div');
    urlPreview.className = 'alert alert-info mt-2 py-2 small';
    urlPreview.style.display = 'none';
    urlPreview.innerHTML = '<i class="bi bi-link-45deg me-1"></i> <strong>Полный URL:</strong> <span id="url-preview"></span>';
    
    if (urlInput) {
        urlInput.parentNode.appendChild(urlPreview);
        
        urlInput.addEventListener('input', function() {
            const value = this.value.trim();
            const baseUrl = window.location.origin;
            
            if (value) {
                let fullUrl;
                if (value.startsWith('http://') || value.startsWith('https://')) {
                    fullUrl = value;
                } else {
                    fullUrl = baseUrl + '/' + value.replace(/^\//, '');
                }
                
                document.getElementById('url-preview').textContent = fullUrl;
                urlPreview.style.display = 'block';
            } else {
                urlPreview.style.display = 'none';
            }
        });
        
        // Инициализация при загрузке
        if (urlInput.value) {
            urlInput.dispatchEvent(new Event('input'));
        }
    }
});