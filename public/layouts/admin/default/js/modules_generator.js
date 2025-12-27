document.addEventListener('DOMContentLoaded', function () {
    // Анимация появления элементов при загрузке
    const fadeElements = document.querySelectorAll('.fade-in');
    fadeElements.forEach(el => {
        el.style.opacity = '0';
    });

    setTimeout(() => {
        fadeElements.forEach(el => {
            el.style.opacity = '1';
        });
    }, 100);

    // Подтверждение удаления модуля
    const deleteButtons = document.querySelectorAll('.delete-module-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            if (!confirm('Вы уверены, что хотите удалить этот модуль?')) {
                e.preventDefault();
                return false;
            }
        });
    });

    // Поиск модулей (если есть поле поиска)
    const searchInput = document.querySelector('input[placeholder*="модул"]');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.modules-table tbody tr');

            rows.forEach(row => {
                const moduleName = row.querySelector('.module-name').textContent.toLowerCase();
                const moduleDescription = row.querySelector('.module-description').textContent.toLowerCase();

                if (moduleName.includes(searchTerm) || moduleDescription.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            // Обновляем счетчик найденных модулей
            const visibleCount = document.querySelectorAll('.modules-table tbody tr[style=""]').length;
            const totalCount = rows.length;
            const countElement = document.querySelector('.modules-count');

            if (countElement) {
                countElement.textContent = `Найдено ${visibleCount} из ${totalCount} модулей`;
            }
        });
    }

    // Сортировка таблицы по заголовкам
    const tableHeaders = document.querySelectorAll('.modules-table th');
    tableHeaders.forEach((header, index) => {
        if (index < 4) { // Не добавляем сортировку для колонки действий
            header.style.cursor = 'pointer';
            header.addEventListener('click', function () {
                sortTable(index);
            });
        }
    });

    // Функция сортировки таблицы
    function sortTable(columnIndex) {
        const table = document.querySelector('.modules-table tbody');
        const rows = Array.from(table.querySelectorAll('tr'));
        const isAscending = table.dataset.sortColumn === String(columnIndex) && table.dataset.sortOrder === 'asc';

        rows.sort((rowA, rowB) => {
            const cellA = rowA.cells[columnIndex].textContent.trim();
            const cellB = rowB.cells[columnIndex].textContent.trim();

            // Для числовых значений (версия)
            if (columnIndex === 1) {
                const numA = parseFloat(cellA) || 0;
                const numB = parseFloat(cellB) || 0;
                return isAscending ? numA - numB : numB - numA;
            }

            // Для дат (дата создания)
            if (columnIndex === 3) {
                const dateA = parseDate(cellA);
                const dateB = parseDate(cellB);
                return isAscending ? dateA - dateB : dateB - dateA;
            }

            // Для строк (название, статус)
            return isAscending ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
        });

        // Очищаем таблицу и добавляем отсортированные строки
        table.innerHTML = '';
        rows.forEach(row => table.appendChild(row));

        // Сохраняем состояние сортировки
        table.dataset.sortColumn = columnIndex;
        table.dataset.sortOrder = isAscending ? 'desc' : 'asc';

        // Обновляем индикаторы сортировки
        updateSortIndicators(columnIndex, isAscending ? 'desc' : 'asc');
    }

    // Функция для парсинга даты
    function parseDate(dateString) {
        const parts = dateString.split('.');
        if (parts.length === 3) {
            return new Date(parts[2], parts[1] - 1, parts[0]).getTime();
        }
        return new Date(dateString).getTime();
    }

    // Функция для обновления индикаторов сортировки
    function updateSortIndicators(columnIndex, order) {
        tableHeaders.forEach((header, index) => {
            header.classList.remove('sort-asc', 'sort-desc');

            if (index === columnIndex) {
                header.classList.add(`sort-${order}`);
            }
        });
    }

    // Добавляем стили для индикаторов сортировки
    const style = document.createElement('style');
    style.textContent = `
        .sort-asc::after {
            content: ' ↗';
            font-weight: bold;
        }
        .sort-desc::after {
            content: ' ↘';
            font-weight: bold;
        }
    `;
    document.head.appendChild(style);

    // Фильтрация по статусу
    // const statusFilter = document.createElement('div');
    // statusFilter.className = 'status-filter mb-3';
    // statusFilter.innerHTML = `
    //     <div class="d-flex gap-2 flex-wrap">
    //         <span class="text-muted" style="font-size: 0.85rem;">Фильтр по статусу:</span>
    //         <button class="btn btn-sm btn-outline-primary status-filter-btn active" data-status="all">Все</button>
    //         <button class="btn btn-sm btn-outline-success status-filter-btn" data-status="active">Активные</button>
    //         <button class="btn btn-sm btn-outline-secondary status-filter-btn" data-status="disabled">Отключенные</button>
    //     </div>
    // `;

    const cardHeader = document.querySelector('.modules-list-header');
    if (cardHeader) {
        cardHeader.parentNode.insertBefore(statusFilter, cardHeader.nextSibling);

        // Обработчики для кнопок фильтрации
        const filterButtons = document.querySelectorAll('.status-filter-btn');
        filterButtons.forEach(button => {
            button.addEventListener('click', function () {
                // Убираем активный класс у всех кнопок
                filterButtons.forEach(btn => btn.classList.remove('active'));
                // Добавляем активный класс текущей кнопке
                this.classList.add('active');

                const status = this.dataset.status;
                filterModulesByStatus(status);
            });
        });
    }

    // Функция фильтрации модулей по статусу
    function filterModulesByStatus(status) {
        const rows = document.querySelectorAll('.modules-table tbody tr');

        rows.forEach(row => {
            const statusElement = row.querySelector('.module-status');
            const rowStatus = getStatusFromElement(statusElement);

            if (status === 'all' || rowStatus === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Обновляем счетчик
        updateModuleCount();
    }

    // Функция для получения статуса из элемента
    function getStatusFromElement(element) {
        if (element.classList.contains('status-active')) return 'active';
        if (element.classList.contains('status-inactive')) return 'inactive';
        if (element.classList.contains('status-disabled')) return 'disabled';
        return '';
    }

    // Функция для обновления счетчика модулей
    function updateModuleCount() {
        const rows = document.querySelectorAll('.modules-table tbody tr');
        const visibleCount = Array.from(rows).filter(row => row.style.display !== 'none').length;
        const totalCount = rows.length;
        const countElement = document.querySelector('.modules-count');

        if (countElement) {
            countElement.textContent = `Показано ${visibleCount} из ${totalCount} модулей`;
        }
    }

    // Инициализация счетчика
    updateModuleCount();
});

document.addEventListener('DOMContentLoaded', function () {
    // Инициализация Bootstrap тултипов
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Автоматическое скрытие алертов
    setTimeout(function () {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function (alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});