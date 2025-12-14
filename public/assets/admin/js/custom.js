document.addEventListener('DOMContentLoaded', function () {
    const menuItemsContainer = document.getElementById('menuItems');
    const addItemBtn = document.getElementById('addItemBtn');
    let itemCount = 1; // Начинаем с 1, так как первая строка уже есть

    // Функция для добавления новой строки
    addItemBtn.addEventListener('click', function () {
        const newRow = document.createElement('tr');
        newRow.className = 'menu-item-row';

        newRow.innerHTML = `
                    <td>
                        <input type="text" class="form-control" 
                               name="items[${itemCount}][title]" placeholder="Введите название">
                    </td>
                    <td>
                        <input type="text" class="form-control" 
                               name="items[${itemCount}][url]" placeholder="Введите URL">
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-sm btn-danger remove-item-btn">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                `;

        menuItemsContainer.appendChild(newRow);
        itemCount++;

        // Активируем кнопки удаления, если строк больше одной
        updateRemoveButtons();
    });

    // Делегирование событий для кнопок удаления
    menuItemsContainer.addEventListener('click', function (e) {
        if (e.target.closest('.remove-item-btn') && !e.target.closest('.remove-item-btn').disabled) {
            e.target.closest('.menu-item-row').remove();
            updateRemoveButtons();
        }
    });

    // Функция для обновления состояния кнопок удаления
    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.menu-item-row');
        const removeButtons = document.querySelectorAll('.remove-item-btn');

        if (rows.length <= 1) {
            removeButtons.forEach(btn => {
                btn.disabled = true;
                btn.classList.add('disabled');
            });
        } else {
            removeButtons.forEach(btn => {
                btn.disabled = false;
                btn.classList.remove('disabled');
            });
        }
    }
});

// Всплывающий текст для вопросика (подсказка)
document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});

// Скрыть \ показать пароль в input
function togglePasswordVisibility(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const eyeIcon = document.getElementById(fieldId + '-eye');

    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}
