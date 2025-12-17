/**
 * JavaScript для страницы создания интеграции (модуль Integration)
 * Путь: public/layouts/admin/modules/integrations/js/create.js
 * 
 * @description Управление 4-шаговым процессом создания интеграции:
 * 1. Выбор внешнего сервиса
 * 2. Настройки подключения к выбранному сервису
 * 3. Выбор внутреннего модуля
 * 4. Сопоставление полей
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Страница создания интеграции загружена');
    
    // Хранилище данных интеграции
    window.integrationData = {
        selectedService: null,
        selectedServiceName: null,
        selectedServiceData: {},
        selectedModule: null,
        selectedModuleName: null,
        serviceSettings: {},
        fieldMapping: []
    };

    // Инициализация всех компонентов
    initServiceSelection();
    initModuleSelection();
    initNavigation();
    initEventHandlers();
    initDynamicFields();
    initModulePreview();
    
    // Инициализируем первый шаг
    goToStep(1);
    
    console.log('Инициализация завершена');
});

/**
 * Инициализация выбора внешнего сервиса
 */
function initServiceSelection() {
    console.log('Инициализация выбора внешнего сервиса');
    
    const serviceCards = document.querySelectorAll('.service-card[data-service]');
    
    serviceCards.forEach(card => {
        card.addEventListener('click', function() {
            // Убираем выделение у всех карточек
            serviceCards.forEach(c => c.classList.remove('selected'));
            
            // Выделяем выбранную
            this.classList.add('selected');
            
            // Получаем данные из data-атрибутов
            const service = this.getAttribute('data-service');
            const serviceName = this.getAttribute('data-service-name');
            
            // Получаем дополнительные данные из карточки
            const serviceBadge = this.querySelector('.badge');
            const serviceIcon = this.querySelector('i');
            const serviceDescription = this.querySelector('.text-muted');
            
            // Сохраняем в глобальные данные
            window.integrationData.selectedService = service;
            window.integrationData.selectedServiceName = serviceName;
            window.integrationData.selectedServiceData = {
                service: service,
                name: serviceName,
                category: serviceBadge ? serviceBadge.textContent : '',
                icon: serviceIcon ? serviceIcon.className : '',
                description: serviceDescription ? serviceDescription.textContent : ''
            };
            
            // Заполняем скрытые поля формы (если они есть)
            const hiddenServiceField = document.getElementById('selected_service');
            const hiddenServiceNameField = document.getElementById('selected_service_name');
            
            if (hiddenServiceField) hiddenServiceField.value = service;
            if (hiddenServiceNameField) hiddenServiceNameField.value = serviceName;
            
            // Показываем информацию о выборе
            showSelectedService(serviceName);
            
            // Обновляем название сервиса на втором шаге
            updateServiceUI(serviceName);
            
            console.log('Выбран сервис:', service, serviceName);
            
            // Автоматически заполняем название интеграции
            autoFillIntegrationName(serviceName);
        });
    });
}

/**
 * Показать выбранный сервис в информационном блоке
 */
function showSelectedService(serviceName) {
    const alert = document.getElementById('selectedServiceAlert');
    const nameSpan = document.getElementById('selectedServiceName');
    
    if (alert && nameSpan) {
        nameSpan.textContent = serviceName;
        alert.style.display = 'block';
    }
}

/**
 * Обновление UI при выборе сервиса
 */
function updateServiceUI(serviceName) {
    const currentServiceName = document.getElementById('currentServiceName');
    const currentServiceInfo = document.getElementById('currentServiceInfo');
    
    if (currentServiceName) {
        currentServiceName.textContent = serviceName;
    }
    
    if (currentServiceInfo) {
        currentServiceInfo.innerHTML = `
            <span class="badge bg-primary">${serviceName}</span>
        `;
    }
}

/**
 * Автозаполнение названия интеграции
 */
function autoFillIntegrationName(serviceName) {
    const integrationNameField = document.getElementById('integration_name');
    if (integrationNameField && !integrationNameField.value) {
        integrationNameField.value = `Интеграция с ${serviceName}`;
    }
}

/**
 * Инициализация выбора внутреннего модуля
 */
