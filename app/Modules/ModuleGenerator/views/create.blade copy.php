@extends('admin.layouts.default')

@section('content')

    <!-- Hero -->
    <div class="content">
        <div
            class="d-md-flex justify-content-md-between align-items-md-center py-3 pt-md-3 pb-md-0 text-center text-md-start">
            <div>
                <h1 class="h3 mb-1">{{ trans('app.modules.name') }}</h1>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Хлебные крошки -->
    <div class="content">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ trans('app.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a
                        href="{{ route('admin.module_generator.index') }}">{{ trans('app.modules.name') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ trans('app.modules.create') }}</li>
            </ol>
        </nav>
    </div>

    <!-- Page Content -->
    <div class="content">

        <form action="{{ route('admin.module_generator.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="block block-rounded">
                <ul class="nav nav-tabs nav-tabs-block" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link active" id="main-tab" data-bs-toggle="tab" data-bs-target="#main-classic" role="tab" aria-controls="main" aria-selected="true">
                        Модуль
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" role="tab" aria-controls="seo" aria-selected="false" tabindex="-1">
                        SEO
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link" id="properties-tab" data-bs-toggle="tab" data-bs-target="#properties" role="tab" aria-controls="properties" aria-selected="false" tabindex="-1">
                        Свойства
                        </button>
                    </li>
                </ul>
                <div class="block-content tab-content overflow-hidden">
                    <!-- Main -->
                    <div class="tab-pane fade show active" id="main-classic" role="tabpanel" aria-labelledby="main-tab" tabindex="0">
                        <div class="row">

                            <div class="block block-rounded">
                                <div class="block-content">
                                    
                                    <!-- Name module -->
                                    <div class="row push">
                                        <div class="col-lg-3">
                                            <label class="form-label" for="example-text-input">Название модуля:</label>
                                        </div>
                                        <div class="col-lg-8 col-xl-5">
                                            <div class="mb-4">
                                                <input type="text" 
                                                    class="@error('name') is-invalid @enderror form-control" 
                                                    id="name" 
                                                    name="name" 
                                                    placeholder="Например: Новости"
                                                    value="{{ old('name', $item->name ?? '') }}"
                                                    autocomplete="off">
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Slug -->
                                    <div class="row push">
                                        <div class="col-lg-3">
                                            <i class="fa-solid fa-circle-question"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                title="Адрес URL для текущего модуля."
                                                style="cursor: pointer; margin-top: 10px; color: #6c757d;">
                                            </i>
                                            <label class="form-label" for="example-text-input">URL-адрес:</label>
                                        </div>
                                        <div class="col-lg-8 col-xl-5">
                                            <div class="mb-4">
                                                <!-- Группа ввода с кнопкой -->
                                                <div class="input-group">
                                                    <input type="text" 
                                                        class="@error('slug') is-invalid @enderror form-control" 
                                                        id="slug-field" 
                                                        name="slug" 
                                                        placeholder="Будет сгенерирован автоматически"
                                                        value="{{ old('slug', $item->slug ?? '') }}">
                                                    <!-- Кнопка будет добавлена автоматически JS -->
                                                </div>
                                                
                                                @error('slug')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- SEO section -->
                                    <div class="row push">
                                        <div class="col-lg-3">
                                            <label class="form-label" for="example-text-input">Включить SEO раздел:</label>
                                        </div>
                                        <div class="col-lg-8 col-xl-5">
                                            <div class="mb-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" value="1" id="section_seo" name="section_seo" checked>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Categories section -->
                                    <div class="row push">
                                        <div class="col-lg-3">
                                            <label class="form-label" for="example-text-input">Включить категории:</label>
                                        </div>
                                        <div class="col-lg-8 col-xl-5">
                                            <div class="mb-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" value="1" id="section_categories" name="section_categories" checked>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        
                        </div>
                    </div>
                    <!-- END Main -->

                    <!-- SEO -->
                    <div class="tab-pane fade show" id="seo" role="tabpanel" aria-labelledby="seo-tab" tabindex="0">
                        <div class="row">

                            <div class="block block-rounded">
                                <div class="block-content">

                                    <div>Настройки для элементов модуля</div>
                                    
                                    <!-- Basic Elements -->
                                    <div class="row push">
                                        <div class="col-lg-3">
                                            <i class="fa-solid fa-circle-question"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                title="Заголовк в вкладке браузера."
                                                style="cursor: pointer; margin-top: 10px; color: #6c757d;">
                                            </i>

                                            <label class="form-label" for="example-text-input">Шаблон META TITLE:</label>
                                        </div>
                                        <div class="col-lg-8 col-xl-5">
                                            <div class="mb-4">
                                                <textarea name="meta_title" class="@error('meta_title') is-invalid @enderror form-control" rows="1">{{ old('meta_title') }}</textarea>
                                                @error('meta_title')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row push">
                                        <div class="col-lg-3">
                                            <label class="form-label" for="example-text-input">Шаблон META KEYWORDS:</label>
                                        </div>
                                        <div class="col-lg-8 col-xl-5">
                                            <div class="mb-4">
                                                <textarea name="meta_keywords" class="@error('meta_keywords') is-invalid @enderror form-control" rows="1">{{ old('meta_keywords') }}</textarea>
                                                @error('meta_keywords')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row push">
                                        <div class="col-lg-3">
                                            <label class="form-label" for="example-text-input">Шаблон META DESCRIPTION:</label>
                                        </div>
                                        <div class="col-lg-8 col-xl-5">
                                            <div class="mb-4">
                                                <textarea name="meta_description" class="@error('meta_description') is-invalid @enderror form-control" rows="1">{{ old('meta_description') }}</textarea>
                                                @error('meta_description')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div>Настройки для изображений модуля</div>

                                    <div class="row push">
                                        <div class="col-lg-3">
                                            <label class="form-label" for="example-text-input">Шаблон ALT:</label>
                                        </div>
                                        <div class="col-lg-8 col-xl-5">
                                            <div class="mb-4">
                                                <textarea name="meta_img_alt" class="@error('meta_img_alt') is-invalid @enderror form-control" rows="1">{{ old('meta_img_alt') }}</textarea>
                                                @error('meta_img_alt')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row push">
                                        <div class="col-lg-3">
                                            <label class="form-label" for="example-text-input">Шаблон TITLE:</label>
                                        </div>
                                        <div class="col-lg-8 col-xl-5">
                                            <div class="mb-4">
                                                <textarea name="meta_img_title" class="@error('meta_img_title') is-invalid @enderror form-control" rows="1">{{ old('meta_img_title') }}</textarea>
                                                @error('meta_img_title')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        
                        </div>
                    </div>
                    <!-- END Main -->

                    <!-- Properties -->
                    <div class="tab-pane fade" id="properties" role="tabpanel" aria-labelledby="properties-tab" tabindex="0">
                        <div class="row g-sm push">
                            <div class="block-content">

                                <!-- Контейнер для строк -->
                                <div id="properties-container">
                                    <div class="row push property-row first-row">
                                        <div class="col-lg-8 col-xl-1">
                                            <div class="mb-4 row-number">
                                                1
                                            </div>
                                        </div>

                                        <div class="col-lg-8 col-xl-3">
                                            <div class="mb-4">
                                                <input type="text" class="form-control" 
                                                    name="name_property" placeholder="Название" 
                                                    value="">
                                            </div>
                                        </div>

                                        <div class="col-lg-8 col-xl-3">
                                            <div class="mb-4">
                                                <select class="form-control form-select" name="property[]" aria-label="Floating label select example">
                                                    <option selected disabled>Выберите свойство</option>

                                                    <optgroup label="Текстовые типы">
                                                        <option value="string">Строка</option>
                                                        <option value="text">Текст</option>
                                                    </optgroup>

                                                    <optgroup label="Числовые типы">
                                                        <option value="integer">Целое число</option>
                                                        <option value="float">Дробное число</option>
                                                        <option value="bigint">Большие целые числа</option>
                                                        <option value="decimal">Десятичное число</option>
                                                    </optgroup>

                                                    {{-- <optgroup label="Специальные типы">
                                                        <option value="file">Файл</option>
                                                    </optgroup> --}}

                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-8 col-xl-3">
                                            <div class="mb-4">
                                                <input type="text" class="form-control code-property-input" name="code_property" placeholder="Код" value="">
                                            </div>
                                        </div>
                                        <div class="col-lg-8 col-xl-1">
                                            <i class="fa-solid fa-circle-question"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                title="Используется при создании в названии БД таблицах. Используйте только английские слова без символов и пробелов."
                                                style="cursor: pointer; margin-top: 10px; color: #6c757d;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END Prermissions -->

                    <button class="btn btn-alt-success me-1 mb-3">
                        <i class="fa fa-fw fa-plus opacity-50 me-1"></i> Добавить
                    </button>

                </div>
            </div>

        </form>

    </div>
    <!-- END Page Content -->

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const nameField = document.getElementById('name');
    const slugField = document.getElementById('slug-field');
    
    if (!nameField || !slugField) return;
    
    let isSlugManuallyChanged = slugField.value.length > 0;
    
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
    
    function generateSlugFromName() {
        if (!isSlugManuallyChanged || slugField.value === '') {
            const nameValue = nameField.value.trim();
            if (nameValue) {
                slugField.value = slugify(nameValue);
            }
        }
    }
    
    slugField.addEventListener('input', function() {
        isSlugManuallyChanged = this.value.length > 0;
    });
    
    let nameTimeout;
    nameField.addEventListener('input', function() {
        clearTimeout(nameTimeout);
        nameTimeout = setTimeout(generateSlugFromName, 300);
    });
    
    nameField.addEventListener('blur', function() {
        if (slugField.value === '' && nameField.value.trim()) {
            generateSlugFromName();
        }
    });
    
    function createRegenerateButton() {
        const existingButton = slugField.parentNode.querySelector('.slug-regenerate-btn');
        if (existingButton) return;
        
        const container = slugField.closest('.input-group') || slugField.parentNode;
        
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-outline-secondary btn-sm slug-regenerate-btn';
        button.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Перегенерировать';
        button.title = 'Сгенерировать slug из текущего названия';
        
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (!nameField.value.trim()) {
                alert('Введите название для генерации slug');
                return;
            }
            
            const newSlug = slugify(nameField.value);
            slugField.value = newSlug;
            isSlugManuallyChanged = true;
        });
        
        if (container.classList.contains('input-group')) {
            const buttonWrapper = document.createElement('span');
            buttonWrapper.className = 'input-group-text p-0 border-0';
            buttonWrapper.appendChild(button);
            container.appendChild(buttonWrapper);
        } else {
            const inputGroup = document.createElement('div');
            inputGroup.className = 'input-group';
            
            container.insertBefore(inputGroup, slugField);
            inputGroup.appendChild(slugField);
            
            const buttonWrapper = document.createElement('span');
            buttonWrapper.className = 'input-group-text p-0 border-0';
            buttonWrapper.appendChild(button);
            inputGroup.appendChild(buttonWrapper);
        }
    }
    
    createRegenerateButton();
});
    </script>

@endsection