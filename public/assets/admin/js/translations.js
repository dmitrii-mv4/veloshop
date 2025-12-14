// Универсальный менеджер переводов (исправленная версия)
class UniversalTranslationManager {
    constructor() {
        this.modal = null;
        this.currentConfig = null;
        this.languages = [
            { code: 'ru', name: 'Русский (основной)', isPrimary: true },
            { code: 'en', name: 'Английский', isPrimary: false }
        ];

        this.init();
    }

    init() {
        // Создаем экземпляр модального окна
        const modalElement = document.getElementById('universalTranslationModal');
        if (modalElement) {
            this.modal = new bootstrap.Modal(modalElement);
        }

        this.bindEvents();
        this.initializeExistingBadges();
    }

    bindEvents() {
        // Обработчики для всех кнопок перевода
        document.addEventListener('click', (e) => {
            const translationBtn = e.target.closest('.translation-btn');
            if (translationBtn) {
                this.openTranslationModal(translationBtn);
            }
        });

        // Сохранение переводов
        const saveBtn = document.getElementById('saveUniversalTranslationsBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => this.saveTranslations());
        }

        // Синхронизация полей в реальном времени
        document.addEventListener('input', (e) => {
            this.handleRealTimeSync(e);
        });
    }

    openTranslationModal(button) {
        const context = button.dataset.translationContext; // 'module' или 'property'
        const config = this.getConfig(context, button);

        if (!config) {
            console.error('Config not found for context:', context, button);
            return;
        }

        this.currentConfig = config;
        this.renderModal(config);
        this.loadExistingTranslations(config);
        this.modal.show();
    }

    getConfig(context, button) {
        const baseConfig = {
            context: context,
            button: button,
            badgeClass: 'translation-badge'
        };

        if (context === 'module') {
            const primaryInput = document.getElementById('translationsMainModule');
            const hiddenInput = document.getElementById('translationsInput');

            if (!primaryInput || !hiddenInput) {
                console.error('Module inputs not found');
                return null;
            }

            return {
                ...baseConfig,
                title: 'Переводы названия модуля',
                primaryInput: primaryInput,
                hiddenInput: hiddenInput,
                modalTitleId: 'universalModalTitle'
            };
        } else if (context === 'property') {
            // Ищем родительскую строку свойства
            const row = button.closest('.property-row');
            if (!row) {
                console.error('Property row not found for button:', button);
                return null;
            }

            // Находим индекс строки
            const allRows = document.querySelectorAll('.property-row');
            const index = Array.from(allRows).indexOf(row);

            // Находим нужные элементы внутри строки
            const primaryInput = row.querySelector('input[name="name_property[]"]');
            const hiddenInput = row.querySelector('.translation-field');
            const codeInput = row.querySelector('input[name="code_property[]"]');

            if (!primaryInput || !hiddenInput) {
                console.error('Property inputs not found in row:', row);
                return null;
            }

            return {
                ...baseConfig,
                title: 'Переводы названия свойства',
                primaryInput: primaryInput,
                hiddenInput: hiddenInput,
                modalTitleId: 'universalModalTitle',
                row: row,
                index: index,
                codeInput: codeInput
            };
        }

        console.error('Unknown context:', context);
        return null;
    }

    renderModal(config) {
        // Обновляем заголовок
        const titleElement = document.getElementById(config.modalTitleId);
        if (titleElement) {
            if (config.context === 'property' && config.primaryInput) {
                const propertyName = config.primaryInput.value || 'Новое свойство';
                titleElement.textContent = `${config.title}: ${propertyName}`;
            } else {
                titleElement.textContent = config.title;
            }
        }

        // Заполняем таблицу переводов
        const tbody = document.getElementById('universalTranslationsTableBody');
        if (tbody) {
            tbody.innerHTML = this.languages.map(lang => this.renderLanguageRow(lang, config)).join('');
        }

        // Сохраняем контекст в скрытые поля
        this.setHiddenField('translationContext', config.context);
        this.setHiddenField('translationIndex', config.index || '');
        this.setHiddenField('translationCode', config.codeInput ? config.codeInput.value : '');
    }

    renderLanguageRow(language, config) {
        const isRu = language.code === 'ru';
        const isModule = config.context === 'module';

        return `
                                                <tr>
                                                    <td>
                                                        <span class="flag flag-country-${language.code}"></span>
                                                        <strong>${language.name}</strong>
                                                    </td>
                                                    <td>
                                                        <input type="text" 
                                                               class="form-control universal-translation-input" 
                                                               data-lang="${language.code}"
                                                               placeholder="${isRu ? 'Автоматически из основного поля' : 'Введите перевод...'}"
                                                               ${isRu && !isModule ? 'readonly' : ''}
                                                               style="${isRu && !isModule ? 'background-color: #f8f9fa;' : ''}">
                                                        ${isRu && isModule ? '<small class="text-muted">Изменения здесь автоматически обновят основное поле</small>' : ''}
                                                    </td>
                                                </tr>
                                            `;
    }

    loadExistingTranslations(config) {
        try {
            if (!config.hiddenInput) {
                console.error('Hidden input not found in config:', config);
                return;
            }

            const savedTranslations = config.hiddenInput.value;
            const translations = savedTranslations ? JSON.parse(savedTranslations) : {};

            // Заполняем поля в модальном окне
            document.querySelectorAll('.universal-translation-input').forEach(input => {
                const lang = input.dataset.lang;
                input.value = translations[lang] || '';
            });

            // Синхронизируем русское поле
            this.syncRussianField(config);

        } catch (e) {
            console.error('Error loading translations:', e);
            document.querySelectorAll('.universal-translation-input').forEach(input => {
                input.value = '';
            });
        }
    }

