/**
 * JavaScript для модуля информационных блоков
 * Включает: обработку форм, модальных окон, счетчиков символов и предпросмотр
 */
class IBlockModule {
    constructor() {
        this.init();
    }

    init() {
        this.initFormHandlers();
        this.initModalHandlers();
        this.initCounterHandlers();
        this.initTooltips();
        this.initEditPage();
    }

    /**
     * Инициализация обработчиков форм
     */
    initFormHandlers() {
        // Инициализация счетчиков символов для всех форм
        this.initCharCounters();
        
        // Обработка отправки формы
        this.initFormSubmit();
        
        // Специфичные для страницы редактирования счетчики
        this.initEditPageCounters();
    }

    /**
     * Инициализация счетчиков символов для общих форм
     */
    initCharCounters() {
        const titleInput = document.getElementById('title');
        const titleCounter = document.getElementById('title-counter');
        
        if (titleInput && titleCounter) {
            this.updateCharCounter(titleInput, titleCounter);
            titleInput.addEventListener('input', () => this.updateCharCounter(titleInput, titleCounter));
        }
    }

    /**
     * Инициализация счетчиков символов для страницы редактирования
     */
    initEditPageCounters() {
        const editTitleInput = document.getElementById('title');
        const editContentTextarea = document.getElementById('content');
        const editTitleCharCount = document.getElementById('titleCharCount');
        const editContentCharCount = document.getElementById('contentCharCount');

        if (editTitleInput && editTitleCharCount) {
            this.updateEditTitleCharCount();
            editTitleInput.addEventListener('input', () => this.updateEditTitleCharCount());
        }

        if (editContentTextarea && editContentCharCount) {
            this.updateEditContentCharCount();
            editContentTextarea.addEventListener('input', () => this.updateEditContentCharCount());
        }
    }

    /**
     * Обновление счетчика символов для общего случая
     */
    updateCharCounter(input, counter) {
        if (!input || !counter) return;
        
        const length = input.value.length;
        const maxLength = input.getAttribute('maxlength') || 255;
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
     * Счетчик символов для заголовка на странице редактирования
     */
    updateEditTitleCharCount() {
        const titleInput = document.getElementById('title');
        const titleCharCount = document.getElementById('titleCharCount');
        
        if (!titleInput || !titleCharCount) return;
        
        const length = titleInput.value.length;
        titleCharCount.textContent = length;
        
        if (length > 200) {
            titleCharCount.className = 'char-counter warning';
        } else if (length > 250) {
            titleCharCount.className = 'char-counter danger';
        } else {
            titleCharCount.className = 'char-counter';
        }
    }

    /**
     * Счетчик символов для содержимого на странице редактирования
     */
    updateEditContentCharCount() {
        const contentTextarea = document.getElementById('content');
        const contentCharCount = document.getElementById('contentCharCount');
        
        if (!contentTextarea || !contentCharCount) return;
        
        const length = contentTextarea.value.length;
        contentCharCount.textContent = length;
        
        if (length < 10) {
            contentCharCount.className = 'char-counter danger';
        } else if (length < 50) {
            contentCharCount.className = 'char-counter warning';
        } else {
            contentCharCount.className = 'char-counter';
        }
    }

    /**
     * Обработка отправки формы
     */
    initFormSubmit() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Сохранение...';
                    submitBtn.disabled = true;
                    
                    // Восстановление кнопки через 5 секунд на случай ошибки
                    setTimeout(() => {
                        if (submitBtn.disabled) {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }
                    }, 5000);
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
        
        // Модальное окно предпросмотра
        this.initPreviewModal();
        
        // Модальное окно удаления на странице редактирования
        this.initEditPageDeleteModal();
    }

