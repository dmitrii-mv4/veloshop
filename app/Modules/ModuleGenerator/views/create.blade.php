@extends('admin::layouts.default')

@section('title', trans('app.modules.module_generator') . ' | KotiksCMS')

@section('content')

    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        
        @include('admin::partials.breadcrumb', [
            'items' => [
                ['url' => route('admin.module_generator.index'), 'title' => trans('app.modules.module_generator')],
                ['title' => trans('app.modules.create')]
            ]
        ])

        <h1 class="h5 mb-0"></h1>
    </div>

    <!-- Действия с модулями -->
    <div class="page-actions fade-in">
        <div>
            <h1 class="h5 mb-0">Создание нового модуля</h1>
        </div>
    </div>
    
    <!-- Форма создания модуля -->
    <div class="card fade-in">       
        <form action="{{ route('admin.module_generator.store') }}" method="POST" enctype="multipart/form-data" id="module-create-form">
            @csrf
            @if($errors->any())
                <div class="alert alert-danger">
                    <h5>Ошибки валидации:</h5>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <!-- Вкладки -->
            <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="main-tab" data-bs-toggle="tab" data-bs-target="#main-content" type="button" role="tab" aria-controls="main-content" aria-selected="true">
                        <i class="bi bi-gear me-2"></i> Основные настройки
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo-content" type="button" role="tab" aria-controls="seo-content" aria-selected="false">
                        <i class="bi bi-search me-2"></i> SEO настройки
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="properties-tab" data-bs-toggle="tab" data-bs-target="#properties-content" type="button" role="tab" aria-controls="properties-content" aria-selected="false">
                        <i class="bi bi-list-check me-2"></i> Свойства модуля
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="translations-tab" data-bs-toggle="tab" data-bs-target="#translations-content" type="button" role="tab" aria-controls="translations-content" aria-selected="false">
                        <i class="bi bi-translate me-2"></i> Переводы
                    </button>
                </li>
            </ul>
            
            <div class="card-body">
                <div class="tab-content">
                    <!-- Основные настройки -->
                    <div class="tab-pane fade show active" id="main-content" role="tabpanel" aria-labelledby="main-tab">
                        <div class="row">
                            <div class="col-lg-8">
                                <!-- Название модуля (русский) -->
                                <div class="mb-4">
                                    <label for="name_ru" class="form-label">
                                        Название модуля (русский)
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('name.ru') is-invalid @enderror" id="name_ru" name="name[ru]" placeholder="Например: Новости, Блог, Каталог" value="{{ old('name.ru', $item->name['ru'] ?? '') }}" autocomplete="off" required>
                                    <div class="form-text">Укажите понятное название для вашего модуля на русском языке</div>
                                    @error('name.ru')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- URL-адрес (slug) -->
                                <div class="mb-4">
                                    <label for="slug-field" class="form-label">
                                        URL-адрес (slug)
                                        <span class="text-danger">*</span>
                                        <i class="bi bi-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Адрес URL для текущего модуля. Используется для маршрутов и URL на стороне сайта"></i>
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug-field" name="slug" placeholder="Будет сгенерирован автоматически" value="{{ old('slug', $item->slug ?? '') }}">
                                        <button type="button" class="btn btn-outline-secondary" id="regenerate-slug">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Только латинские буквы, цифры и дефисы. Будет использоваться в URL</div>
                                    @error('slug')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Код модуля -->
                                <div class="mb-4">
                                    <label for="code_module"  class="form-label">
                                        Код модуля
                                        <span class="text-danger">*</span>
                                        <i class="bi bi-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Это индификатор модуля который используется в URL админ панели и подстановки в качестве значений по умолчанию"></i>
                                    </label>
                                    <input type="text" class="form-control @error('code_module') is-invalid @enderror" id="code_module" name="code_module" placeholder="Например: news" value="{{ old('code_module') }}" required>
                                    <div class="form-text">Только латинские буквы без знаков</div>
                                    @error('code_module')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Описание модуля (русский) -->
                                <div class="mb-4">
                                    <label for="description_ru" class="form-label">Описание модуля (русский)</label>
                                    <textarea class="form-control @error('description.ru') is-invalid @enderror" id="description_ru" name="description[ru]" rows="3" placeholder="Краткое описание функционала модуля на русском языке">{{ old('description.ru', $item->description['ru'] ?? '') }}</textarea>
                                    <div class="form-text">Необязательное поле. Поможет понять назначение модуля</div>
                                    @error('description.ru')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-lg-4">
                                <!-- Быстрые настройки -->
                                <div class="card border-light mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="bi bi-lightning-charge me-2"></i>Быстрые настройки</h6>
                                    </div>
                                    <div class="card-body">
                                        <!-- Включить SEO раздел -->
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" value="1" id="option_seo" name="option_seo" checked>
                                                <label class="form-check-label" for="option_seo">
                                                    Включить SEO раздел
                                                </label>
                                            </div>
                                            <div class="form-text small">Добавит возможность настройки SEO для элементов модуля</div>
                                        </div>
                                        
                                        <!-- Включить категории -->
                                        {{-- <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" value="1" id="option_categories" name="option_categories" checked>
                                                <label class="form-check-label" for="option_categories">
                                                    Включить категории
                                                </label>
                                            </div>
                                            <div class="form-text small">Добавит систему категорий для элементов модуля</div>
                                        </div> --}}

                                        <!-- Включить корзину -->
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" value="1" id="option_trash" name="option_trash" checked>
                                                <label class="form-check-label" for="option_trash">
                                                    Включить корзину
                                                </label>
                                            </div>
                                            <div class="form-text small">Добавит мягкое удаление элементов</div>
                                        </div>
                                        
                                        <!-- Включить теги -->
                                        {{-- <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" value="1" id="option_tags" name="option_tags">
                                                <label class="form-check-label" for="option_tags">
                                                    Включить теги
                                                </label>
                                            </div>
                                            <div class="form-text small">Добавит систему тегов для элементов модуля</div>
                                        </div> --}}
                                        
                                        <!-- Включить комментарии -->
                                        {{-- <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" value="1" id="option_comments" name="option_comments">
                                                <label class="form-check-label" for="option_comments">
                                                    Включить комментарии
                                                </label>
                                            </div>
                                            <div class="form-text small">Добавит систему комментариев для элементов модуля</div>
                                        </div> --}}
                                    </div>
                                </div>
                                
                                <!-- Статус модуля -->
                                <div class="mb-4">
                                    <label class="form-label">Статус модуля</label>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="status" id="status-active" value="1" checked>
                                        <label class="form-check-label" for="status-active">
                                            <span class="badge bg-success me-1">Активен</span> Модуль будет доступен сразу
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status-inactive" value="0">
                                        <label class="form-check-label" for="status-inactive">
                                            <span class="badge bg-warning me-1">Неактивен</span> Модуль будет отключен
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- SEO настройки -->
                    <div class="tab-pane fade" id="seo-content" role="tabpanel" aria-labelledby="seo-tab">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Эти настройки будут использоваться как шаблоны для SEO-данных элементов модуля
                                </div>
                                
                                <!-- Настройки для элементов модуля -->
                                <h6 class="mb-3">Настройки для элементов модуля</h6>
                                
                                <!-- Шаблон META TITLE -->
                                <div class="mb-4">
                                    <label for="meta_title" class="form-label">
                                        Шаблон META TITLE
                                        <i class="bi bi-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Заголовок во вкладке браузера. Можно использовать переменные: {title}, {category}, {site_name}"></i>
                                    </label>
                                    <textarea name="meta_title" class="form-control @error('meta_title') is-invalid @enderror" id="meta_title" rows="2">{{ old('meta_title', $item->meta_title ?? '{title} | {site_name}') }}</textarea>
                                    <div class="form-text">Используйте переменные для автоматической подстановки: {title}, {category}, {site_name}</div>
                                    @error('meta_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Шаблон META KEYWORDS -->
                                <div class="mb-4">
                                    <label for="meta_keywords" class="form-label">Шаблон META KEYWORDS</label>
                                    <textarea name="meta_keywords" class="form-control @error('meta_keywords') is-invalid @enderror" id="meta_keywords" rows="2">{{ old('meta_keywords', $item->meta_keywords ?? '{title}, {category}') }}</textarea>
                                    <div class="form-text">Ключевые слова через запятую. Переменные: {title}, {category}, {tags}</div>
                                    @error('meta_keywords')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Шаблон META DESCRIPTION -->
                                <div class="mb-4">
                                    <label for="meta_description" class="form-label">Шаблон META DESCRIPTION</label>
                                    <textarea name="meta_description" class="form-control @error('meta_description') is-invalid @enderror" id="meta_description" rows="2">{{ old('meta_description', $item->meta_description ?? '') }}</textarea>
                                    <div class="form-text">Краткое описание для поисковых систем. Переменные: {title}, {category}, {excerpt}</div>
                                    @error('meta_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <h6 class="mb-3 mt-4">Настройки для изображений модуля</h6>
                                
                                <!-- Шаблон ALT для изображений -->
                                <div class="mb-4">
                                    <label for="meta_img_alt" class="form-label">Шаблон ALT для изображений</label>
                                    <textarea name="meta_img_alt" class="form-control @error('meta_img_alt') is-invalid @enderror" id="meta_img_alt" rows="2">{{ old('meta_img_alt', $item->meta_img_alt ?? '{title}') }}</textarea>
                                    <div class="form-text">ALT-текст для изображений. Переменные: {title}, {category}</div>
                                    @error('meta_img_alt')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Шаблон TITLE для изображений -->
                                <div class="mb-4">
                                    <label for="meta_img_title" class="form-label">Шаблон TITLE для изображений</label>
                                    <textarea name="meta_img_title" class="form-control @error('meta_img_title') is-invalid @enderror" id="meta_img_title" rows="2">{{ old('meta_img_title', $item->meta_img_title ?? '{title}') }}</textarea>
                                    <div class="form-text">TITLE для изображений. Переменные: {title}, {category}</div>
                                    @error('meta_img_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-lg-4">
                                <!-- SEO превью -->
                                <div class="card border-light mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="bi bi-eye me-2"></i>Превью SEO</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="seo-preview mb-3">
                                            <div class="seo-title-preview text-primary mb-1" id="seo-title-preview">Заголовок страницы</div>
                                            <div class="seo-url-preview text-success small mb-2" id="seo-url-preview">https://example.com/slug</div>
                                            <div class="seo-description-preview text-muted small" id="seo-description-preview">Описание страницы для поисковых систем...</div>
                                        </div>
                                        <div class="form-text small">Предварительный просмотр того, как может выглядеть страница в поисковой выдаче</div>
                                    </div>
                                </div>
                                
                                <!-- Доступные переменные -->
                                <div class="card border-light">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="bi bi-code me-2"></i>Доступные переменные</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled mb-0">
                                            <li><code>{title}</code> - Название элемента</li>
                                            <li><code>{category}</code> - Название категории</li>
                                            <li><code>{site_name}</code> - Название сайта</li>
                                            <li><code>{excerpt}</code> - Краткое описание</li>
                                            <li><code>{tags}</code> - Теги элемента</li>
                                            <li><code>{date}</code> - Дата публикации</li>
                                            <li><code>{author}</code> - Автор элемента</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Свойства модуля -->
                    <div class="tab-pane fade" id="properties-content" role="tabpanel" aria-labelledby="properties-tab">
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info mb-4">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Добавьте свойства (поля) для вашего модуля. Эти поля будут использоваться для хранения данных элементов модуля
                                </div>
                                
                                <!-- Контейнер для свойств -->
                                <div id="properties-container">
                                    <div class="property-item card border-light mb-3">
                                        <div class="card-body">
                                            <div class="row g-3 align-items-center">
                                                <div class="col-md-3">
                                                    <label class="form-label mb-0">Название свойства (русский)</label>
                                                    <input type="text" class="form-control property-name-ru" name="properties[0][name][ru]" placeholder="Например: Цена, Описание, Автор" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label mb-0">Тип данных</label>
                                                    <select class="form-select property-type" name="properties[0][type]" required>
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
                                                        <input type="text" class="form-control property-code" name="properties[0][code]" placeholder="Например: price, description" required>
                                                        <button type="button" class="btn btn-outline-secondary generate-code-btn">
                                                            <i class="bi bi-magic"></i>
                                                        </button>
                                                    </div>
                                                    <div class="form-text small">Только латинские буквы, цифры и подчеркивания</div>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label mb-0">Обязательное поле</label>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="properties[0][required]">
                                                        <label class="form-check-label">Да</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <label class="form-label mb-0">Действия</label>
                                                    <button type="button" class="btn btn-danger btn-sm remove-property" disabled>
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Кнопка добавления свойства -->
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <button type="button" class="btn btn-outline-primary" id="add-property">
                                        <i class="bi bi-plus-circle me-2"></i>Добавить свойство
                                    </button>
                                    
                                    <div class="text-muted small" id="property-count">Добавлено 1 свойство</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Переводы -->
                    <div class="tab-pane fade" id="translations-content" role="tabpanel" aria-labelledby="translations-tab">
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info mb-4">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Настройте переводы для мультиязычной поддержки модуля. Все поля будут доступны на указанных языках.
                                </div>
                                
                                <!-- Вкладки для языков -->
                                <ul class="nav nav-pills mb-4" id="langTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="lang-ru-tab" data-bs-toggle="pill" data-bs-target="#lang-ru" type="button" role="tab" aria-controls="lang-ru" aria-selected="true">
                                            Русский (ru)
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="lang-en-tab" data-bs-toggle="pill" data-bs-target="#lang-en" type="button" role="tab" aria-controls="lang-en" aria-selected="false">
                                            Английский (en)
                                        </button>
                                    </li>
                                </ul>
                                
                                <div class="tab-content" id="langTabsContent">
                                    <!-- Русский язык -->
                                    <div class="tab-pane fade show active" id="lang-ru" role="tabpanel" aria-labelledby="lang-ru-tab">
                                        <p class="text-muted mb-3">Русские переводы уже заполнены в основных настройках</p>
                                        
                                        <!-- Название модуля на русском -->
                                        <div class="mb-3">
                                            <label class="form-label">Название модуля (русский)</label>
                                            <input type="text" class="form-control" id="translation_name_ru" name="name[ru]" value="{{ old('name.ru', $item->name['ru'] ?? '') }}" readonly>
                                        </div>
                                        
                                        <!-- Описание модуля на русском -->
                                        <div class="mb-3">
                                            <label class="form-label">Описание модуля (русский)</label>
                                            <textarea class="form-control" id="translation_description_ru" rows="3" readonly>{{ old('description.ru', $item->description['ru'] ?? '') }}</textarea>
                                        </div>
                                        
                                        <!-- Свойства модуля на русском -->
                                        <div class="mb-3">
                                            <label class="form-label">Свойства модуля (русский)</label>
                                            <div class="properties-translations-container" data-lang="ru">
                                                <!-- Динамически заполняется JavaScript -->
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Английский язык -->
                                    <div class="tab-pane fade" id="lang-en" role="tabpanel" aria-labelledby="lang-en-tab">
                                        <!-- Название модуля на английском -->
                                        <div class="mb-3">
                                            <label for="name_en" class="form-label">Название модуля (английский)</label>
                                            <input type="text" class="form-control @error('name.en') is-invalid @enderror" id="name_en" name="name[en]" placeholder="Module name in English" value="{{ old('name.en', $item->name['en'] ?? '') }}">
                                            @error('name.en')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <!-- Описание модуля на английском -->
                                        <div class="mb-3">
                                            <label for="description_en" class="form-label">Описание модуля (английский)</label>
                                            <textarea class="form-control @error('description.en') is-invalid @enderror" id="description_en" name="description[en]" rows="3" placeholder="Module description in English">{{ old('description.en', $item->description['en'] ?? '') }}</textarea>
                                            @error('description.en')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <!-- Свойства модуля на английском -->
                                        <div class="mb-3">
                                            <label class="form-label">Свойства модуля (английский)</label>
                                            <div class="properties-translations-container" data-lang="en">
                                                <!-- Динамически заполняется JavaScript -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Кнопки формы -->
                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <div>
                        <a href="{{ route('admin.module_generator.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Назад к списку
                        </a>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-plus-circle me-2"></i>Создать модуль
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @endsection