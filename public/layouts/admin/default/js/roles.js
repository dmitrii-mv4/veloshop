document.addEventListener('DOMContentLoaded', function() {
    // Инициализация popover для index страницы
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    if (popoverTriggerList.length > 0) {
        const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
    }
    
    // Обработка удаления роли для index и edit страниц
    const deleteRoleButtons = document.querySelectorAll('.delete-role-btn');
    const deleteRoleModal = document.getElementById('deleteRoleModal');
    const roleNameToDelete = document.getElementById('roleNameToDelete');
    const deleteRoleForm = document.getElementById('deleteRoleForm');
    
    if (deleteRoleButtons.length > 0) {
        deleteRoleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const roleName = this.getAttribute('data-role-name');
                const deleteUrl = this.getAttribute('data-delete-url');
                
                if (roleNameToDelete) {
                    roleNameToDelete.textContent = roleName;
                }
                
                if (deleteRoleForm) {
                    deleteRoleForm.action = deleteUrl;
                }
            });
        });
        
        // Если модальное окно скрыто, сбрасываем форму
        if (deleteRoleModal) {
            deleteRoleModal.addEventListener('hidden.bs.modal', function() {
                if (roleNameToDelete) {
                    roleNameToDelete.textContent = '';
                }
                if (deleteRoleForm) {
                    deleteRoleForm.action = '';
                }
            });
        }
    }
    
    // Код для управления разрешениями (только для create и edit страниц)
    const permissionsTab = document.getElementById('permissionsTab');
    if (permissionsTab) {
        // Определяем, какая роль редактируется
        const roleForm = document.getElementById('role-edit-form');
        const isAdminRole = roleForm ? roleForm.dataset.roleId === '1' : false;
        
        // Проверяем наличие элементов для управления разрешениями
        const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
        const selectAllModuleButtons = document.querySelectorAll('.select-all-module');
        const deselectAllModuleButtons = document.querySelectorAll('.deselect-all-module');
        
        // Обработка быстрого выбора разрешений для модуля (только если не Администратор)
        if (!isAdminRole && selectAllModuleButtons.length > 0) {
            selectAllModuleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const module = this.getAttribute('data-module');
                    const checkboxes = document.querySelectorAll(`.permission-checkbox[data-module="${module}"]:not(:disabled)`);
                    
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = true;
                    });
                    
                    // Обновляем счетчики во вкладках
                    updateTabBadges();
                });
            });
        }
        
        if (!isAdminRole && deselectAllModuleButtons.length > 0) {
            deselectAllModuleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const module = this.getAttribute('data-module');
                    const checkboxes = document.querySelectorAll(`.permission-checkbox[data-module="${module}"]:not(:disabled)`);
                    
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    
                    // Обновляем счетчики во вкладках
                    updateTabBadges();
                });
            });
        }
        
        // Создаем и добавляем глобальные кнопки "Выбрать все" и "Снять все" только если не Администратор
        if (!isAdminRole && permissionCheckboxes.length > 0) {
            const selectAllBtn = document.createElement('button');
            selectAllBtn.type = 'button';
            selectAllBtn.className = 'btn btn-outline-primary btn-sm mb-3 me-2';
            selectAllBtn.innerHTML = '<i class="bi bi-check-square me-1"></i> Выбрать все разрешения';
            selectAllBtn.id = 'select-all-permissions';
            
            const deselectAllBtn = document.createElement('button');
            deselectAllBtn.type = 'button';
            deselectAllBtn.className = 'btn btn-outline-secondary btn-sm mb-3';
            deselectAllBtn.innerHTML = '<i class="bi bi-x-square me-1"></i> Снять все разрешения';
            deselectAllBtn.id = 'deselect-all-permissions';
            
            // Находим контейнер для кнопок (перед вкладками)
            const tabContainer = permissionsTab.parentElement;
            const buttonContainer = document.createElement('div');
            buttonContainer.className = 'd-flex mb-3';
            buttonContainer.appendChild(selectAllBtn);
            buttonContainer.appendChild(deselectAllBtn);
            
            // Вставляем кнопки перед вкладками
            tabContainer.insertBefore(buttonContainer, permissionsTab);
            
            // Обработчики для глобальных кнопок
            selectAllBtn.addEventListener('click', function() {
                permissionCheckboxes.forEach(checkbox => {
                    if (!checkbox.disabled) {
                        checkbox.checked = true;
                    }
                });
                
                // Обновляем счетчики во вкладках
                updateTabBadges();
            });
            
            deselectAllBtn.addEventListener('click', function() {
                permissionCheckboxes.forEach(checkbox => {
                    if (!checkbox.disabled) {
                        checkbox.checked = false;
                    }
                });
                
                // Обновляем счетчики во вкладках
                updateTabBadges();
            });
        }
        
        // Обработка клика по чекбоксам для обновления счетчика во вкладках
        if (!isAdminRole) {
            permissionCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateTabBadges();
                });
            });
        }
        
        // Функция для обновления счетчиков во вкладках
        function updateTabBadges() {
            const tabs = document.querySelectorAll('#permissionsTab .nav-link');
            
            tabs.forEach(tab => {
                const target = tab.getAttribute('data-bs-target');
                const module = target.replace('#', '').replace('-pane', '');
                
                if (module) {
                    const checkboxes = document.querySelectorAll(`.permission-checkbox[data-module="${module}"]:not(:disabled)`);
                    const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
                    
                    // Находим или создаем бейдж
                    let badge = tab.querySelector('.badge');
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'badge bg-primary ms-1';
                        tab.appendChild(badge);
                    }
                    
                    if (checkedCount > 0) {
                        badge.textContent = checkedCount;
                        badge.style.display = 'inline';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            });
        }
        
        // Инициализируем счетчики при загрузке
        setTimeout(updateTabBadges, 100);
    }
    
    // Дополнительная функциональность для форм
    const roleForm = document.querySelector('form[action*="roles"]');
    if (roleForm) {
        // Безопасно получаем данные из data-атрибутов
        const roleId = roleForm.dataset.roleId ? parseInt(roleForm.dataset.roleId) : 0;
        const isSystemRole = roleForm.dataset.isSystem === 'true';
        const isAdminRole = roleId === 1;
        
        // Для роли Администратора блокируем отправку формы
        if (isAdminRole) {
            roleForm.addEventListener('submit', function(e) {
                e.preventDefault();
                alert('Роль "Администратор" защищена от изменений');
                return false;
            });
        } 
        // Для системных ролей (кроме роли "Пользователь") проверяем разрешения пользователя
        else if (isSystemRole && roleId !== 3) {
            roleForm.addEventListener('submit', function(e) {
                if (!confirm('Вы редактируете системную роль. Убедитесь, что у вас есть необходимые права.')) {
                    e.preventDefault();
                    return false;
                }
            });
        }
    }
});