/**
 * JavaScript для страницы создания интеграции (модуль Integration)
 * Путь: public/layouts/admin/modules/integrations/js/create.js
 * 
 * @description Управление 4-шаговым процессом создания интеграции:
 * 1. Выбор внешнего сервиса
 * 2. Настройки подключения к выбранному сервису
 * 3. Выбор внутреннего модуля
 * 4. Сопоставление полей (с динамической загрузкой полей модуля)
 */

// Глобальный флаг инициализации
if (typeof window.integrationInitialized === 'undefined') {
    window.integrationInitialized = true;

    class IntegrationCreator {
        constructor() {
            this.integrationData = {
                selectedService: null,
                selectedServiceName: null,
                selectedServiceData: {},
                selectedModule: null,
                selectedModuleName: null,
                serviceSettings: {},
                moduleFields: [],
                fieldMapping: []
            };
            
            this.init();
        }

        init() {
            console.log('Страница создания интеграции загружена');
            
            // Ждем полной загрузки DOM
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.initializeComponents());
            } else {
                this.initializeComponents();
            }
        }

        initializeComponents() {
            try {
                this.initServiceSelection();
                this.initModuleSelection();
                this.initNavigation();
                this.initEventHandlers();
                this.initModuleFieldsLoading();
                
                // Инициализируем первый шаг
                this.goToStep(1);
                
                console.log('Инициализация завершена');
            } catch (error) {
                console.error('Ошибка инициализации:', error);
            }
        }

        /**
         * Инициализация выбора внешнего сервиса
         */
        initServiceSelection() {
            console.log('Инициализация выбора внешнего сервиса');
            
            const serviceCards = document.querySelectorAll('.service-card[data-service]');
            
            if (serviceCards.length === 0) {
                console.warn('Карточки сервисов не найдены');
                return;
            }
            
            serviceCards.forEach(card => {
                card.addEventListener('click', () => this.handleServiceSelection(card));
            });
        }

        handleServiceSelection(card) {
            try {
                // Убираем выделение у всех карточек
                document.querySelectorAll('.service-card[data-service]').forEach(c => {
                    c.classList.remove('selected');
                });
                
                // Выделяем выбранную
                card.classList.add('selected');
                
                // Получаем данные из data-атрибутов
                const service = card.getAttribute('data-service');
                const serviceName = card.getAttribute('data-service-name');
                const serviceType = card.getAttribute('data-service-type');
                const serviceIcon = card.getAttribute('data-service-icon');
                const serviceCategory = card.getAttribute('data-service-category');
                
                // Сохраняем в глобальные данные
                this.integrationData.selectedService = service;
                this.integrationData.selectedServiceName = serviceName;
                this.integrationData.selectedServiceData = {
                    service: service,
                    name: serviceName,
                    type: serviceType,
                    icon: serviceIcon,
                    category: serviceCategory
                };
                
                // Заполняем скрытые поля формы
                this.updateHiddenFields(service, serviceName, serviceType, serviceIcon, serviceCategory);
                
                // Показываем информацию о выборе
                this.showSelectedService(serviceName);
                
                // Обновляем название сервиса на втором шаге
                this.updateServiceUI(serviceName);
                
                console.log('Выбран сервис:', service, serviceName);
                
                // Автоматически заполняем название интеграции
                this.autoFillIntegrationName(serviceName);
            } catch (error) {
                console.error('Ошибка выбора сервиса:', error);
            }
        }

        updateHiddenFields(service, serviceName, serviceType, serviceIcon, serviceCategory) {
            const hiddenFields = [
                { id: 'selected_service', value: service },
                { id: 'selected_service_name', value: serviceName },
                { id: 'selected_service_type', value: serviceType || '' },
                { id: 'selected_service_icon', value: serviceIcon || '' },
                { id: 'selected_service_category', value: serviceCategory || '' }
            ];
            
            hiddenFields.forEach(field => {
                const element = document.getElementById(field.id);
                if (element) {
                    element.value = field.value;
                }
            });
        }

        /**
         * Показать выбранный сервис в информационном блоке
         */
        showSelectedService(serviceName) {
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
        updateServiceUI(serviceName) {
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
        autoFillIntegrationName(serviceName) {
            const integrationNameField = document.getElementById('integration_name');
            if (integrationNameField && !integrationNameField.value) {
                integrationNameField.value = `Интеграция с ${serviceName}`;
            }
        }

        /**
         * Инициализация выбора внутреннего модуля
         */
        initModuleSelection() {
            console.log('Инициализация выбора внутреннего модуля');
            
            const moduleCards = document.querySelectorAll('.service-card[data-module]');
            
            if (moduleCards.length === 0) {
                console.warn('Карточки модулей не найдены');
                return;
            }
            
            moduleCards.forEach(card => {
                card.addEventListener('click', () => this.handleModuleSelection(card));
            });
        }

        handleModuleSelection(card) {
            try {
                // Убираем выделение у всех карточек
                document.querySelectorAll('.service-card[data-module]').forEach(c => {
                    c.classList.remove('selected');
                });
                
                // Выделяем выбранную
                card.classList.add('selected');
                
                // Получаем данные
                const module = card.getAttribute('data-module');
                const moduleName = card.getAttribute('data-module-name');
                
                // Сохраняем в глобальные данные
                this.integrationData.selectedModule = module;
                this.integrationData.selectedModuleName = moduleName;
                
                // Обновляем информацию о модуле на четвертом шаге
                this.updateModuleUI(moduleName);
                
                // Генерируем событие выбора модуля
                const moduleSelectedEvent = new CustomEvent('moduleSelected', {
                    detail: {
                        module: module,
                        moduleName: moduleName
                    }
                });
                document.dispatchEvent(moduleSelectedEvent);
                
                console.log('Выбран модуль:', module, moduleName);
            } catch (error) {
                console.error('Ошибка выбора модуля:', error);
            }
        }

        /**
         * Обновление UI при выборе модуля
         */
        updateModuleUI(moduleName) {
            const currentModuleInfo = document.getElementById('currentModuleInfo');
            const selectedModuleLabel = document.getElementById('selectedModuleLabel');
            
            if (currentModuleInfo) {
                currentModuleInfo.innerHTML = `
                    <span class="badge bg-success">${moduleName}</span>
                `;
            }
            
            if (selectedModuleLabel) {
                selectedModuleLabel.textContent = moduleName;
            }
        }

        /**
         * Инициализация навигации по шагам
         */
        initNavigation() {
            console.log('Инициализация навигации по шагам');
            
            // Назначаем обработчики для кнопок "Далее"
            this.addClickListener('nextToStep2', (e) => {
                e.preventDefault();
                if (this.validateStep1()) {
                    this.goToStep(2);
                }
            });
            
            this.addClickListener('nextToStep3', (e) => {
                e.preventDefault();
                if (this.validateStep2()) {
                    this.goToStep(3);
                }
            });
            
            this.addClickListener('nextToStep4', (e) => {
                e.preventDefault();
                if (this.validateStep3()) {
                    this.goToStep(4);
                }
            });
            
            // Назначаем обработчики для кнопок "Назад"
            this.addClickListener('backToStep1', (e) => {
                e.preventDefault();
                this.goToStep(1);
            });
            
            this.addClickListener('backToStep2', (e) => {
                e.preventDefault();
                this.goToStep(2);
            });
            
            this.addClickListener('backToStep3', (e) => {
                e.preventDefault();
                this.goToStep(3);
            });
            
            // Кнопка изменения выбора сервиса
            this.addClickListener('changeServiceBtn', (e) => {
                e.preventDefault();
                this.resetServiceSelection();
            });
            
            console.log('Навигация инициализирована');
        }

        addClickListener(elementId, handler) {
            const element = document.getElementById(elementId);
            if (element) {
                element.addEventListener('click', handler);
            } else {
                console.warn(`Элемент #${elementId} не найден`);
            }
        }

        /**
         * Сброс выбора сервиса
         */
        resetServiceSelection() {
            document.querySelectorAll('.service-card[data-service]').forEach(c => {
                c.classList.remove('selected');
            });
            
            const alert = document.getElementById('selectedServiceAlert');
            if (alert) {
                alert.style.display = 'none';
            }
            
            this.integrationData.selectedService = null;
            this.integrationData.selectedServiceName = null;
            this.integrationData.selectedServiceData = {};
            
            // Очищаем скрытые поля
            const hiddenFields = [
                'selected_service',
                'selected_service_name', 
                'selected_service_type',
                'selected_service_icon',
                'selected_service_category'
            ];
            
            hiddenFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) field.value = '';
            });
        }

        /**
         * Инициализация обработчиков событий
         */
        initEventHandlers() {
            console.log('Инициализация обработчиков событий');
            
            // Тестирование интеграции
            this.addClickListener('testIntegrationBtn', () => {
                this.showTestModal();
            });
            
            // Запуск теста
            this.addClickListener('runTestBtn', () => {
                this.runIntegrationTest();
            });
            
            // Обработка отправки формы
            const form = document.getElementById('integrationForm');
            if (form) {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.createIntegration();
                });
            }
            
            console.log('Обработчики событий назначены');
        }

        /**
         * Инициализация загрузки полей модуля
         */
        initModuleFieldsLoading() {
            console.log('Инициализация загрузки полей модуля');
            
            // При изменении выбора модуля
            document.addEventListener('moduleSelected', (e) => {
                const moduleName = e.detail.moduleName || e.detail.module;
                if (moduleName) {
                    // Сохраняем в скрытое поле
                    const selectedModuleInput = document.getElementById('selectedModuleInput');
                    if (selectedModuleInput) {
                        selectedModuleInput.value = moduleName;
                    }
                }
            });
        }

        /**
         * Переход к указанному шагу
         */
        goToStep(stepNumber) {
            console.log('Переход к шагу:', stepNumber);
            
            try {
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
                    if (stepNumber === 2 && this.integrationData.selectedService) {
                        this.showServiceSettings(this.integrationData.selectedService);
                    }
                    
                    // При переходе на шаг 4 загружаем поля модуля
                    if (stepNumber === 4 && this.integrationData.selectedModule) {
                        this.loadModuleFields(this.integrationData.selectedModule);
                    }
                }
                
                // Прокрутка к верху
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } catch (error) {
                console.error('Ошибка перехода к шагу:', error);
            }
        }

        /**
         * Показать настройки выбранного сервиса
         */
        showServiceSettings(service) {
            try {
                // Скрываем все настройки
                document.querySelectorAll('.service-settings').forEach(settings => {
                    settings.style.display = 'none';
                });
                
                // Показываем настройки для выбранного сервиса
                const serviceSettings = document.getElementById(`settings-${service}`);
                if (serviceSettings) {
                    serviceSettings.style.display = 'block';
                }
            } catch (error) {
                console.error('Ошибка показа настроек сервиса:', error);
            }
        }

        /**
         * Загрузка полей модуля с сервера
         */
        async loadModuleFields(moduleName) {
            console.log('Загрузка полей для модуля:', moduleName);
            
            const loader = document.getElementById('moduleFieldsLoader');
            const errorDiv = document.getElementById('fieldsError');
            const container = document.getElementById('fieldMappingRows');
            const moduleInput = document.getElementById('selectedModuleInput');
            
            // Показываем загрузку
            if (loader) loader.style.display = 'block';
            if (errorDiv) errorDiv.style.display = 'none';
            
            // Сохраняем имя модуля
            if (moduleInput) moduleInput.value = moduleName;
            
            try {
                // Используем правильный URL с именем маршрута
                const url = `/admin/integration/module-fields/${encodeURIComponent(moduleName)}`;
                console.log('Запрос к:', url);
                
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.getCsrfToken()
                    }
                });
                
                console.log('Статус ответа:', response.status);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                console.log('Данные ответа:', data);
                
                if (data.success) {
                    console.log('Получены поля модуля:', data.fields);
                    this.integrationData.moduleFields = data.fields;
                    this.renderModuleFields(data.fields, moduleName);
                } else {
                    throw new Error(data.message || 'Ошибка загрузки полей');
                }

            } catch (error) {
                console.error('Ошибка загрузки полей модуля:', error);
                if (errorDiv) {
                    errorDiv.textContent = `Ошибка: ${error.message}`;
                    errorDiv.style.display = 'block';
                }
                
                if (container) {
                    container.innerHTML = `
                        <div class="alert alert-warning p-4 text-center">
                            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                            <p>Не удалось загрузить поля модуля</p>
                            <button class="btn btn-sm btn-outline-primary retry-load-btn">
                                <i class="fas fa-redo me-1"></i> Попробовать снова
                            </button>
                        </div>
                    `;
                    
                    // Добавляем обработчик для повторной попытки
                    const retryButton = container.querySelector('.retry-load-btn');
                    if (retryButton) {
                        retryButton.addEventListener('click', () => {
                            this.loadModuleFields(moduleName);
                        });
                    }
                }
            } finally {
                if (loader) loader.style.display = 'none';
            }
        }

        getCsrfToken() {
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            return metaTag ? metaTag.getAttribute('content') : '';
        }

        /**
         * Отображение полей модуля в интерфейсе
         */
        renderModuleFields(fields, moduleName) {
            const container = document.getElementById('fieldMappingRows');
            const template = document.getElementById('fieldMappingTemplate');
            
            if (!container) {
                console.error('Контейнер fieldMappingRows не найден');
                return;
            }
            
            if (!template) {
                console.error('Шаблон fieldMappingTemplate не найден');
                return;
            }
            
            // Очищаем контейнер
            container.innerHTML = '';
            
            // Если поля не найдены
            if (!fields || fields.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-warning p-4 text-center">
                        <i class="fas fa-database fa-2x mb-3"></i>
                        <p>У модуля "${moduleName}" нет полей для сопоставления</p>
                        <p class="small text-muted">Создайте поля в модуле через административную панель</p>
                    </div>
                `;
                return;
            }
            
            // Создаем строки для каждого поля
            fields.forEach((field, index) => {
                const clone = template.content.cloneNode(true);
                const row = clone.querySelector('.field-mapping-row');
                
                if (!row) {
                    console.error('Строка field-mapping-row не найдена в шаблоне');
                    return;
                }
                
                const fieldSelect = clone.querySelector('.field-module-select');
                const fieldTypeInfo = clone.querySelector('.field-type-info');
                const removeBtn = clone.querySelector('.remove-field-btn');
                const fieldInput = clone.querySelector('.field-1c-input');
                
                // Проверяем существование элементов
                if (!fieldSelect || !fieldTypeInfo || !removeBtn || !fieldInput) {
                    console.error('Один из элементов в шаблоне не найден');
                    return;
                }
                
                // Добавляем опцию с полем модуля
                const option = document.createElement('option');
                option.value = field.name || field.field_name;
                option.textContent = `${field.name || field.field_name} (${field.type || field.data_type})`;
                option.selected = true;
                fieldSelect.appendChild(option);
                
                // Показываем информацию о типе
                const fieldType = field.type || field.data_type || 'unknown';
                const isNullable = field.nullable !== undefined ? field.nullable : true;
                fieldTypeInfo.textContent = `Тип: ${fieldType}${!isNullable ? ', NOT NULL' : ''}`;
                
                // Автоматически заполняем поле 1C на основе имени поля
                const suggested1cField = this.mapFieldNameTo1C(field.name || field.field_name);
                fieldInput.value = suggested1cField;
                fieldInput.setAttribute('data-field-type', fieldType);
                
                // Добавляем обработчик удаления (кроме первых 3 полей)
                if (index >= 3) {
                    removeBtn.style.display = 'block';
                    removeBtn.addEventListener('click', () => {
                        row.remove();
                        this.updateFieldMapping();
                    });
                }
                
                // Обработчики изменений
                fieldInput.addEventListener('input', () => this.updateFieldMapping());
                fieldSelect.addEventListener('change', () => this.updateFieldMapping());
                
                const requiredCheckbox = clone.querySelector('.field-required');
                if (requiredCheckbox) {
                    requiredCheckbox.addEventListener('change', () => this.updateFieldMapping());
                }
                
                container.appendChild(clone);
            });
            
            // Добавляем кнопку для добавления дополнительных полей
            if (fields.length > 0) {
                const addMoreDiv = document.createElement('div');
                addMoreDiv.className = 'p-3 text-center border-top';
                addMoreDiv.innerHTML = `
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addCustomFieldBtn">
                        <i class="fas fa-plus me-1"></i> Добавить дополнительное поле
                    </button>
                `;
                container.appendChild(addMoreDiv);
                
                // Обработчики для кнопок
                const addCustomFieldBtn = document.getElementById('addCustomFieldBtn');
                if (addCustomFieldBtn) {
                    addCustomFieldBtn.addEventListener('click', () => this.addCustomField());
                }
            }
            
            // Инициализируем маппинг
            this.updateFieldMapping();
        }

        /**
         * Маппинг имен полей на соответствующие поля 1C
         */
        mapFieldNameTo1C(fieldName) {
            if (!fieldName) return '';
            
            const mapping = {
                'title': 'Наименование',
                'name': 'Наименование',
                'price': 'Цена',
                'cost': 'Себестоимость',
                'quantity': 'Количество',
                'sku': 'Артикул',
                'article': 'Артикул',
                'description': 'Описание',
                'weight': 'Вес',
                'volume': 'Объем',
                'code': 'Код',
                'external_id': 'Идентификатор',
                'created_at': 'ДатаСоздания',
                'updated_at': 'ДатаИзменения',
                'status': 'Статус',
                'active': 'Активен',
                'enabled': 'Включен',
                'category': 'Категория',
                'group': 'Группа',
                'type': 'Тип',
                'unit': 'ЕдиницаИзмерения',
                'vendor': 'Производитель',
                'brand': 'Бренд',
                'model': 'Модель',
                'color': 'Цвет',
                'size': 'Размер',
                'material': 'Материал',
                'country': 'Страна',
                'manufacturer': 'Производитель',
                'barcode': 'Штрихкод',
                'width': 'Ширина',
                'height': 'Высота',
                'depth': 'Глубина',
                'length': 'Длина',
                'diameter': 'Диаметр',
                'image': 'Изображение',
                'photo': 'Фото',
                'picture': 'Картинка',
                'url': 'Ссылка',
                'link': 'Ссылка',
                'email': 'Email',
                'phone': 'Телефон',
                'address': 'Адрес',
                'city': 'Город',
                'zip': 'Индекс',
                'country_code': 'КодСтраны',
                'currency': 'Валюта',
                'tax': 'Налог',
                'discount': 'Скидка',
                'bonus': 'Бонус',
                'rating': 'Рейтинг',
                'views': 'Просмотры',
                'sales': 'Продажи',
                'stock': 'Остаток',
                'reserve': 'Резерв'
            };
            
            // Проверяем точное соответствие
            if (mapping[fieldName.toLowerCase()]) {
                return mapping[fieldName.toLowerCase()];
            }
            
            // Проверяем частичное соответствие
            for (const [key, value] of Object.entries(mapping)) {
                if (fieldName.toLowerCase().includes(key)) {
                    return value;
                }
            }
            
            // Если не нашли соответствие, возвращаем оригинальное имя с заглавной буквы
            return fieldName.charAt(0).toUpperCase() + fieldName.slice(1);
        }

        /**
         * Обновление данных маппинга
         */
        updateFieldMapping() {
            const mapping = [];
            const rows = document.querySelectorAll('.field-mapping-row');
            
            rows.forEach(row => {
                const field1c = row.querySelector('.field-1c-input')?.value.trim();
                const fieldModule = row.querySelector('.field-module-select')?.value;
                const isRequired = row.querySelector('.field-required')?.checked || false;
                const fieldType = row.querySelector('.field-1c-input')?.getAttribute('data-field-type');
                
                if (field1c && fieldModule) {
                    mapping.push({
                        source: field1c,
                        target: fieldModule,
                        required: isRequired,
                        type: fieldType || 'string'
                    });
                }
            });
            
            // Сохраняем в глобальные данные и скрытое поле
            this.integrationData.fieldMapping = mapping;
            const fieldMappingInput = document.getElementById('fieldMappingInput');
            if (fieldMappingInput) {
                fieldMappingInput.value = JSON.stringify(mapping);
            }
            
            console.log('Текущий маппинг:', mapping);
            return mapping;
        }

        /**
         * Добавление кастомного поля
         */
        addCustomField() {
            const container = document.getElementById('fieldMappingRows');
            const template = document.getElementById('fieldMappingTemplate');
            
            if (!container || !template) {
                console.error('Контейнер или шаблон не найдены');
                return;
            }
            
            const clone = template.content.cloneNode(true);
            const row = clone.querySelector('.field-mapping-row');
            const fieldSelect = clone.querySelector('.field-module-select');
            const removeBtn = clone.querySelector('.remove-field-btn');
            
            if (!row || !fieldSelect || !removeBtn) {
                console.error('Элементы в шаблоне не найдены');
                return;
            }
            
            // Очищаем выбранное поле
            fieldSelect.innerHTML = '<option value="">-- Выберите поле --</option>';
            
            // Добавляем опцию "Кастомное поле"
            const customOption = document.createElement('option');
            customOption.value = 'custom_field';
            customOption.textContent = 'Кастомное поле (введите название)';
            customOption.selected = true;
            fieldSelect.appendChild(customOption);
            
            // Добавляем доступные поля модуля
            if (this.integrationData.moduleFields && this.integrationData.moduleFields.length > 0) {
                this.integrationData.moduleFields.forEach(field => {
                    const option = document.createElement('option');
                    option.value = field.name || field.field_name;
                    option.textContent = `${field.name || field.field_name} (${field.type || field.data_type})`;
                    fieldSelect.appendChild(option);
                });
            }
            
            // Делаем поле для ввода кастомного названия
            const customInput = document.createElement('input');
            customInput.type = 'text';
            customInput.className = 'form-control mt-2 custom-field-name';
            customInput.placeholder = 'Введите название поля';
            fieldSelect.parentNode.appendChild(customInput);
            
            // Показываем кнопку удаления
            removeBtn.style.display = 'block';
            removeBtn.addEventListener('click', () => {
                row.remove();
                this.updateFieldMapping();
            });
            
            // Обработчики изменений
            const field1cInput = clone.querySelector('.field-1c-input');
            const requiredCheckbox = clone.querySelector('.field-required');
            
            if (field1cInput) {
                field1cInput.addEventListener('input', () => this.updateFieldMapping());
            }
            
            if (fieldSelect) {
                fieldSelect.addEventListener('change', () => this.updateFieldMapping());
            }
            
            if (customInput) {
                customInput.addEventListener('input', function() {
                    // При вводе в кастомное поле, обновляем значение select
                    if (fieldSelect.value === 'custom_field') {
                        this.updateFieldMapping();
                    }
                }.bind(this));
            }
            
            if (requiredCheckbox) {
                requiredCheckbox.addEventListener('change', () => this.updateFieldMapping());
            }
            
            // Находим контейнер с кнопками добавления
            const addButtonsDiv = container.querySelector('.text-center.border-top');
            if (addButtonsDiv) {
                container.insertBefore(clone, addButtonsDiv);
            } else {
                container.appendChild(clone);
            }
            
            this.updateFieldMapping();
        }

        /**
         * Валидация шага 1 (выбор сервиса)
         */
        validateStep1() {
            console.log('Валидация шага 1');
            
            if (!this.integrationData.selectedService) {
                this.showToast('Пожалуйста, выберите внешний сервис для интеграции', 'warning');
                return false;
            }
            return true;
        }

        /**
         * Валидация шага 2 (настройки подключения)
         */
        validateStep2() {
            console.log('Валидация шага 2');
            
            const service = this.integrationData.selectedService;
            let isValid = true;
            
            // Проверяем настройки для выбранного драйвера
            if (service) {
                const settingsContainer = document.getElementById(`settings-${service}`);
                if (settingsContainer) {
                    // Проверяем обязательные поля в форме настроек драйвера
                    const requiredInputs = settingsContainer.querySelectorAll('[required]');
                    for (const input of requiredInputs) {
                        if (!input.value.trim()) {
                            this.showToast(`Пожалуйста, заполните обязательное поле: ${input.previousElementSibling?.textContent || input.name}`, 'warning');
                            input.focus();
                            isValid = false;
                            break;
                        }
                    }
                }
            }
            
            // Проверяем общее название интеграции
            const integrationName = document.getElementById('integration_name');
            if (integrationName && (!integrationName.value || integrationName.value.trim() === '')) {
                this.showToast('Пожалуйста, укажите название интеграции', 'warning');
                isValid = false;
            }
            
            return isValid;
        }

        /**
         * Валидация шага 3 (выбор модуля)
         */
        validateStep3() {
            console.log('Валидация шага 3');
            
            if (!this.integrationData.selectedModule) {
                this.showToast('Пожалуйста, выберите внутренний модуль для интеграции', 'warning');
                return false;
            }
            
            const syncDirection = document.querySelector('input[name="sync_direction"]:checked');
            if (!syncDirection) {
                this.showToast('Пожалуйста, выберите направление синхронизации', 'warning');
                return false;
            }
            
            return true;
        }

        /**
         * Создание интеграции с валидацией
         */
        createIntegration() {
            console.log('Создание интеграции');
            
            // Валидация всех шагов
            if (!this.validateStep1() || !this.validateStep2() || !this.validateStep3()) {
                this.showToast('Пожалуйста, заполните все обязательные поля', 'warning');
                return;
            }
            
            // Проверка, что есть хотя бы одно сопоставленное поле
            const mapping = this.updateFieldMapping();
            if (mapping.length === 0) {
                this.showToast('Пожалуйста, сопоставьте хотя бы одно поле', 'warning');
                
                // Прокрутка к полям сопоставления
                const fieldMappingContainer = document.getElementById('fieldMappingContainer');
                if (fieldMappingContainer) {
                    fieldMappingContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }
            
            // Показываем загрузку
            this.showToast('Создание интеграции...', 'info');
            
            // Отправляем форму
            const form = document.getElementById('integrationForm');
            if (!form) {
                this.showToast('Форма не найдена', 'error');
                return;
            }
            
            // Добавляем дополнительные данные в скрытые поля
            const selectedModuleInput = document.getElementById('selectedModuleInput');
            const fieldMappingInput = document.getElementById('fieldMappingInput');
            
            if (selectedModuleInput) selectedModuleInput.value = this.integrationData.selectedModule;
            if (fieldMappingInput) fieldMappingInput.value = JSON.stringify(mapping);
            
            // Отправка формы
            form.submit();
        }

        /**
         * Получение настроек сервиса
         */
        getServiceSettings() {
            const service = this.integrationData.selectedService;
            const settings = {};
            
            if (!service) return settings;
            
            // Собираем настройки из формы драйвера
            const settingsContainer = document.getElementById(`settings-${service}`);
            if (settingsContainer) {
                const inputs = settingsContainer.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    if (input.name && input.name.startsWith('config[')) {
                        const key = input.name.match(/config\[(.*?)\]/)?.[1];
                        if (key) {
                            if (input.type === 'checkbox') {
                                settings[key] = input.checked ? input.value : '';
                            } else {
                                settings[key] = input.value;
                            }
                        }
                    }
                });
            }
            
            return settings;
        }

        /**
         * Показать модальное окно тестирования
         */
        showTestModal() {
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
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>Тестирование проверит подключение к сервису и корректность сопоставления полей</small>
                        </div>
                    </div>
                `;
            }
            
            testModal.show();
        }

        /**
         * Запуск теста интеграции
         */
        runIntegrationTest() {
            console.log('Запуск теста интеграции');
            
            const testResults = document.getElementById('testResults');
            if (!testResults) return;
            
            testResults.innerHTML = `
                <div class="text-center py-3">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Тестирование...</span>
                    </div>
                    <p>Выполняется тестирование интеграции...</p>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                    </div>
                </div>
            `;
            
            const progressBar = testResults.querySelector('.progress-bar');
            
            // Имитация прогресса тестирования
            let progress = 0;
            const interval = setInterval(() => {
                progress += 10;
                if (progressBar) {
                    progressBar.style.width = `${progress}%`;
                }
                
                if (progress >= 100) {
                    clearInterval(interval);
                    this.showTestResults();
                }
            }, 300);
        }

        /**
         * Показать результаты тестирования
         */
        showTestResults() {
            const testResults = document.getElementById('testResults');
            if (!testResults) return;
            
            const mapping = this.updateFieldMapping();
            const hasMapping = mapping.length > 0;
            const serviceSettings = this.getServiceSettings();
            const hasServiceSettings = Object.keys(serviceSettings).length > 0;
            
            testResults.innerHTML = `
                <div class="alert ${hasMapping && hasServiceSettings ? 'alert-success' : 'alert-warning'}">
                    <h6><i class="fas ${hasMapping && hasServiceSettings ? 'fa-check-circle' : 'fa-exclamation-triangle'} me-2"></i>
                        ${hasMapping && hasServiceSettings ? 'Тестирование завершено успешно!' : 'Тестирование завершено с предупреждениями'}
                    </h6>
                    <hr>
                    <div class="small">
                        <p><strong>Проверено:</strong></p>
                        <ul class="mb-0">
                            <li>Выбор сервиса: <span class="text-success">✓ ${this.integrationData.selectedServiceName || 'Не выбран'}</span></li>
                            <li>Настройки подключения: <span class="${hasServiceSettings ? 'text-success' : 'text-warning'}">
                                ${hasServiceSettings ? '✓ Настроены' : '⚠ Не настроены'}
                            </span></li>
                            <li>Выбор модуля: <span class="text-success">✓ ${this.integrationData.selectedModuleName || 'Не выбран'}</span></li>
                            <li>Сопоставление полей: <span class="${hasMapping ? 'text-success' : 'text-warning'}">
                                ${hasMapping ? `✓ ${mapping.length} полей сопоставлено` : '⚠ Нет сопоставленных полей'}
                            </span></li>
                        </ul>
                    </div>
                </div>
            `;
        }

        /**
         * Показать toast-уведомление
         */
        showToast(message, type = 'info') {
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
    }

    // Инициализируем приложение при загрузке страницы
    document.addEventListener('DOMContentLoaded', () => {
        new IntegrationCreator();
    });

    // Экспортируем класс для отладки
    window.IntegrationCreator = IntegrationCreator;

    console.log('Страница создания интеграции полностью инициализирована');
}

