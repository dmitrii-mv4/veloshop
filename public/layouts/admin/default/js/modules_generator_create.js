document.addEventListener('DOMContentLoaded', function() {
    // Инициализация тултипов Bootstrap
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    
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
    
    // Получаем DOM элементы
    const nameFieldRu = document.getElementById('name_ru');
    const slugField = document.getElementById('slug-field');
    const regenerateSlugBtn = document.getElementById('regenerate-slug');
    const descriptionRu = document.getElementById('description_ru');
    const translationDescriptionRu = document.getElementById('translation_description_ru');
    const codeModuleField = document.getElementById('code_module');
    
    // Получаем элементы для отображения в разделе переводов
    const ruNameDisplay = document.querySelector('#lang-ru .form-control.bg-light:first-child');
    const ruDescriptionDisplay = document.querySelector('#lang-ru .form-control.bg-light:nth-child(2)');
    const ruNameHidden = document.querySelector('#lang-ru input[name="name[ru]"]');
    const ruDescriptionHidden = document.querySelector('#lang-ru input[name="description[ru]"]');
    
    let isSlugManuallyChanged = slugField.value.length > 0;

    // Проверяем, что элементы существуют
    if (descriptionRu && translationDescriptionRu) {
        // Синхронизируем при загрузке
        translationDescriptionRu.value = descriptionRu.value;
        
        // Синхронизируем при вводе
        descriptionRu.addEventListener('input', function() {
            translationDescriptionRu.value = this.value;
        });
        
        // Также синхронизируем при изменении через программу
        descriptionRu.addEventListener('change', function() {
            translationDescriptionRu.value = this.value;
        });
    }
    
    // Функция для преобразования текста в slug
    function slugify(text) {
        return text
            .toString()
            .toLowerCase()
            .trim()
            .replace(/[а-яё]/g, function(match) {
                const ruMap = {
                    'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd',
                    'е': 'e', 'ё': 'yo', 'ж': 'zh', 'з': 'z', 'и': 'i',
                    'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n',
                    'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't',
                    'у': 'u', 'ф': 'f', 'х': 'kh', 'ц': 'ts', 'ч': 'ch',
                    'ш': 'sh', 'щ': 'shch', 'ъ': '', 'ы': 'y', 'ь': '',
                    'э': 'e', 'ю': 'yu', 'я': 'ya'
                };
                return ruMap[match] || match;
            })
            .replace(/[^\x00-\x7F]/g, '')
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/--+/g, '-')
            .replace(/^-+/, '')
            .replace(/-+$/, '');
    }
    
    // Функция для обновления отображения в разделе переводов
    function updateRussianTranslations() {
        if (ruNameDisplay && nameFieldRu) {
            ruNameDisplay.textContent = nameFieldRu.value;
        }
        if (ruNameHidden && nameFieldRu) {
            ruNameHidden.value = nameFieldRu.value;
        }
        if (ruDescriptionDisplay && descriptionRu) {
            ruDescriptionDisplay.textContent = descriptionRu.value;
        }
        if (ruDescriptionHidden && descriptionRu) {
            ruDescriptionHidden.value = descriptionRu.value;
        }
    }
    
    // Функция генерации slug из названия
    function generateSlugFromName() {
        if (!isSlugManuallyChanged || slugField.value === '') {
            const nameValue = nameFieldRu.value.trim();
            if (nameValue) {
                slugField.value = slugify(nameValue);
                
                // Автоматически заполняем code_module из slug
                if (codeModuleField && !codeModuleField.value) {
                    codeModuleField.value = slugify(nameValue).replace(/-/g, '_');
                }
                
                updateRussianTranslations();
            }
        }
    }
    
    // Обработчик ввода в поле slug
    slugField.addEventListener('input', function() {
        isSlugManuallyChanged = this.value.length > 0;
    });
    
    // Обработчик ввода в поле названия на русском
    let nameTimeout;
    nameFieldRu.addEventListener('input', function() {
        clearTimeout(nameTimeout);
        nameTimeout = setTimeout(function() {
            generateSlugFromName();
            updateRussianTranslations();
        }, 300);
    });
    
    // Обработчик потери фокуса с поля названия
    nameFieldRu.addEventListener('blur', function() {
        if (slugField.value === '' && nameFieldRu.value.trim()) {
            generateSlugFromName();
        }
    });
    
    // Обработчик кнопки перегенерации slug
    regenerateSlugBtn.addEventListener('click', function() {
        if (!nameFieldRu.value.trim()) {
            alert('Введите название для генерации slug');
            nameFieldRu.focus();
            return;
        }
        
        const newSlug = slugify(nameFieldRu.value);
        slugField.value = newSlug;
        isSlugManuallyChanged = true;
        
        // Обновляем code_module
        if (codeModuleField) {
            codeModuleField.value = newSlug.replace(/-/g, '_');
        }
        
        updateRussianTranslations();
        alert('Slug успешно перегенерирован');
    });
    
    // Обработчик ввода в поле описания
    if (descriptionRu) {
        descriptionRu.addEventListener('input', function() {
            updateRussianTranslations();
        });
    }
    
    // Инициализация при загрузке
    updateRussianTranslations();
    
    // Управление свойствами модуля (остальной код остается без изменений)
    const propertiesContainer = document.getElementById('properties-container');
    const addPropertyBtn = document.getElementById('add-property');
    const propertyCountElement = document.getElementById('property-count');
    let propertyCounter = 1;
    
    // Объект для хранения свойств и их переводов
    const moduleProperties = {};
    
    // Инициализация moduleProperties из существующих свойств
    function initializeModuleProperties() {
        document.querySelectorAll('.property-item').forEach((propertyElement, index) => {
            const nameRuInput = propertyElement.querySelector('.property-name-ru');
            const codeInput = propertyElement.querySelector('.property-code');
            const typeInput = propertyElement.querySelector('.property-type');
            
            moduleProperties[index] = {
                code: codeInput.value.trim(),
                name: {
                    ru: nameRuInput.value.trim(),
                    en: ''
                },
                type: typeInput ? typeInput.value : 'string'
            };
        });
    }
    
    // Вызываем инициализацию при загрузке
    initializeModuleProperties();
    updatePropertiesTranslations(); // Обновляем переводы после инициализации
    
    // Функция обновления счетчика свойств
    function updatePropertyCount() {
        const count = propertiesContainer.children.length;
        propertyCountElement.textContent = `Добавлено ${count} ${getRussianWordForm(count, ['свойство', 'свойства', 'свойств'])}`;
        
        // Активируем кнопки удаления, если свойств больше одного
        const removeButtons = document.querySelectorAll('.remove-property');
        removeButtons.forEach(button => {
            button.disabled = count <= 1;
        });
    }
    
    // Функция для получения правильной формы слова
    function getRussianWordForm(number, forms) {
        const cases = [2, 0, 1, 1, 1, 2];
        return forms[(number % 100 > 4 && number % 100 < 20) ? 2 : cases[(number % 10 < 5) ? number % 10 : 5]];
    }
    
    // Функция создания нового свойства
    function createPropertyElement(index) {
        const propertyElement = document.createElement('div');
        propertyElement.className = 'property-item card border-light mb-3';
        propertyElement.setAttribute('data-property-index', index);
        propertyElement.innerHTML = `
            <div class="card-body">
                <div class="row g-3 align-items-center">
                    <div class="col-md-3">
                        <label class="form-label mb-0">Название свойства (русский)</label>
                        <input type="text" class="form-control property-name-ru" name="properties[${index}][name][ru]" placeholder="Например: Цена, Описание, Автор" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-0">Тип данных</label>
                        <select class="form-select property-type" name="properties[${index}][type]" required>
                            <option value="" disabled selected>Выберите тип</option>
                            <optgroup label="Текстовые типы">
                                <option value="string">Строка (до 255 символов)</option>
                                <option value="text">Текст (длинный текст)</option>
                            </optgroup>
                            <optgroup label="Числовые типы">
                                <option value="integer">Целое число</option>
                                <option value="bigInteger">Большое целое число</option>
                                <option value="float">Дробное число</option>
                                <option value="decimal">Десятичное число</option>
                            </optgroup>
                            <optgroup label="Дата и время">
                                <option value="date">Дата</option>
                                <option value="datetime">Дата и время</option>
                                <option value="time">Время</option>
                                <option value="timestamp">Метка времени</option>
                            </optgroup>
                            <optgroup label="Логические">
                                <option value="boolean">Да/Нет (Boolean)</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-0">Код поля (slug)</label>
                        <div class="input-group">
                            <input type="text" class="form-control property-code" name="properties[${index}][code]" placeholder="Например: price, description" required>
                            <button type="button" class="btn btn-outline-secondary generate-code-btn">
                                <i class="bi bi-magic"></i>
                            </button>
                        </div>
                        <div class="form-text small">Только латинские буквы, цифры и подчеркивания</div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-0">Обязательное поле</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="properties[${index}][required]">
                            <label class="form-check-label">Да</label>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label mb-0">Действия</label>
                        <button type="button" class="btn btn-danger btn-sm remove-property">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        return propertyElement;
    }
    
    // Функция добавления обработчиков событий для элемента свойства
    function addPropertyEventListeners(propertyElement) {
        const index = propertyElement.getAttribute('data-property-index');
        
        // Генерация кода из названия
        const generateCodeBtn = propertyElement.querySelector('.generate-code-btn');
        const propertyNameInput = propertyElement.querySelector('.property-name-ru');
        const propertyCodeInput = propertyElement.querySelector('.property-code');
        const propertyTypeInput = propertyElement.querySelector('.property-type');
        
        generateCodeBtn.addEventListener('click', function() {
            const propertyName = propertyNameInput.value.trim();
            if (!propertyName) {
                showAlert('Введите название свойства для генерации кода', 'warning');
                propertyNameInput.focus();
                return;
            }
            
            const code = slugify(propertyName).replace(/-/g, '_');
            propertyCodeInput.value = code;
            
            // Обновляем сохраненное свойство
            if (moduleProperties[index]) {
                moduleProperties[index].code = code;
            }
            
            updatePropertiesTranslations();
            showAlert('Код свойства сгенерирован', 'success');
        });
        
        // Автоматическая генерация кода при вводе названия
        propertyNameInput.addEventListener('blur', function() {
            if (!propertyCodeInput.value.trim() && this.value.trim()) {
                const code = slugify(this.value).replace(/-/g, '_');
                propertyCodeInput.value = code;
                
                // Обновляем сохраненное свойство
                if (moduleProperties[index]) {
                    moduleProperties[index].code = code;
                    moduleProperties[index].name.ru = this.value.trim();
                }
                
                updatePropertiesTranslations();
            }
        });
        
        // Сохранение данных свойства при изменении
        propertyNameInput.addEventListener('input', function() {
            const index = propertyElement.getAttribute('data-property-index');
            if (moduleProperties[index]) {
                moduleProperties[index].name.ru = this.value.trim();
            }
            
            // Обновляем переводы через 500 мс после остановки ввода
            clearTimeout(window.propertyNameTimeout);
            window.propertyNameTimeout = setTimeout(updatePropertiesTranslations, 500);
        });
        
        propertyCodeInput.addEventListener('input', function() {
            const index = propertyElement.getAttribute('data-property-index');
            if (moduleProperties[index]) {
                moduleProperties[index].code = this.value.trim();
            }
            updatePropertiesTranslations();
        });
        
        if (propertyTypeInput) {
            propertyTypeInput.addEventListener('change', function() {
                const index = propertyElement.getAttribute('data-property-index');
                if (moduleProperties[index]) {
                    moduleProperties[index].type = this.value;
                }
            });
        }
        
        // Удаление свойства
        const removePropertyBtn = propertyElement.querySelector('.remove-property');
        removePropertyBtn.addEventListener('click', function() {
            if (propertiesContainer.children.length <= 1) {
                showAlert('Должно быть хотя бы одно свойство', 'warning');
                return;
            }
            
            // Удаляем свойство из объекта
            const index = propertyElement.getAttribute('data-property-index');
            if (moduleProperties[index]) {
                delete moduleProperties[index];
            }
            
            propertyElement.remove();
            updatePropertyCount();
            updatePropertiesTranslations();
            showAlert('Свойство удалено', 'success');
        });
        
        // Инициализируем объект свойства, если его еще нет
        if (!moduleProperties[index]) {
            moduleProperties[index] = {
                name: {
                    ru: propertyNameInput.value.trim(),
                    en: ''
                },
                code: propertyCodeInput.value.trim(),
                type: propertyTypeInput ? propertyTypeInput.value : 'string'
            };
        }
    }
    
    // Обновление переводов свойств в разделе переводов
    function updatePropertiesTranslations() {
        // Получаем контейнеры для каждого языка
        const ruContainer = document.querySelector('.properties-translations-container[data-lang="ru"]');
        const enContainer = document.querySelector('.properties-translations-container[data-lang="en"]');
        
        if (!ruContainer || !enContainer) return;
        
        // Очищаем контейнеры
        ruContainer.innerHTML = '';
        enContainer.innerHTML = '';
        
        // Перебираем moduleProperties
        Object.keys(moduleProperties).forEach(index => {
            const property = moduleProperties[index];
            
            if (property && property.code) {
                // Русский язык
                const ruItem = document.createElement('div');
                ruItem.className = 'property-translation-item';
                ruItem.innerHTML = `
                    <div class="property-translation-code">${property.code}</div>
                    <div class="property-translation-input flex-grow-1">
                        <input type="text" 
                            class="form-control form-control-sm" 
                            name="properties[${index}][name][ru]" 
                            value="${property.name.ru || ''}" 
                            readonly>
                    </div>
                `;
                ruContainer.appendChild(ruItem);
                
                // Английский язык
                const enItem = document.createElement('div');
                enItem.className = 'property-translation-item';
                enItem.innerHTML = `
                    <div class="property-translation-code">${property.code}</div>
                    <div class="property-translation-input flex-grow-1">
                        <input type="text" 
                            class="form-control form-control-sm" 
                            name="properties[${index}][name][en]" 
                            value="${property.name.en || ''}" 
                            placeholder="Перевод на английский">
                    </div>
                `;
                
                // Добавляем обработчик изменения значения для английского поля
                const enInput = enItem.querySelector('input');
                enInput.addEventListener('input', function() {
                    if (moduleProperties[index]) {
                        moduleProperties[index].name.en = this.value.trim();
                    }
                });
                
                enContainer.appendChild(enItem);
            }
        });
        
        // Если свойств нет, показываем сообщение
        if (Object.keys(moduleProperties).length === 0) {
            ruContainer.innerHTML = '<div class="text-muted text-center py-3">Сначала добавьте свойства в соответствующей вкладке</div>';
            enContainer.innerHTML = '<div class="text-muted text-center py-3">Сначала добавьте свойства в соответствующей вкладке</div>';
        }
    }
    
    // Обработчик добавления нового свойства
    addPropertyBtn.addEventListener('click', function() {
        propertyCounter++;
        const newIndex = propertyCounter - 1;
        const newProperty = createPropertyElement(newIndex);
        propertiesContainer.appendChild(newProperty);
        
        // Инициализируем moduleProperties для нового свойства
        moduleProperties[newIndex] = {
            code: '',
            name: {
                ru: '',
                en: ''
            },
            type: 'string'
        };
        
        addPropertyEventListeners(newProperty);
        updatePropertyCount();
        updatePropertiesTranslations();
        
        // Прокручиваем к новому элементу
        newProperty.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        
        showAlert('Новое свойство добавлено', 'success');
    });
    
    // Инициализация обработчиков для существующих свойств
    document.querySelectorAll('.property-item').forEach((propertyElement, index) => {
        propertyElement.setAttribute('data-property-index', index);
        addPropertyEventListeners(propertyElement);
    });
    
    // Обновление счетчика свойств при загрузке
    updatePropertyCount();
    
    // Предварительный просмотр модуля
    const previewBtn = document.getElementById('preview-module');
    previewBtn.addEventListener('click', function() {
        const moduleName = nameFieldRu.value.trim();
        
        if (!moduleName) {
            showAlert('Введите название модуля для предварительного просмотра', 'warning');
            nameFieldRu.focus();
            return;
        }
        
        // Создаем модальное окно с предварительным просмотром
        createPreviewModal();
    });
    
    // Функция создания модального окна предварительного просмотра
    function createPreviewModal() {
        // Собираем данные для предварительного просмотра
        const moduleData = {
            name: {
                ru: nameFieldRu.value.trim(),
                en: document.getElementById('name_en').value.trim()
            },
            description: {
                ru: document.getElementById('description_ru').value.trim(),
                en: document.getElementById('description_en').value.trim()
            },
            slug: slugField.value.trim(),
            hasSeo: document.getElementById('section_seo').checked,
            hasCategories: document.getElementById('section_categories').checked,
            hasTags: document.getElementById('section_tags').checked,
            hasComments: document.getElementById('section_comments').checked,
            hasMedia: document.getElementById('section_media').checked,
            status: document.querySelector('input[name="status"]:checked').value,
            propertiesCount: Object.keys(moduleProperties).length
        };
        
        // Создаем содержимое модального окна
        const modalContent = `
            <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="previewModalLabel">Предварительный просмотр модуля</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="mb-3">Основная информация</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">Название (ru):</th>
                                            <td>${moduleData.name.ru || 'Не указано'}</td>
                                        </tr>
                                        <tr>
                                            <th>Название (en):</th>
                                            <td>${moduleData.name.en || 'Не указано'}</td>
                                        </tr>
                                        <tr>
                                            <th>URL-адрес:</th>
                                            <td>/${moduleData.slug || 'module-slug'}</td>
                                        </tr>
                                        <tr>
                                            <th>Статус:</th>
                                            <td>
                                                ${moduleData.status === 'active' ? 
                                                    '<span class="badge bg-success">Активен</span>' : 
                                                    '<span class="badge bg-warning">Неактивен</span>'}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="mb-3">Включенные разделы</h6>
                                    <div class="mb-3">
                                        ${moduleData.hasSeo ? '<span class="badge bg-info me-1 mb-1">SEO</span>' : ''}
                                        ${moduleData.hasCategories ? '<span class="badge bg-info me-1 mb-1">Категории</span>' : ''}
                                        ${moduleData.hasTags ? '<span class="badge bg-info me-1 mb-1">Теги</span>' : ''}
                                        ${moduleData.hasComments ? '<span class="badge bg-info me-1 mb-1">Комментарии</span>' : ''}
                                        ${moduleData.hasMedia ? '<span class="badge bg-info me-1 mb-1">Медиа</span>' : ''}
                                    </div>
                                    
                                    <h6 class="mb-3">Статистика</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="60%">Количество свойств:</th>
                                            <td>${moduleData.propertiesCount}</td>
                                        </tr>
                                        <tr>
                                            <th>Будет создано таблиц:</th>
                                            <td>${1 + (moduleData.hasCategories ? 1 : 0)}</td>
                                        </tr>
                                        <tr>
                                            <th>Поддерживаемые языки:</th>
                                            <td>ru, en</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Свойства модуля -->
                            ${moduleData.propertiesCount > 0 ? `
                            <div class="mt-4">
                                <h6 class="mb-3">Свойства модуля:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Код</th>
                                                <th>Название (ru)</th>
                                                <th>Название (en)</th>
                                                <th>Тип</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${Object.keys(moduleProperties).map(index => {
                                                const prop = moduleProperties[index];
                                                return `
                                                    <tr>
                                                        <td><code>${prop.code}</code></td>
                                                        <td>${prop.name.ru || '-'}</td>
                                                        <td>${prop.name.en || '-'}</td>
                                                        <td><span class="badge bg-light text-dark">${prop.type}</span></td>
                                                    </tr>
                                                `;
                                            }).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            ` : ''}
                            
                            <div class="mt-4">
                                <h6 class="mb-3">Будет создана следующая структура:</h6>
                                <div class="module-structure small">
                                    <div class="mb-2"><code>app/Modules/</code></div>
                                    <div class="ms-3 mb-1"><code>├── ${moduleData.name.ru.replace(/\s+/g, '') || 'ModuleName'}/</code></div>
                                    <div class="ms-5 mb-1"><code>├── Models/</code></div>
                                    <div class="ms-7 mb-1"><code>├── ${moduleData.name.ru.replace(/\s+/g, '') || 'ModuleName'}Model.php</code></div>
                                    <div class="ms-5 mb-1"><code>├── Controllers/</code></div>
                                    <div class="ms-7 mb-1"><code>├── Admin/</code></div>
                                    <div class="ms-9 mb-1"><code>├── ${moduleData.name.ru.replace(/\s+/g, '') || 'ModuleName'}Controller.php</code></div>
                                    <div class="ms-5 mb-1"><code>├── Resources/</code></div>
                                    <div class="ms-7 mb-1"><code>├── views/</code></div>
                                    <div class="ms-5 mb-1"><code>├── translations/</code></div>
                                    <div class="ms-7 mb-1"><code>├── ru.json</code></div>
                                    <div class="ms-7 mb-1"><code>├── en.json</code></div>
                                    <div class="ms-5 mb-1"><code>└── Routes/</code></div>
                                    <div class="ms-7 mb-1"><code>├── admin.php</code></div>
                                    <div class="ms-7"><code>└── web.php</code></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('module-create-form').submit()">
                                <i class="bi bi-check-circle me-2"></i>Создать модуль
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Добавляем модальное окно в DOM
        const modalContainer = document.createElement('div');
        modalContainer.innerHTML = modalContent;
        document.body.appendChild(modalContainer);
        
        // Показываем модальное окно
        const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
        previewModal.show();
        
        // Удаляем модальное окно из DOM после скрытия
        document.getElementById('previewModal').addEventListener('hidden.bs.modal', function() {
            modalContainer.remove();
        });
    }
    
    // Функция показа уведомлений
    function showAlert(message, type = 'info') {
        // Удаляем существующие алерты
        const existingAlerts = document.querySelectorAll('.custom-alert');
        existingAlerts.forEach(alert => alert.remove());
        
        // Создаем новый алерт
        const alertDiv = document.createElement('div');
        alertDiv.className = `custom-alert alert alert-${type} alert-dismissible fade show`;
        alertDiv.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Автоматически скрываем алерт через 5 секунд
        setTimeout(() => {
            if (alertDiv.parentNode) {
                const bsAlert = new bootstrap.Alert(alertDiv);
                bsAlert.close();
            }
        }, 5000);
    }
    
    // Инициализация обновления предварительного просмотра при загрузке
    updatePreviewNames();
    
    // Синхронизация полей при загрузке страницы
    if (translationNameRu && nameFieldRu) {
        translationNameRu.value = nameFieldRu.value;
    }
    if (translationDescriptionRu && descriptionRu) {
        translationDescriptionRu.value = descriptionRu.value;
    }
});