function initModuleSelection() {
    console.log('Инициализация выбора внутреннего модуля');
    
    const moduleCards = document.querySelectorAll('.service-card[data-module]');
    
    moduleCards.forEach(card => {
        card.addEventListener('click', function() {
            // Убираем выделение у всех карточек
            moduleCards.forEach(c => c.classList.remove('selected'));
            
            // Выделяем выбранную
            this.classList.add('selected');
            
            // Получаем данные
            const module = this.getAttribute('data-module');
            const moduleName = this.getAttribute('data-module-name');
            
            // Сохраняем в глобальные данные
            window.integrationData.selectedModule = module;
            window.integrationData.selectedModuleName = moduleName;
            
            // Обновляем информацию о модуле на четвертом шаге
            updateModuleUI(moduleName);
            
            // Показываем настройки модуля
            showModuleSettings();
            
            console.log('Выбран модуль:', module, moduleName);
        });
    });
}

/**
 * Обновление UI при выборе модуля
 */
function updateModuleUI(moduleName) {
    const currentModuleInfo = document.getElementById('currentModuleInfo');
    if (currentModuleInfo) {
        currentModuleInfo.innerHTML = `
            <span class="badge bg-success">${moduleName}</span>
        `;
    }
}

/**
 * Показать настройки модуля
 */
function showModuleSettings() {
    const moduleSettings = document.getElementById('moduleSettings');
    if (moduleSettings) {
        moduleSettings.style.display = 'block';
    }
}

/**
 * Инициализация навигации по шагам
 */
function initNavigation() {
    console.log('Инициализация навигации по шагам');
    
    // Назначаем обработчики для кнопок "Далее"
    document.getElementById('nextToStep2')?.addEventListener('click', function(e) {
        e.preventDefault();
        if (validateStep1()) {
            goToStep(2);
        }
    });
    
    document.getElementById('nextToStep3')?.addEventListener('click', function(e) {
        e.preventDefault();
        if (validateStep2()) {
            goToStep(3);
        }
    });
    
    document.getElementById('nextToStep4')?.addEventListener('click', function(e) {
        e.preventDefault();
        if (validateStep3()) {
            goToStep(4);
        }
    });
    
    // Назначаем обработчики для кнопок "Назад"
    document.getElementById('backToStep1')?.addEventListener('click', function(e) {
        e.preventDefault();
        goToStep(1);
    });
    
    document.getElementById('backToStep2')?.addEventListener('click', function(e) {
        e.preventDefault();
        goToStep(2);
    });
    
    document.getElementById('backToStep3')?.addEventListener('click', function(e) {
        e.preventDefault();
        goToStep(3);
    });
    
    // Кнопка изменения выбора сервиса
    document.getElementById('changeServiceBtn')?.addEventListener('click', function(e) {
        e.preventDefault();
        resetServiceSelection();
    });
    
    console.log('Навигация инициализирована');
}

/**
 * Сброс выбора сервиса
 */
function resetServiceSelection() {
    document.querySelectorAll('.service-card[data-service]').forEach(c => {
        c.classList.remove('selected');
    });
    
    const alert = document.getElementById('selectedServiceAlert');
    if (alert) {
        alert.style.display = 'none';
    }
    
    window.integrationData.selectedService = null;
    window.integrationData.selectedServiceName = null;
    window.integrationData.selectedServiceData = {};
    
    // Очищаем скрытые поля
    const hiddenServiceField = document.getElementById('selected_service');
    const hiddenServiceNameField = document.getElementById('selected_service_name');
    if (hiddenServiceField) hiddenServiceField.value = '';
    if (hiddenServiceNameField) hiddenServiceNameField.value = '';
}

/**
 * Инициализация обработчиков событий
 */