    syncRussianField(config) {
        const ruInput = document.querySelector('.universal-translation-input[data-lang="ru"]');
        if (!ruInput || !config.primaryInput) return;

        if (config.context === 'module') {
            // Для модуля: двусторонняя синхронизация
            ruInput.value = config.primaryInput.value;
        } else {
            // Для свойств: только отображение (readonly)
            ruInput.value = config.primaryInput.value || '';
        }
    }

    saveTranslations() {
        if (!this.currentConfig) {
            console.error('No current config for saving translations');
            return;
        }

        const config = this.currentConfig;
        const translations = {};
        let translationCount = 0;

        // Собираем переводы
        document.querySelectorAll('.universal-translation-input').forEach(input => {
            const lang = input.dataset.lang;
            const value = input.value.trim();

            if (value) {
                translations[lang] = value;
                // Считаем только не-русские переводы для свойств, все переводы для модуля
                if (config.context === 'module' || lang !== 'ru') {
                    translationCount++;
                }
            }
        });

        // Особые правила для разных контекстов
        if (config.context === 'module') {
            this.saveModuleTranslations(config, translations);
        } else if (config.context === 'property') {
            this.savePropertyTranslations(config, translations);
        }

        // Обновляем бейдж
        this.updateBadge(config.button, translationCount);

        // Закрываем модальное окно
        this.modal.hide();
    }

    saveModuleTranslations(config, translations) {
        // Обновляем основное поле из русского перевода
        const ruValue = translations['ru'];
        if (ruValue && config.primaryInput) {
            config.primaryInput.value = ruValue;
        }

        // Сохраняем в скрытое поле
        config.hiddenInput.value = JSON.stringify(translations);
    }

    savePropertyTranslations(config, translations) {
        // Для свойств русский перевод всегда берется из основного поля
        const russianValue = config.primaryInput.value.trim();
        if (russianValue) {
            translations['ru'] = russianValue;
        }

        // Сохраняем в скрытое поле
        config.hiddenInput.value = JSON.stringify(translations);

        // Обновляем имя скрытого поля на основе кода свойства
        if (config.codeInput && config.codeInput.value) {
            config.hiddenInput.name = `translations[properties][${config.codeInput.value}]`;
        }
    }

    handleRealTimeSync(e) {
        // Синхронизация при изменении полей в реальном времени
        if (e.target.name === 'name_property[]') {
            const row = e.target.closest('.property-row');
            if (row) {
                this.updatePropertyRussianTranslation(row);
            }
        } else if (e.target.id === 'translationsMainModule' && this.modal && this.modal._element.classList.contains('show')) {
            this.syncRussianField(this.currentConfig);
        }
    }

    updatePropertyRussianTranslation(row) {
        const primaryInput = row.querySelector('input[name="name_property[]"]');
        const hiddenInput = row.querySelector('.translation-field');
        const button = row.querySelector('.translation-btn');

        if (!primaryInput || !hiddenInput) return;

        try {
            const translations = hiddenInput.value ? JSON.parse(hiddenInput.value) : {};
            const russianValue = primaryInput.value.trim();

            if (russianValue) {
                translations['ru'] = russianValue;
                hiddenInput.value = JSON.stringify(translations);

                // Обновляем бейдж
                const translationCount = Object.keys(translations).filter(lang => lang !== 'ru').length;
                this.updateBadge(button, translationCount);
            }
        } catch (e) {
            console.error('Error updating Russian translation:', e);
        }
    }

    updateBadge(button, count) {
        if (!button) return;

        let badge = button.querySelector('.translation-badge');

        if (!badge && count > 0) {
            badge = document.createElement('span');
            badge.className = 'translation-badge badge bg-success ms-1';
            badge.style.cssText = 'font-size: 0.7em; position: relative; top: -2px;';
            button.appendChild(badge);
        }

        if (badge) {
            badge.textContent = count > 0 ? count : '';
            if (count === 0) {
                badge.remove();
            }
        }
    }

    initializeExistingBadges() {
        // Бейджи для модуля
        const moduleTranslationsInput = document.getElementById('translationsInput');
        const moduleButton = document.querySelector('.translation-btn[data-translation-context="module"]');
        if (moduleTranslationsInput && moduleButton && moduleTranslationsInput.value) {
            try {
                const translations = JSON.parse(moduleTranslationsInput.value);
                const count = Object.keys(translations).length;
                this.updateBadge(moduleButton, count);
            } catch (e) {
                console.error('Error initializing module badge:', e);
            }
        }

        // Бейджи для свойств
        document.querySelectorAll('.property-row').forEach(row => {
            const hiddenInput = row.querySelector('.translation-field');
            const button = row.querySelector('.translation-btn');

            if (hiddenInput && button && hiddenInput.value) {
                try {
                    const translations = JSON.parse(hiddenInput.value);
                    const count = Object.keys(translations).filter(lang => lang !== 'ru').length;
                    this.updateBadge(button, count);
                } catch (e) {
                    console.error('Error initializing property badge:', e);
                }
            }
        });
    }

    setHiddenField(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.value = value || '';
        }
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    new UniversalTranslationManager();
});
