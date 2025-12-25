// Общий JavaScript для управления пользователями
document.addEventListener('DOMContentLoaded', function () {
    console.log('Страница управления пользователями загружена');
    
    // Определяем текущую страницу по URL
    const currentPath = window.location.pathname;
    const isIndexPage = currentPath.includes('/users') && 
                       !currentPath.includes('/create') && 
                       !currentPath.includes('/edit') &&
                       !currentPath.includes('/store') &&
                       !currentPath.includes('/update');
    const isCreatePage = currentPath.includes('/create');
    const isEditPage = currentPath.includes('/edit');
    
    console.log('Текущая страница:', { currentPath, isIndexPage, isCreatePage, isEditPage });
    
    // ============================================
    // ОБЩИЕ ФУНКЦИИ ДЛЯ ВСЕХ СТРАНИЦ
    // ============================================
    
    // Обработчик модального окна удаления (есть на index и edit)
    const deleteUserModal = document.getElementById('deleteUserModal');
    if (deleteUserModal) {
        console.log('Модальное окно удаления найдено');
        deleteUserModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const userName = button.getAttribute('data-user-name');
            const deleteUrl = button.getAttribute('data-delete-url');

            console.log('Открытие модального окна для удаления:', { userName, deleteUrl });
            
            document.getElementById('userNameToDelete').textContent = userName;
            document.getElementById('deleteUserForm').action = deleteUrl;
        });
    }
    
    // Уведомление о системном пользователе при попытке удаления
    const deleteButtons = document.querySelectorAll('.delete-user-btn[disabled]');
    if (deleteButtons.length > 0) {
        console.log('Найдено заблокированных кнопок удаления:', deleteButtons.length);
        
        // Создаем toast контейнер, если его нет
        if (!document.querySelector('.toast-container')) {
            const toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            toastContainer.innerHTML = `
                <div id="systemUserToast" class="toast align-items-center text-bg-warning border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Пользователь <strong id="systemUserName"></strong> является системным и не может быть удален
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            document.body.appendChild(toastContainer);
        }
        
        // Добавляем обработчики для заблокированных кнопок
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const userName = this.getAttribute('data-user-name');
                console.log('Попытка удаления системного пользователя:', userName);
                
                // Показываем toast уведомление
                const toast = new bootstrap.Toast(document.getElementById('systemUserToast'));
                document.getElementById('systemUserName').textContent = userName;
                toast.show();
            });
        });
    }
    
    // ============================================
    // ЛОГИКА ДЛЯ СТРАНИЦЫ СПИСКА ПОЛЬЗОВАТЕЛЕЙ (index)
    // ============================================
    if (isIndexPage) {
        console.log('Инициализация страницы списка пользователей');
        
        // Авто-сабмит при изменении фильтров (ТОЛЬКО на странице списка)
        const filterSelects = document.querySelectorAll('select[name="per_page"], select[name="role_id"], select[name="status"]');
        console.log('Найдено фильтров для авто-сабмита:', filterSelects.length);
        
        filterSelects.forEach(select => {
            // Удаляем предыдущие обработчики (если есть)
            const newSelect = select.cloneNode(true);
            select.parentNode.replaceChild(newSelect, select);
            
            // Добавляем обработчик на новый элемент
            const freshSelect = document.querySelector(`select[name="${newSelect.name}"]`);
            if (freshSelect) {
                freshSelect.addEventListener('change', function () {
                    console.log('Изменен фильтр:', this.name, this.value);
                    this.closest('form').submit();
                });
            }
        });
    }
    
    // ============================================
    // ЛОГИКА ДЛЯ СТРАНИЦЫ СОЗДАНИЯ ПОЛЬЗОВАТЕЛЯ (create)
    // ============================================
    if (isCreatePage) {
        console.log('Инициализация страницы создания пользователя');
        
        // ОТКЛЮЧАЕМ авто-сабмит для всех select на странице создания
        const allSelectsOnCreate = document.querySelectorAll('select');
        allSelectsOnCreate.forEach(select => {
            // Клонируем select без обработчиков событий
            const newSelect = select.cloneNode(true);
            select.parentNode.replaceChild(newSelect, select);
        });
        
        // Валидация формы создания
        const createForm = document.getElementById('createUserForm');
        if (createForm) {
            console.log('Форма создания найдена');
            
            createForm.addEventListener('submit', function (e) {
                const password = document.getElementById('password');
                const confirmPassword = document.getElementById('password_confirmation');
                
                if (password && confirmPassword) {
                    if (password.value !== confirmPassword.value) {
                        e.preventDefault();
                        console.log('Пароли не совпадают');
                        alert('Пароли не совпадают!');
                        password.focus();
                    }
                }
            });
        }
        
        // Маска для телефона
        const phoneInputCreate = document.getElementById('phone');
        if (phoneInputCreate) {
            console.log('Инициализация маски телефона для создания');
            
            phoneInputCreate.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, '');
                
                if (value.length > 0) {
                    if (value.length <= 3) {
                        value = '+7 (' + value;
                    } else if (value.length <= 6) {
                        value = '+7 (' + value.substring(0, 3) + ') ' + value.substring(3);
                    } else if (value.length <= 8) {
                        value = '+7 (' + value.substring(0, 3) + ') ' + value.substring(3, 6) + '-' + value.substring(6);
                    } else {
                        value = '+7 (' + value.substring(0, 3) + ') ' + value.substring(3, 6) + '-' + value.substring(6, 8) + '-' + value.substring(8, 10);
                    }
                }
                
                e.target.value = value;
            });
        }
    }
    
    // ============================================
    // ЛОГИКА ДЛЯ СТРАНИЦЫ РЕДАКТИРОВАНИЯ ПОЛЬЗОВАТЕЛЯ (edit)
    // ============================================
    if (isEditPage) {
        console.log('Инициализация страницы редактирования пользователя');
        
        // Валидация формы редактирования
        const editForm = document.getElementById('editUserForm');
        if (editForm) {
            console.log('Форма редактирования найдена');
            
            editForm.addEventListener('submit', function (e) {
                const password = document.getElementById('password');
                const confirmPassword = document.getElementById('password_confirmation');
                
                if (password && confirmPassword) {
                    if (password.value && password.value !== confirmPassword.value) {
                        e.preventDefault();
                        console.log('Пароли не совпадают');
                        alert('Пароли не совпадают!');
                        password.focus();
                    }
                }
            });
        }
        
        // Маска для телефона
        const phoneInputEdit = document.getElementById('phone');
        if (phoneInputEdit) {
            console.log('Инициализация маски телефона для редактирования');
            
            phoneInputEdit.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, '');
                
                if (value.length > 0) {
                    if (value.length <= 3) {
                        value = '+7 (' + value;
                    } else if (value.length <= 6) {
                        value = '+7 (' + value.substring(0, 3) + ') ' + value.substring(3);
                    } else if (value.length <= 8) {
                        value = '+7 (' + value.substring(0, 3) + ') ' + value.substring(3, 6) + '-' + value.substring(6);
                    } else {
                        value = '+7 (' + value.substring(0, 3) + ') ' + value.substring(3, 6) + '-' + value.substring(6, 8) + '-' + value.substring(8, 10);
                    }
                }
                
                e.target.value = value;
            });
        }
        
        // Показывать/скрывать поле подтверждения пароля
        const passwordField = document.getElementById('password');
        const confirmField = document.getElementById('password_confirmation');
        
        if (passwordField && confirmField) {
            passwordField.addEventListener('input', function () {
                if (this.value.length > 0) {
                    confirmField.required = true;
                    console.log('Поле подтверждения пароля стало обязательным');
                } else {
                    confirmField.required = false;
                    console.log('Поле подтверждения пароля стало необязательным');
                }
            });
            
            // Инициализируем состояние при загрузке
            if (passwordField.value.length > 0) {
                confirmField.required = true;
            }
        }
    }
    
    // ============================================
    // ДОПОЛНИТЕЛЬНЫЕ ПРОВЕРКИ И ОБРАБОТЧИКИ
    // ============================================
    
    // Проверка загрузки Bootstrap
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap не загружен! Модальные окна и toast не будут работать.');
    } else {
        console.log('Bootstrap успешно загружен');
    }
    
    // Обработчик для всех форм с подтверждением удаления
    document.querySelectorAll('form[onsubmit*="confirm"]').forEach(form => {
        const originalOnsubmit = form.onsubmit;
        form.onsubmit = function(e) {
            const result = originalOnsubmit.call(this, e);
            if (result === false) {
                e.preventDefault();
            }
            return result;
        };
    });
});