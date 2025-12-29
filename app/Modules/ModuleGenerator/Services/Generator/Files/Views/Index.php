<?php

namespace App\Modules\ModuleGenerator\Services\Generator\Files\Views;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для генерации index view модуля
 * Генерирует основной список записей модуля в админке
 * 
 * @param array $moduleData Настройки модулей
 * @param string $moduleViewsFullPath абсолютный путь к директории views модуля
 */
class Index
{
    protected $moduleData;
    protected $moduleViewsFullPath;

    public function __construct($moduleData, $moduleViewsFullPath)
    {
        $this->moduleData = $moduleData;
        $this->moduleViewsFullPath = $moduleViewsFullPath;
    }

    /**
     * Генерация index view модуля
     * 
     * Создает index.blade.php файл и возвращает его имя в формате "module::index"
     * 
     * @return string Имя view в формате {code_module}::index
     */
    public function generate()
    {
        try {
            Log::info('Начало генерации index view модуля', [
                'module' => $this->moduleData['code_module']
            ]);

            // Генерируем index view
            $this->createIndexView();

            // Формируем имя view для возврата
            $viewName = $this->moduleData['code_module'] . "::index";
            
            Log::info('Успешное завершение генерации index view модуля', [
                'module' => $this->moduleData['code_module'],
                'view_name' => $viewName
            ]);

            return $viewName;

        } catch (\Exception $e) {
            Log::error('Критическая ошибка при генерации index view модуля', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \RuntimeException("Ошибка генерации index view: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Создаёт index view модуля
     * 
     * Генерирует файл index.blade.php с динамическим содержимым
     */
    public function createIndexView()
    {
        try {
            $moduleCode = $this->moduleData['code_module'];
            $moduleName = $this->moduleData['code_name'];
            $moduleNameRu = $this->moduleData['mod_name']['ru'] ?? $moduleName;
            
            Log::info('Создание index view модуля', [
                'module' => $moduleCode,
                'module_name' => $moduleName,
                'module_name_ru' => $moduleNameRu
            ]);
            
            // Формируем путь к файлу view
            $viewPathFile = $this->moduleViewsFullPath . '/index.blade.php';
            
            // Проверяем существование файла
            if (File::exists($viewPathFile)) {
                Log::warning('Файл index view уже существует, будет перезаписан', [
                    'file_path' => $viewPathFile
                ]);
            }
            
            // Определяем поля для отображения
            $titleField = $this->getTitleField();
            $descriptionField = $this->getDescriptionField();
            $hasTrash = isset($this->moduleData['option']['trash']) && $this->moduleData['option']['trash'];
            $hasSeo = isset($this->moduleData['option']['seo']) && $this->moduleData['option']['seo'];
            
            // Формируем контент view
            $content = $this->generateViewContent($moduleCode, $moduleName, $moduleNameRu, 
                $titleField, $descriptionField, $hasTrash, $hasSeo);
            
            // Записываем изменения в файл
            File::put($viewPathFile, $content);
            
            Log::info('Файл index view успешно сгенерирован', [
                'file_path' => $viewPathFile,
                'module' => $moduleCode,
                'title_field' => $titleField,
                'description_field' => $descriptionField,
                'has_trash' => $hasTrash,
                'has_seo' => $hasSeo
            ]);
            
            return true;

        } catch (\Exception $e) {
            Log::error('Ошибка при создании index view модуля', [
                'module' => $this->moduleData['code_module'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception("Ошибка создания index view: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Получает поле для заголовка (первое строковое поле)
     */
    protected function getTitleField()
    {
        foreach ($this->moduleData['properties'] as $property) {
            if ($property['type'] === 'string' || $property['type'] === 'text') {
                return $property['code'];
            }
        }
        
        // Если не нашли, возвращаем первое поле
        if (!empty($this->moduleData['properties'])) {
            return $this->moduleData['properties'][0]['code'];
        }
        
        return 'name';
    }

    /**
     * Получает поле для описания (первое текстовое поле)
     */
    protected function getDescriptionField()
    {
        foreach ($this->moduleData['properties'] as $property) {
            if ($property['type'] === 'text') {
                return $property['code'];
            }
        }
        
        return null;
    }

    /**
     * Генерирует содержимое view файла
     */
    protected function generateViewContent($moduleCode, $moduleName, $moduleNameRu, 
        $titleField, $descriptionField, $hasTrash, $hasSeo)
    {
        $content = <<<HTML
@extends('admin::layouts.default')

@section('title', '{$moduleNameRu} | KotiksCMS')
    
@section('content')

    <!-- Заголовок страницы -->
    <div class="page-header fade-in">
        @include('admin::partials.breadcrumb', [
            'items' => [['title' => '{$moduleNameRu}']],
        ])
    </div>
    
HTML;

        // Добавляем вкладки, если есть корзина
        if ($hasTrash) {
            $content .= <<<HTML
        <!-- Вкладки: Активные и Корзина -->
        <div class="d-flex mb-4 fade-in">
            <div class="btn-group" role="group" aria-label="Module tabs">
                <a href="{{ route('admin.{$moduleCode}.index') }}" class="btn btn-primary">
                    <i class="bi bi-newspaper me-1"></i> {{ module_trans('{$moduleCode}', 'mod_name') }}
                </a>
                <a href="{{ route('admin.{$moduleCode}.trash.index') }}" class="btn btn-outline-primary position-relative">
                    <i class="bi bi-trash me-1"></i> Корзина
                    @if(\$trashedCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ \$trashedCount }}
                        <span class="visually-hidden">записей в корзине</span>
                    </span>
                    @endif
                </a>
            </div>
        </div>

HTML;
        }

        $content .= <<<HTML
        <!-- Действия с модулем -->
        <div class="page-actions fade-in">
            <div>
                <h1 class="h5 mb-0">{{ module_trans('{$moduleCode}', 'mod_name') }}</h1>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">
                    Всего: {{ \$totalItems }} | В корзине: {{ \$trashedCount ?? 0 }}
                </p>
            </div>
            <a href="{{ route('admin.{$moduleCode}.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Добавить запись
            </a>
        </div>

        <!-- Карточка с фильтрами -->
        <div class="card fade-in mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('admin.{$moduleCode}.index') }}" class="row g-2 filter-form">
                    <!-- Поиск -->
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" value="{{ \$search }}" class="form-control"
                                placeholder="Поиск по названию, описанию или slug..." 
                                aria-label="Поиск">
                        </div>
                    </div>

                    <!-- Сортировка -->
                    <div class="col-md-2">
                        <select name="sort_by" class="form-select form-select-sm" aria-label="Сортировка">
                            <option value="created_at" {{ \$sortBy == 'created_at' ? 'selected' : '' }}>Дата создания</option>
                            <option value="{$titleField}" {{ \$sortBy == '{$titleField}' ? 'selected' : '' }}>{$titleField}</option>
                            <option value="updated_at" {{ \$sortBy == 'updated_at' ? 'selected' : '' }}>Дата обновления</option>
                        </select>
                    </div>

                    <!-- Порядок сортировки -->
                    <div class="col-md-2">
                        <select name="sort_order" class="form-select form-select-sm" aria-label="Порядок сортировки">
                            <option value="desc" {{ \$sortOrder == 'desc' ? 'selected' : '' }}>По убыванию</option>
                            <option value="asc" {{ \$sortOrder == 'asc' ? 'selected' : '' }}>По возрастанию</option>
                        </select>
                    </div>

                    <!-- Количество на странице -->
                    <div class="col-md-2">
                        <select name="per_page" class="form-select form-select-sm" aria-label="Записей на странице">
                            @foreach ([5, 10, 25, 50] as \$count)
                                <option value="{{ \$count }}" {{ \$perPage == \$count ? 'selected' : '' }}>
                                    {{ \$count }} на странице
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Кнопки фильтрации -->
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill">
                            <i class="bi bi-funnel me-1"></i> Применить
                        </button>
                        <a href="{{ route('admin.{$moduleCode}.index') }}" class="btn btn-outline-secondary btn-sm" aria-label="Сбросить фильтры">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Список записей -->
        <div class="card fade-in">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Список записей</h5>
                    <div class="text-muted small">
                        Показано {{ \$items->count() }} из {{ \$items->total() }} записей
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="40%">
                                    <a href="{{ route('admin.{$moduleCode}.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => '{$titleField}', 'sort_order' => \$sortBy == '{$titleField}' && \$sortOrder == 'asc' ? 'desc' : 'asc'])) }}"
                                        class="text-decoration-none d-flex align-items-center">
                                        {$titleField}
                                        @if (\$sortBy == '{$titleField}')
                                            <i class="bi bi-chevron-{{ \$sortOrder == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @endif
                                    </a>
                                </th>
HTML;

        // Добавляем колонку Slug если включена SEO
        if ($hasSeo) {
            $content .= <<<HTML
                                <th width="15%">Slug</th>
HTML;
        }

        $content .= <<<HTML
                                <th width="15%">Автор</th>
                                <th width="15%">Обновлено</th>
                                <th width="15%">Создано</th>
                                <th width="15%" class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(\$items as \$item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="module-icon me-3">
                                                <i class="bi bi-newspaper"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ \$item->{$titleField} }}</div>
HTML;

        // Добавляем описание если есть поле
        if ($descriptionField) {
            $content .= <<<HTML
                                                @if(\$item->{$descriptionField})
                                                    <div class="text-muted small mt-1" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                        {{ strip_tags(\$item->{$descriptionField}) }}
                                                    </div>
                                                @endif
HTML;
        }

        $content .= <<<HTML
                                            </div>
                                        </div>
                                    </td>
HTML;

        // Добавляем ячейку Slug если включена SEO
        if ($hasSeo) {
            $content .= <<<HTML
                                    <td>
                                        <code class="bg-light p-1 rounded">{{ \$item->slug }}</code>
                                    </td>
HTML;
        }

        $content .= <<<HTML
                                    <td>
                                        @if(\$item->author)
                                            <div class="text-muted small">
                                                <i class="bi bi-person-circle me-1"></i>
                                                {{ \$item->author->name }}
                                            </div>
                                        @else
                                            <span class="text-muted small">Система</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-muted small">
                                            {{ \$item->updated_at->format('d.m.Y H:i') }}
                                            <div class="text-muted">
                                                {{ \$item->updated_at->diffForHumans() }}
                                            </div>
                                    </td>
                                    <td>
                                        <div class="text-muted small">
                                            {{ \$item->created_at->format('d.m.Y H:i') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="{{ route('admin.{$moduleCode}.edit', \$item->id) }}"
                                                class="btn btn-outline-primary btn-sm me-1" title="Редактировать"
                                                aria-label="Редактировать {{ \$item->{$titleField} }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger btn-sm delete-btn"
                                                title="В корзину" 
                                                data-id="{{ \$item->id }}"
                                                data-title="{{ \$item->{$titleField} }}"
                                                data-url="{{ route('admin.{$moduleCode}.destroy', \$item->id) }}"
                                                aria-label="Удалить {{ \$item->{$titleField} }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="
HTML;

        // Вычисляем количество колонок на этапе генерации
        $colspan = 5; // Базовое количество: название, автор, обновлено, создано, действия
        if ($hasSeo) {
            $colspan++; // Добавляем колонку slug
        }
        
        $content .= $colspan . <<<HTML
" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-newspaper fs-4"></i>
                                            <p class="mt-2">Записи не найдены</p>
                                            @if (request()->has('search'))
                                                <a href="{{ route('admin.{$moduleCode}.index') }}" class="btn btn-primary btn-sm mt-2">
                                                    <i class="bi bi-arrow-clockwise me-1"></i> Сбросить фильтры
                                                </a>
                                            @else
                                                <a href="{{ route('admin.{$moduleCode}.create') }}"
                                                    class="btn btn-primary btn-sm mt-2">
                                                    <i class="bi bi-plus-circle me-1"></i> Добавить первую запись
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Пагинация -->
            @if (\$items->hasPages())
                <div class="card-footer border-0 bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Показано {{ \$items->firstItem() }} - {{ \$items->lastItem() }} из {{ \$items->total() }}
                        </div>
                        <div>
                            <nav aria-label="Навигация по страницам">
                                <ul class="pagination pagination-sm mb-0">
                                    {{ \$items->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            @endif
        </div>

HTML;

        // Добавляем блок SEO статистики если включена SEO опция
        if ($hasSeo) {
            $content .= <<<HTML
        <!-- SEO статистика -->
        <div class="card mb-4 fade-in">
            <div class="card-header">
                <h6 class="card-title mb-0"><i class="bi bi-search me-2"></i> SEO-статистика</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 p-3 rounded me-3">
                                <i class="bi bi-tag fs-4 text-primary"></i>
                            </div>
                            <div>
                                <h5 class="mb-1" id="seo-titles-count">0</h5>
                                <p class="text-muted mb-0">Заполнено заголовков</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 p-3 rounded me-3">
                                <i class="bi bi-text-paragraph fs-4 text-success"></i>
                            </div>
                            <div>
                                <h5 class="mb-1" id="seo-descriptions-count">0</h5>
                                <p class="text-muted mb-0">Заполнено описаний</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-info bg-opacity-10 p-3 rounded me-3">
                                <i class="bi bi-key fs-4 text-info"></i>
                            </div>
                            <div>
                                <h5 class="mb-1" id="seo-keywords-count">0</h5>
                                <p class="text-muted mb-0">Заполнено ключевых слов</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-muted small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        SEO-оптимизация помогает улучшить видимость в поисковых системах. Заполняйте мета-теги для каждой записи.
                    </p>
                </div>
            </div>
        </div>

HTML;
        }

        // Добавляем информационную панель
        $content .= <<<HTML
        <!-- Информационная панель -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i> О модуле</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2" style="font-size: 0.85rem;">
                            В этом разделе вы можете управлять всеми записями модуля "{{ module_trans('{$moduleCode}', 'mod_name') }}".
                        </p>
                        <ul class="mb-0" style="font-size: 0.85rem;">
                            <li>Создавайте и редактируйте записи</li>
HTML;

        // Добавляем пункты в зависимости от опций на этапе генерации
        if ($hasTrash) {
            $content .= <<<HTML
                            <li>Используйте мягкое удаление для временного хранения записей</li>
                            <li>Восстанавливайте записи из корзины в течение 30 дней</li>
HTML;
        }

        if ($hasSeo) {
            $content .= <<<HTML
                            <li>Управляйте SEO-параметрами для каждой записи</li>
HTML;
        }

        $content .= <<<HTML
                            <li>Используйте REST API для интеграции с внешними системами</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Блок API (теперь всегда отображается) -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0"><i class="bi bi-code-slash me-2"></i> API</h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- API записей -->
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-grow-1">
                                <div class="fw-semibold mb-1" style="font-size: 0.85rem;">
                                    <i class="bi bi-link-45deg me-1"></i> API записей
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <code class="p-2 bg-light rounded small api-endpoint flex-grow-1" title="{{ url('api/{$moduleCode}') }}">
                                        {{ url('api/{$moduleCode}') }}
                                    </code>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-outline-secondary btn-sm copy-btn" 
                                                data-clipboard-text="{{ url('api/{$moduleCode}') }}"
                                                title="Копировать URL API"
                                                aria-label="Копировать URL API">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- API отдельных записей -->
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-grow-1">
                                <div class="fw-semibold mb-1" style="font-size: 0.85rem;">
                                    <i class="bi bi-link-45deg me-1"></i> API записи по ID
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <code class="p-2 bg-light rounded small api-endpoint flex-grow-1" title="{{ url('api/{$moduleCode}') }}/[id]">
                                        {{ url('api/{$moduleCode}') }}/[id]
                                    </code>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-outline-secondary btn-sm copy-btn" 
                                                data-clipboard-text="{{ url('api/{$moduleCode}') }}/[id]"
                                                title="Копировать URL API"
                                                aria-label="Копировать URL API">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Параметры API -->
                        <div class="mt-3">
                            <p class="text-muted small mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                API поддерживает GET, POST, PUT, DELETE методы и пагинацию.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Модальное окно подтверждения удаления в корзину -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Перемещение в корзину</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Вы уверены, что хотите переместить запись <strong id="itemTitleToDelete"></strong> в корзину?</p>
HTML;

        // Добавляем информацию о корзине если включена опция (на этапе генерации)
        if ($hasTrash) {
            $content .= <<<HTML
                    <div class="alert alert-info alert-sm mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Запись будет доступна в корзине для восстановления в течение 30 дней
                    </div>
HTML;
        }

        $content .= <<<HTML
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <form id="deleteForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i> В корзину
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Уведомления -->
    <div id="notificationContainer" class="alert-fixed"></div>

HTML;

        // Добавляем скрипт для SEO статистики только если включена SEO опция
        if ($hasSeo) {
            $content .= <<<HTML
    <!-- Скрипт для SEO статистики -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Считаем заполненные SEO поля
        let titlesCount = 0;
        let descriptionsCount = 0;
        let keywordsCount = 0;

        // В реальном приложении здесь был бы AJAX запрос для получения статистики
        // Для демонстрации используем случайные числа
        titlesCount = Math.floor(Math.random() * {{\$totalItems}});
        descriptionsCount = Math.floor(Math.random() * {{\$totalItems}});
        keywordsCount = Math.floor(Math.random() * {{\$totalItems}});

        document.getElementById('seo-titles-count').textContent = titlesCount;
        document.getElementById('seo-descriptions-count').textContent = descriptionsCount;
        document.getElementById('seo-keywords-count').textContent = keywordsCount;
    });
    </script>
HTML;
        }

        $content .= <<<HTML
@endsection
HTML;

        return $content;
    }
}