function initEventHandlers() {
    console.log('Инициализация обработчиков событий');
    
    // Сохранение черновика
    document.getElementById('saveDraftBtn')?.addEventListener('click', function() {
        saveAsDraft();
    });
    
    // Отмена создания
    document.getElementById('cancelBtn')?.addEventListener('click', function() {
        cancelIntegration();
    });
    
    // Тестирование интеграции
    document.getElementById('testIntegrationBtn')?.addEventListener('click', function() {
        showTestModal();
    });
    
    // Запуск теста
    document.getElementById('runTestBtn')?.addEventListener('click', function() {
        runIntegrationTest();
    });
    
    // Обработка отправки формы
    document.getElementById('integrationForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        createIntegration();
    });
    
    // Изменение типа сервиса (для динамических полей)
    document.getElementById('serviceType')?.addEventListener('change', function() {
        updateServiceFields(this.value);
    });
    
    // Изменение выбранного модуля
    document.getElementById('internalModule')?.addEventListener('change', function() {
        updateModuleSettings(this.value);
    });
    
    console.log('Обработчики событий назначены');
}

/**
 * Инициализация динамических полей
 */
function initDynamicFields() {
    console.log('Инициализация динамических полей');
    
    // Скрываем все специфичные поля сервисов при загрузке
    document.querySelectorAll('.service-fields').forEach(field => {
        field.style.display = 'none';
    });
    
    // Показываем поля для выбранного типа сервиса (если есть)
    const selectedService = document.getElementById('serviceType')?.value;
    if (selectedService) {
        updateServiceFields(selectedService);
    }
}

/**
 * Обновление полей в зависимости от типа сервиса
 */
function updateServiceFields(serviceType) {
    console.log('Обновление полей для сервиса:', serviceType);
    
    // Скрываем все поля
    document.querySelectorAll('.service-fields').forEach(field => {
        field.style.display = 'none';
    });
    
    // Показываем соответствующие поля
    const targetFields = document.querySelector(`.service-fields[data-service-type="${serviceType}"]`);
    if (targetFields) {
        targetFields.style.display = 'block';
    }
}

/**
 * Инициализация предпросмотра модуля
 */
function initModulePreview() {
    const moduleSelect = document.getElementById('internalModule');
    
    if (moduleSelect) {
        moduleSelect.addEventListener('change', function() {
            const moduleName = this.value;
            if (moduleName) {
                showModulePreview(moduleName);
            } else {
                hideModulePreview();
            }
        });
    }
}

/**
 * Обновление настроек модуля
 */
function updateModuleSettings(moduleName) {
    console.log('Обновление настроек модуля:', moduleName);
    
    const moduleSettings = document.getElementById('moduleSettings');
    
    if (moduleName && moduleSettings) {
        moduleSettings.style.display = 'block';
        showModulePreview(moduleName);
    } else if (moduleSettings) {
        moduleSettings.style.display = 'none';
        hideModulePreview();
    }
}

/**
 * Показать предпросмотр полей модуля
 */
function showModulePreview(moduleName) {
    console.log('Показ предпросмотра модуля:', moduleName);
    
    const previewDiv = document.getElementById('moduleFieldsPreview');
    const previewContent = document.getElementById('previewContent');
    
    if (!previewDiv || !previewContent) return;
    
    // Примеры полей для разных модулей
    const demoData = {
        'news': [
            { name: 'id', type: 'integer', required: true },
            { name: 'title', type: 'string', required: true },
            { name: 'content', type: 'text', required: true },
            { name: 'slug', type: 'string', required: false },
            { name: 'status', type: 'enum', required: true },
            { name: 'published_at', type: 'datetime', required: false }
        ],
        'articles': [
            { name: 'id', type: 'integer', required: true },
            { name: 'title', type: 'string', required: true },
            { name: 'body', type: 'text', required: true },
            { name: 'author_id', type: 'integer', required: true },
            { name: 'category_id', type: 'integer', required: false },
            { name: 'is_published', type: 'boolean', required: true }
        ],
        'products': [
            { name: 'id', type: 'integer', required: true },
            { name: 'name', type: 'string', required: true },
            { name: 'description', type: 'text', required: false },
            { name: 'price', type: 'decimal', required: true },
            { name: 'sku', type: 'string', required: true },
            { name: 'stock', type: 'integer', required: true }
        ]
    };
    
    let html = '';
    let fields = [];
    
    // Получаем поля в зависимости от модуля
    if (demoData[moduleName]) {
        fields = demoData[moduleName];
    } else {
        // Заглушка по умолчанию
        fields = [
            { name: 'id', type: 'integer', required: true },
            { name: 'name', type: 'string', required: true },
            { name: 'description', type: 'text', required: false },
            { name: 'created_at', type: 'datetime', required: true }
        ];
    }
    
    // Генерируем HTML для предпросмотра
    fields.forEach(field => {
        html += `
            <div class="setting-item">
                <span class="setting-label">
                    ${field.name}
                    ${field.required ? '<span class="text-danger">*</span>' : ''}
                </span>
                <span class="setting-value">
                    <span class="badge bg-light text-dark">${field.type}</span>
                </span>
            </div>
        `;
    });
    
    previewContent.innerHTML = html;
    previewDiv.style.display = 'block';
}

