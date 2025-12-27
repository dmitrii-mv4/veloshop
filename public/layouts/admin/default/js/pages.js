/**
 * Общий JavaScript для модуля страниц
 * Включает: обработку форм, модальных окон, счетчиков символов
 */
class PageModule {
    constructor() {
        this.init();
    }

    init() {
        this.initFormHandlers();
        this.initModalHandlers();
        this.initCounterHandlers();
    }

    /**
     * Инициализация обработчиков форм
     */
    initFormHandlers() {
        // Инициализация счетчиков символов
        this.initCharCounters();
        
        // Генерация slug
        this.initSlugGeneration();
        
        // Управление полем даты публикации
        this.initPublishedAtField();
        
        // Обработка отправки формы
        this.initFormSubmit();
    }

    /**
     * Инициализация счетчиков символов
     */
    initCharCounters() {
        document.querySelectorAll('[data-char-counter]').forEach(element => {
            const counter = document.getElementById(element.getAttribute('data-char-counter'));
            if (counter) {
                this.updateCharCounter(element, counter);
                element.addEventListener('input', () => this.updateCharCounter(element, counter));
            }
        });
    }

    /**
     * Обновление счетчика символов
     */
    updateCharCounter(input, counter) {
        const length = input.value.length;
        const maxLength = input.getAttribute('maxlength');
        counter.textContent = length;
        
        const percentage = (length / maxLength) * 100;
        counter.parentElement.className = 'char-counter mt-1';
        
        if (percentage >= 90) {
            counter.parentElement.classList.add('danger');
        } else if (percentage >= 70) {
            counter.parentElement.classList.add('warning');
        }
    }

    /**
     * Инициализация генерации slug
     */
    initSlugGeneration() {
        const titleInput = document.getElementById('title');
        const slugInput = document.getElementById('slug');
        const generateSlugBtn = document.getElementById('generate-slug');
        const slugPreview = document.getElementById('slug-preview');

        if (!titleInput || !slugInput) return;

        // Автогенерация slug при изменении заголовка
        titleInput.addEventListener('input', () => {
            if (!slugInput.value || slugInput.getAttribute('data-manual') !== 'true') {
                slugInput.value = this.generateSlug(titleInput.value);
                this.updateSlugPreview();
            }
        });

        // Кнопка генерации slug
        if (generateSlugBtn) {
            generateSlugBtn.addEventListener('click', () => {
                slugInput.value = this.generateSlug(titleInput.value);
                slugInput.setAttribute('data-manual', 'true');
                this.updateSlugPreview();
            });
        }

        // Пометить slug как ручной ввод
        slugInput.addEventListener('input', () => {
            slugInput.setAttribute('data-manual', 'true');
            this.updateSlugPreview();
        });

        // Функция обновления предпросмотра
        this.updateSlugPreview = () => {
            if (slugPreview) {
                slugPreview.textContent = '/' + (slugInput.value || 'url-stranicy');
            }
        };

        // Инициализация предпросмотра
        this.updateSlugPreview();
    }

    /**
     * Генерация slug из текста
     */
    generateSlug(text) {
        return text.toLowerCase()
            .trim()
            .replace(/[^\w\s-]/g, '')
            .replace(/[\s_-]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    /**
     * Управление полем даты публикации
     */
    initPublishedAtField() {
        const statusSelect = document.getElementById('status');
        const publishedAtField = document.getElementById('published-at-field');

        if (!statusSelect || !publishedAtField) return;

        const toggleField = () => {
            if (statusSelect.value === 'published') {
                publishedAtField.style.display = 'block';
                
                // Установить текущую дату и время, если поле пустое
                const publishedAtInput = document.getElementById('published_at');
                if (publishedAtInput && !publishedAtInput.value) {
                    const now = new Date();
                    publishedAtInput.value = now.toISOString().slice(0, 16);
                }
            } else {
                publishedAtField.style.display = 'none';
            }
        };

        statusSelect.addEventListener('change', toggleField);
        toggleField(); // Инициализация
    }

    /**
     * Обработка отправки формы
     */
    initFormSubmit() {
        const forms = document.querySelectorAll('form[data-page-form]');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Сохранение...';
                    submitBtn.disabled = true;
                }
            });
        });
    }

    /**
     * Инициализация модальных окон
     */
    initModalHandlers() {
        // Модальное окно удаления в корзину
        this.initDeleteModal();
        
        // Модальное окно полного удаления
        this.initForceDeleteModal();
    }

    /**
     * Модальное окно удаления в корзину
     */
    initDeleteModal() {
        const deleteButtons = document.querySelectorAll('.delete-page-btn');
        const deleteModal = document.getElementById('deletePageModal');
        const pageTitleToDelete = document.getElementById('pageTitleToDelete');
        const deleteForm = document.getElementById('deletePageForm');

        if (!deleteButtons.length || !deleteModal || !pageTitleToDelete || !deleteForm) return;

        deleteButtons.forEach(button => {
            button.addEventListener('click', () => {
                const pageTitle = button.getAttribute('data-page-title');
                const deleteUrl = button.getAttribute('data-delete-url');

                pageTitleToDelete.textContent = pageTitle;
                deleteForm.action = deleteUrl;
            });
        });
    }

    /**
     * Модальное окно полного удаления
     */
    initForceDeleteModal() {
        const forceDeleteButtons = document.querySelectorAll('.force-delete-btn');
        const forceDeleteModal = document.getElementById('forceDeleteModal');
        const pageTitleToForceDelete = document.getElementById('pageTitleToForceDelete');
        const forceDeleteForm = document.getElementById('forceDeleteForm');

        if (!forceDeleteButtons.length || !forceDeleteModal || !pageTitleToForceDelete || !forceDeleteForm) return;

        forceDeleteButtons.forEach(button => {
            button.addEventListener('click', () => {
                const pageTitle = button.getAttribute('data-page-title');
                const forceUrl = button.getAttribute('data-force-url');

                pageTitleToForceDelete.textContent = pageTitle;
                forceDeleteForm.action = forceUrl;
            });
        });
    }

    /**
     * Инициализация счетчиков символов для конкретных полей
     */
    initCounterHandlers() {
        // Автозаполнение мета-заголовка из заголовка
        const titleInput = document.getElementById('title');
        const metaTitleInput = document.getElementById('meta_title');
        const metaTitleCounter = document.getElementById('meta-title-counter');

        if (titleInput && metaTitleInput && metaTitleCounter) {
            titleInput.addEventListener('blur', () => {
                if (!metaTitleInput.value.trim()) {
                    metaTitleInput.value = titleInput.value;
                    this.updateCharCounter(metaTitleInput, metaTitleCounter);
                }
            });
        }
    }
}

// Инициализация при загрузке документа
document.addEventListener('DOMContentLoaded', () => {
    new PageModule();
});