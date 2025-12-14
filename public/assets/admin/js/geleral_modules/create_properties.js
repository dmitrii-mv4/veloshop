document.addEventListener('DOMContentLoaded', function () {
    // Функция для обновления name скрытого поля переводов на основе code_property
    function updateTranslationField(row) {
        const codeInput = row.querySelector('input[name="code_property[]"]');
        const translationField = row.querySelector('.translation-field');

        if (codeInput && translationField) {
            const codeValue = codeInput.value.trim();
            if (codeValue) {
                // Формируем name в формате translations[properties][код_свойства]
                translationField.name = `translations[properties][${codeValue}]`;

                // Обновляем код свойства в модальном окне, если оно открыто для этой строки
                const currentIndex = document.getElementById('currentPropertyIndex');
                const rowIndex = Array.from(document.querySelectorAll('.property-row')).indexOf(row);
                if (currentIndex && currentIndex.value == rowIndex) {
                    document.getElementById('currentPropertyCode').value = codeValue;
                }
            } else {
                // Если код пустой, оставляем пустое имя
                translationField.name = '';
            }
        }
    }

    // Добавление новой строки свойств
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('add-property-row')) {
            const container = document.getElementById('properties-container');
            const rowCount = document.querySelectorAll('.property-row').length;

            const newRow = `
<div class="row push property-row">
    <div class="col-lg-8 col-xl-1">
        <div class="mb-4 row-number">
            ${rowCount + 1}
            <input type="hidden" class="translation-field" name="translations[properties][]" id="translationsInput_${rowCount + 1}" value="">
            <button type="button" class="btn btn-sm btn-alt-secondary translation-btn" 
                    data-translation-context="property">
                <i class="fa fa-language"></i>
            </button>
        </div>
    </div>
    <div class="col-lg-8 col-xl-3">
        <div class="mb-4">
            <input type="text" class="form-control" name="name_property[]" placeholder="Название" value="">
        </div>
    </div>
    <div class="col-lg-8 col-xl-3">
        <div class="mb-4">
            <select class="form-control form-select" name="property[]">
                <option selected disabled>Выберите свойство</option>

                <optgroup label="Текстовые типы">
                    <option value="string" '. (old('property.0') == 'string' ? 'selected' : '') .'>Строка</option>
                    <option value="text" '. (old('property.0') == 'text' ? 'selected' : '') .'>Текст</option>
                </optgroup>

                <optgroup label="Числовые типы">
                    <option value="integer" '. (old('property.0') == 'integer' ? 'selected' : '') .'>Целое число</option>
                    <option value="float" '. (old('property.0') == 'float' ? 'selected' : '') .'>Дробное число</option>
                    <option value="bigint" '. (old('property.0') == 'bigint' ? 'selected' : '') .'>Большие целые числа</option>
                    <option value="decimal" '. (old('property.0') == 'decimal' ? 'selected' : '') .'>Десятичное число</option>
                </optgroup>

                <optgroup label="Специальные типы">
                    <option value="file" '. (old('property.0') == 'string' ? 'selected' : '') .'>Файл</option>
                </optgroup>
            </select>
            
        </div>
    </div>
    <div class="col-lg-8 col-xl-3">
        <div class="mb-4">
            <input type="text" class="form-control code-property-input" name="code_property[]" placeholder="Код" value="">
        </div>
    </div>
    <div class="col-lg-8 col-xl-1">
        <div class="mb-4">
            <button type="button" class="btn btn-danger remove-row">×</button>
        </div>
    </div>
</div>
`;

            container.insertAdjacentHTML('beforeend', newRow);

            // Добавляем обработчик события input для нового поля code_property
            const addedRow = container.lastElementChild;
            const codeInput = addedRow.querySelector('.code-property-input');
            codeInput.addEventListener('input', function () {
                updateTranslationField(addedRow);
            });
        }

        // Удаление строки (только для не-первых строк)
        if (e.target.classList.contains('remove-row')) {
            const row = e.target.closest('.property-row');
            // Проверяем, что это не первая строка
            if (!row.classList.contains('first-row')) {
                row.remove();
                // Обновляем нумерацию
                document.querySelectorAll('.property-row').forEach((row, index) => {
                    row.querySelector('.row-number').textContent = index + 1;
                });
            }
        }
    });

    // Обработчик события input для существующих полей code_property
    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('code-property-input')) {
            const row = e.target.closest('.property-row');
            updateTranslationField(row);
        }
    });

    // Инициализация для уже существующих строк при загрузке страницы
    document.querySelectorAll('.property-row').forEach(row => {
        const codeInput = row.querySelector('input[name="code_property[]"]');
        if (codeInput) {
            codeInput.classList.add('code-property-input');
            updateTranslationField(row);
        }
    });
});