    /**
     * Модальное окно удаления в корзину
     */
    initDeleteModal() {
        const deleteButtons = document.querySelectorAll('.delete-iblock-btn');
        const deleteModal = document.getElementById('deleteIBlockModal');
        const iblockTitleToDelete = document.getElementById('iblockTitleToDelete');
        const deleteForm = document.getElementById('deleteIBlockForm');

        if (!deleteButtons.length || !deleteModal || !iblockTitleToDelete || !deleteForm) return;

        deleteButtons.forEach(button => {
            button.addEventListener('click', () => {
                const iblockTitle = button.getAttribute('data-iblock-title');
                const deleteUrl = button.getAttribute('data-delete-url');

                iblockTitleToDelete.textContent = iblockTitle;
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
        const iblockTitleToForceDelete = document.getElementById('iblockTitleToForceDelete');
        const forceDeleteForm = document.getElementById('forceDeleteForm');

        if (!forceDeleteButtons.length || !forceDeleteModal || !iblockTitleToForceDelete || !forceDeleteForm) return;

        forceDeleteButtons.forEach(button => {
            button.addEventListener('click', () => {
                const iblockTitle = button.getAttribute('data-iblock-title');
                const forceUrl = button.getAttribute('data-force-url');

                iblockTitleToForceDelete.textContent = iblockTitle;
                forceDeleteForm.action = forceUrl;
            });
        });
    }

    /**
     * Модальное окно предпросмотра
     */
    initPreviewModal() {
        const previewBtn = document.getElementById('previewBtn');
        const previewModal = document.getElementById('previewModal');
        const previewContent = document.getElementById('previewContent');
        const deleteBtn = document.getElementById('deleteBtn');

        if (!previewBtn || !previewModal) return;

        previewBtn.addEventListener('click', () => {
            const titleInput = document.getElementById('title');
            const contentTextarea = document.getElementById('content');
            
            const title = titleInput ? titleInput.value || 'Без названия' : 'Без названия';
            const content = contentTextarea ? contentTextarea.value || 'Содержимое отсутствует' : 'Содержимое отсутствует';
            
            const iblockId = deleteBtn ? deleteBtn.getAttribute('data-iblock-id') : '';
            
            const previewHTML = `
                <div class="preview-container">
                    <h3 class="border-bottom pb-2 mb-3">${this.escapeHtml(title)}</h3>
                    <div class="content-preview mb-3">
                        ${content}
                    </div>
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">Информация о блоке</h6>
                        </div>
                        <div class="card-body small">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>ID:</strong> ${iblockId || 'Новый'}
                                </div>
                                <div class="col-md-6">
                                    <strong>Статус:</strong> Активен
                                </div>
                                <div class="col-md-6">
                                    <strong>Длина заголовка:</strong> ${title.length} символов
                                </div>
                                <div class="col-md-6">
                                    <strong>Длина содержимого:</strong> ${content.length} символов
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            if (previewContent) {
                previewContent.innerHTML = previewHTML;
            }
            
            const modalInstance = new bootstrap.Modal(previewModal);
            modalInstance.show();
        });
    }

    /**
     * Модальное окно удаления на странице редактирования
     */
    initEditPageDeleteModal() {
        const deleteBtn = document.getElementById('deleteBtn');
        const deleteModal = document.getElementById('deleteModal');

        if (!deleteBtn || !deleteModal) return;

        deleteBtn.addEventListener('click', () => {
            const modalInstance = new bootstrap.Modal(deleteModal);
            modalInstance.show();
        });
    }

    /**
     * Инициализация tooltips
     */
    initTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    /**
     * Инициализация счетчиков символов для конкретных полей
     */
    initCounterHandlers() {
        // Автоматическое обновление счетчика при загрузке страницы
        document.querySelectorAll('[maxlength]').forEach(input => {
            const counterId = input.id + '-counter';
            const counter = document.getElementById(counterId);
            if (counter) {
                this.updateCharCounter(input, counter);
            }
        });
    }

    /**
     * Инициализация страницы редактирования
     */
    initEditPage() {
        const editForm = document.getElementById('editIBlockForm');
        
        if (!editForm) return;

        // Валидация формы редактирования
        editForm.addEventListener('submit', (event) => {
            const titleInput = document.getElementById('title');
            const contentTextarea = document.getElementById('content');
            
            if (!titleInput || !contentTextarea) return;
            
            const title = titleInput.value.trim();
            const content = contentTextarea.value.trim();
            
            if (!title || title.length < 3) {
                event.preventDefault();
                alert('Название блока должно содержать минимум 3 символа');
                titleInput.focus();
                return;
            }
            
            if (!content || content.length < 10) {
                event.preventDefault();
                alert('Содержимое блока должно содержать минимум 10 символов');
                contentTextarea.focus();
                return;
            }
        });
    }

    /**
     * Функция для экранирования HTML
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Показать уведомление
     */
    showNotification(message, type = 'success') {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alert.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        `;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alert);
        
        // Автоматическое скрытие через 5 секунд
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
}

// Инициализация при загрузке документа
document.addEventListener('DOMContentLoaded', () => {
    new IBlockModule();
    
    // Инициализация Bootstrap компонентов
    if (typeof bootstrap !== 'undefined') {
        // Активация всех модальных окон
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (!modal.id || modal.id !== 'previewModal') {
                new bootstrap.Modal(modal);
            }
        });
    }
});