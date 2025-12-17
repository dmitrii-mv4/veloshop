// Переключение бокового меню на мобильных устройствах
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');

menuToggle.addEventListener('click', function () {
    sidebar.classList.toggle('mobile-show');
    sidebarOverlay.style.display = sidebar.classList.contains('mobile-show') ? 'block' : 'none';
});

sidebarOverlay.addEventListener('click', function () {
    sidebar.classList.remove('mobile-show');
    sidebarOverlay.style.display = 'none';
});

// Закрытие меню при клике на ссылку на мобильных устройствах
document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
    link.addEventListener('click', function () {
        if (window.innerWidth < 1200) {
            sidebar.classList.remove('mobile-show');
            sidebarOverlay.style.display = 'none';
        }
    });
});

// Анимация появления элементов при загрузке
document.addEventListener('DOMContentLoaded', function () {
    const fadeElements = document.querySelectorAll('.fade-in');
    fadeElements.forEach(el => {
        el.style.opacity = '0';
    });

    setTimeout(() => {
        fadeElements.forEach(el => {
            el.style.opacity = '1';
        });
    }, 100);

    // Инициализация активного элемента в переключателе языков
    const languageItems = document.querySelectorAll('.language-item');
    languageItems.forEach(item => {
        item.addEventListener('click', function (e) {
            e.preventDefault();
            languageItems.forEach(i => i.classList.remove('active'));
            this.classList.add('active');

            // Обновляем текст в кнопке
            const languageName = this.textContent.trim();
            const languageFlag = this.querySelector('.language-flag').innerHTML;
            const languageBtn = document.querySelector('.language-btn');
            languageBtn.querySelector('.language-flag').innerHTML = languageFlag;
            languageBtn.querySelector('.language-name').textContent = languageName;
        });
    });
});

// Закрытие меню при изменении размера окна на десктоп
window.addEventListener('resize', function () {
    if (window.innerWidth >= 1200) {
        sidebar.classList.remove('mobile-show');
        sidebarOverlay.style.display = 'none';
    }
});