/**
 * Скрыть предпросмотр модуля
 */
function hideModulePreview() {
    const previewDiv = document.getElementById('moduleFieldsPreview');
    if (previewDiv) {
        previewDiv.style.display = 'none';
    }
}

/**
 * Переход к указанному шагу
 */
function goToStep(stepNumber) {
    console.log('Переход к шагу:', stepNumber);
    
    // Обновляем индикатор шагов
    document.querySelectorAll('.step').forEach(step => {
        step.classList.remove('active', 'completed');
    });
    
    // Устанавливаем состояние для всех шагов
    for (let i = 1; i <= 4; i++) {
        const step = document.querySelector(`.step[data-step="${i}"]`);
        if (step) {
            if (i < stepNumber) {
                step.classList.add('completed');
            } else if (i == stepNumber) {
                step.classList.add('active');
            }
        }
    }
    
    // Показываем соответствующий контент
    document.querySelectorAll('.step-content').forEach(content => {
        content.classList.remove('active');
    });
    
    const activeContent = document.getElementById(`step${stepNumber}`);
    if (activeContent) {
        activeContent.classList.add('active');
        
        // При переходе на шаг 2 показываем настройки выбранного сервиса
        if (stepNumber === 2 && window.integrationData.selectedService) {
            showServiceSettings(window.integrationData.selectedService);
        }
        
        // При переходе на шаг 4 генерируем поля для сопоставления
        if (stepNumber === 4 && window.integrationData.selectedService && window.integrationData.selectedModule) {
            generateFieldMapping(window.integrationData.selectedServiceName, window.integrationData.selectedModuleName);
        }
    }
    
    // Прокрутка к верху
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * Показать настройки выбранного сервиса
 */
function showServiceSettings(service) {
    // Скрываем все настройки
    document.querySelectorAll('.service-settings').forEach(settings => {
        settings.style.display = 'none';
    });
    
    // Показываем настройки для выбранного сервиса
    const serviceSettings = document.getElementById(`settings-${service}`);
    if (serviceSettings) {
        serviceSettings.style.display = 'block';
    }
}

/**
 * Генерация полей для сопоставления
 */
function generateFieldMapping(serviceName, moduleName) {
    const container = document.getElementById('fieldMappingRows');
    if (!container) return;
    
    // Примеры полей для разных сервисов и модулей
    const fieldExamples = {
        '1C:Предприятие': {
            'products': [
                { source: 'Наименование', sourceType: 'string', target: 'name' },
                { source: 'Артикул', sourceType: 'string', target: 'sku' },
                { source: 'Цена', sourceType: 'decimal', target: 'price' },
                { source: 'Количество', sourceType: 'integer', target: 'stock' },
                { source: 'Описание', sourceType: 'text', target: 'description' }
            ],
            'orders': [
                { source: 'Номер', sourceType: 'string', target: 'order_number' },
                { source: 'Дата', sourceType: 'date', target: 'created_at' },
                { source: 'Контрагент', sourceType: 'string', target: 'customer_name' },
                { source: 'Сумма', sourceType: 'decimal', target: 'total_amount' }
            ]
        },
        'Яндекс.Маркет': {
            'products': [
                { source: 'offerId', sourceType: 'string', target: 'sku' },
                { source: 'name', sourceType: 'string', target: 'name' },
                { source: 'price', sourceType: 'decimal', target: 'price' },
                { source: 'category', sourceType: 'string', target: 'category' }
            ],
            'orders': [
                { source: 'id', sourceType: 'string', target: 'order_number' },
                { source: 'creationDate', sourceType: 'date', target: 'created_at' },
                { source: 'items', sourceType: 'array', target: 'items' }
            ]
        }
    };
    
    let html = '';
    let fields = [];
    
    // Получаем поля в зависимости от сервиса и модуля
    if (fieldExamples[serviceName] && fieldExamples[serviceName][moduleName]) {
        fields = fieldExamples[serviceName][moduleName];
    } else {
        // Заглушка по умолчанию
        fields = [
            { source: 'Поле 1', sourceType: 'string', target: 'field_a' },
            { source: 'Поле 2', sourceType: 'string', target: 'field_b' },
            { source: 'Поле 3', sourceType: 'string', target: 'field_c' }
        ];
    }
    
    // Очищаем предыдущее сопоставление
    window.integrationData.fieldMapping = [];
    
    // Генерируем строки для сопоставления
    fields.forEach((field, index) => {
        html += `
            <div class="field-mapping-row">
                <div class="field-source">
                    <div class="d-flex align-items-center">
                        <div>
                            <strong>${field.source}</strong>
                            <div class="small text-muted">${field.sourceType}</div>
                        </div>
                    </div>
                </div>
                <div class="field-direction">
                    <i class="fas fa-long-arrow-alt-right"></i>
                </div>
                <div class="field-target">
                    <select class="form-select field-mapping-select" data-field-index="${index}">
                        <option value="">-- Не сопоставлять --</option>
                        <option value="${field.target}" selected>${field.target}</option>
                        <option value="name">Название</option>
                        <option value="price">Цена</option>
                        <option value="description">Описание</option>
                        <option value="quantity">Количество</option>
                        <option value="sku">Артикул</option>
                    </select>
                </div>
            </div>
        `;
        
        // Инициализируем маппинг
        window.integrationData.fieldMapping[index] = {
            source: field.source,
            target: field.target
        };
    });
    
    container.innerHTML = html;
    
    // Добавляем обработчики для селектов
    document.querySelectorAll('.field-mapping-select').forEach(select => {
        select.addEventListener('change', function() {
            const fieldIndex = parseInt(this.getAttribute('data-field-index'));
            window.integrationData.fieldMapping[fieldIndex] = {
                source: fields[fieldIndex].source,
                target: this.value
            };
            console.log('Сопоставление обновлено:', window.integrationData.fieldMapping);
        });
    });
}

/**
 * Валидация шага 1 (выбор сервиса)
 */
function validateStep1() {
    console.log('Валидация шага 1');
    
    if (!window.integrationData.selectedService) {
        showToast('Пожалуйста, выберите внешний сервис для интеграции', 'warning');
        return false;
    }
    return true;
}

/**
 * Валидация шага 2 (настройки подключения)
 */
function validateStep2() {
    console.log('Валидация шага 2');
    
    const service = window.integrationData.selectedService;
    let isValid = true;
    
    switch(service) {
        case '1c':
            if (!document.getElementById('1c_url')?.value) {
                showToast('Пожалуйста, заполните все обязательные поля для подключения к 1С', 'warning');
                isValid = false;
            }
            break;
        case 'telegram':
            if (!document.getElementById('telegram_token')?.value) {
                showToast('Пожалуйста, укажите токен Telegram бота', 'warning');
                isValid = false;
            }
            break;
        case 'yandex_market':
            if (!document.getElementById('yandex_client_id')?.value ||
                !document.getElementById('yandex_token')?.value ||
                !document.getElementById('yandex_campaign_id')?.value) {
                showToast('Пожалуйста, заполните все обязательные поля для подключения к Яндекс.Маркет', 'warning');
                isValid = false;
            }
            break;
    }
    
    // Проверяем общее название интеграции
    const integrationName = document.getElementById('integration_name')?.value;
    if (!integrationName || integrationName.trim() === '') {
        showToast('Пожалуйста, укажите название интеграции', 'warning');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Валидация шага 3 (выбор модуля)
 */
function validateStep3() {
    console.log('Валидация шага 3');
    
    if (!window.integrationData.selectedModule) {
        showToast('Пожалуйста, выберите внутренний модуль для интеграции', 'warning');
        return false;
    }
    
    const syncDirection = document.querySelector('input[name="sync_direction"]:checked');
    if (!syncDirection) {
        showToast('Пожалуйста, выберите направление синхронизации', 'warning');
        return false;
    }
    
    return true;
}

/**
 * Сохранение черновика
 */
function saveAsDraft() {
    console.log('Сохранение черновика интеграции');
    
    // Собираем данные
    const draftData = {
        integrationData: window.integrationData,
        formData: collectFormData(),
        timestamp: new Date().toISOString()
    };
    
    // Сохраняем в localStorage для демонстрации
    localStorage.setItem('integrationDraft', JSON.stringify(draftData));
    
    showToast('Черновик интеграции сохранен', 'success');
}

/**
 * Сбор данных формы
 */
function collectFormData() {
    const formData = {};
    
    // Основные поля
    const integrationName = document.getElementById('integration_name');
    const integrationDescription = document.getElementById('integration_description');
    
    if (integrationName) formData.name = integrationName.value;
    if (integrationDescription) formData.description = integrationDescription.value;
    
    // Настройки
    const isActive = document.getElementById('is_active');
    const logRequests = document.getElementById('log_requests');
    const autoRetry = document.getElementById('auto_retry');
    
    if (isActive) formData.is_active = isActive.checked;
    if (logRequests) formData.log_requests = logRequests.checked;
    if (autoRetry) formData.auto_retry = autoRetry.checked;
    
    // Направление синхронизации
    const syncDirection = document.querySelector('input[name="sync_direction"]:checked');
    if (syncDirection) formData.sync_direction = syncDirection.value;
    
    return formData;
}

/**
 * Отмена создания интеграции
 */
function cancelIntegration() {
    if (confirm('Вы уверены, что хотите отменить создание интеграции? Все несохраненные данные будут потеряны.')) {
        showToast('Создание интеграции отменено', 'info');
        
        // Перенаправляем на список интеграций
        setTimeout(() => {
            window.location.href = 'integration_index_static.html';
        }, 1000);
    }
}

/**
 * Показать модальное окно тестирования
 */
function showTestModal() {
    console.log('Показ модального окна тестирования');
    
    const testModalElement = document.getElementById('testModal');
    if (!testModalElement) {
        console.error('Элемент модального окна не найден');
        return;
    }
    
    const testModal = new bootstrap.Modal(testModalElement);
    
    // Очищаем предыдущие результаты
    const testResults = document.getElementById('testResults');
    if (testResults) {
        testResults.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Загрузка...</span>
                </div>
                <p>Готово к тестированию интеграции</p>
            </div>
        `;
    }
    
    testModal.show();
}

/**
 * Запуск теста интеграции
 */
function runIntegrationTest() {
    console.log('Запуск теста интеграции');
    
    const testResults = document.getElementById('testResults');
    if (!testResults) return;
    
    testResults.innerHTML = `
        <div class="text-center py-3">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Тестирование...</span>
            </div>
            <p>Выполняется тестирование интеграции...</p>
        </div>
    `;
    
    // Имитация тестирования
    setTimeout(() => {
        testResults.innerHTML = `
            <div class="alert alert-success">
                <h6><i class="fas fa-check-circle me-2"></i>Тестирование завершено успешно!</h6>
                <hr>
                <div class="small">
                    <p><strong>Проверено:</strong></p>
                    <ul class="mb-0">
                        <li>Подключение к внешнему сервису: <span class="text-success">Успешно</span></li>
                        <li>Доступ к модулю: <span class="text-success">Успешно</span></li>
                        <li>Сопоставление полей: <span class="text-success">Успешно</span></li>
                        <li>Тестовый обмен данными: <span class="text-success">Успешно</span></li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-3">
                <h6>Отправленные данные:</h6>
                <pre class="bg-light p-3 rounded small">${JSON.stringify({
                    test: true,
                    service: window.integrationData.selectedService,
                    module: window.integrationData.selectedModule,
                    syncDirection: document.querySelector('input[name="sync_direction"]:checked')?.value
                }, null, 2)}</pre>
            </div>
            
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle me-2"></i>
                <small>Интеграция готова к использованию. Вы можете создать её или внести изменения.</small>
            </div>
        `;
    }, 2000);
}

/**
 * Создание интеграции
 */
function createIntegration() {
    console.log('Создание интеграции');
    
    // Собираем все данные
    const integrationData = {
        // Основные данные
        name: document.getElementById('integration_name')?.value || '',
        description: document.getElementById('integration_description')?.value || '',
        
        // Данные сервиса
        service: window.integrationData.selectedService,
        service_name: window.integrationData.selectedServiceName,
        service_data: window.integrationData.selectedServiceData,
        
        // Данные модуля
        module: window.integrationData.selectedModule,
        module_name: window.integrationData.selectedModuleName,
        
        // Настройки
        sync_direction: document.querySelector('input[name="sync_direction"]:checked')?.value || 'import',
        field_mapping: window.integrationData.fieldMapping,
        
        // Флаги
        is_active: document.getElementById('is_active')?.checked || true,
        log_requests: document.getElementById('log_requests')?.checked || false,
        auto_retry: document.getElementById('auto_retry')?.checked || true,
        
        // Сервис-специфичные настройки
        settings: getServiceSettings(),
        
        // Метаданные
        created_at: new Date().toISOString()
    };
    
    console.log('Создаваемая интеграция:', integrationData);
    
    // Имитация создания
    showToast('Создание интеграции...', 'info');
    
    // В реальном приложении здесь будет AJAX-запрос
    setTimeout(() => {
        showToast('Интеграция успешно создана!', 'success');
        
        // Перенаправляем на страницу списка интеграций
        setTimeout(() => {
            window.location.href = 'integration_index_static.html';
        }, 1000);
    }, 1500);
}

/**
 * Получение настроек сервиса
 */
function getServiceSettings() {
    const service = window.integrationData.selectedService;
    const settings = {};
    
    if (!service) return settings;
    
    switch(service) {
        case '1c':
            const url = document.getElementById('1c_url');s
            const syncType = document.getElementById('1c_sync_type');
            const syncInterval = document.getElementById('1c_sync_interval');
            
            if (url) settings.url = url.value;
            if (login) settings.login = login.value;
            if (password) settings.password = password.value;
            if (syncType) settings.sync_type = syncType.value;
            if (syncInterval) settings.sync_interval = syncInterval.value;
            break;
            
        case 'telegram':
            const token = document.getElementById('telegram_token');
            const chatId = document.getElementById('telegram_chat_id');
            
            if (token) settings.token = token.value;
            if (chatId) settings.chat_id = chatId.value;
            break;
            
        case 'yandex_market':
            const clientId = document.getElementById('yandex_client_id');
            const yandexToken = document.getElementById('yandex_token');
            const campaignId = document.getElementById('yandex_campaign_id');
            const yandexSyncType = document.getElementById('yandex_sync_type');
            
            if (clientId) settings.client_id = clientId.value;
            if (yandexToken) settings.token = yandexToken.value;
            if (campaignId) settings.campaign_id = campaignId.value;
            if (yandexSyncType) settings.sync_type = yandexSyncType.value;
            break;
    }
    
    return settings;
}

/**
 * Показать toast-уведомление
 */
function showToast(message, type = 'info') {
    console.log(`Toast: ${type} - ${message}`);
    
    const typeClasses = {
        'info': 'text-bg-primary',
        'success': 'text-bg-success',
        'warning': 'text-bg-warning',
        'error': 'text-bg-danger'
    };
    
    let toastContainer = document.getElementById('toast-container');
    
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    const toastId = 'toast-' + Date.now();
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `toast align-items-center ${typeClasses[type] || typeClasses.info} border-0`;
    toast.setAttribute('role', 'alert');
    
    const iconClass = type === 'success' ? 'fa-check-circle' : 
                     type === 'error' ? 'fa-exclamation-circle' : 
                     type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas ${iconClass} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast, {
        autohide: true,
        delay: 3000
    });
    
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', function () {
        toast.remove();
    });
}

/**
 * Экспорт функций для отладки
 */
window.goToStep = goToStep;
window.validateStep1 = validateStep1;
window.validateStep2 = validateStep2;
window.validateStep3 = validateStep3;

console.log('Страница создания интеграции полностью инициализирована');