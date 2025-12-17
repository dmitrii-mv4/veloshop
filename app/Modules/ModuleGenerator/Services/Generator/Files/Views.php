<?php

namespace App\Modules\ModuleGenerator\Services\Generator\Files;

use App\Modules\ModuleGenerator\Services\ModuleConfigService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для генерации views файлов для модулей
 * 
 * @param array $moduleData Настройки модулей
 * @param string $moduleViewsPath путь к директории модуля views
 * @param string $moduleViewsCategoryPath путь к директории модуля views для категорий
 */

class Views
{
    protected $moduleData;
    protected $moduleViewsPath;
    protected $moduleViewsCategoryPath;

    public function __construct($moduleData)
    {
        $this->moduleData = $moduleData;
    }

    public function generate()
    {
        // Создание структуры директорий
        $this->moduleViewsPath = $this->ensureModulesViewsDir();

        // Генеририуем views файлы 
        $indexViewName = $this->createViewsIndex();
        //$createViewName = $this->createViewsCreate();
        //$updateViewName = $this->editViewsCreate();

        // Упаковываем в массив названия views файлы
        $viewNamesData = [
            'indexViewName' => $indexViewName,
            //'createViewName' => $createViewName,
            //'updateViewName' => $updateViewName,
        ];

        return $viewNamesData;
    }

    /**
     * Создает или проверяет существование директории для views файлов модуля
     * 
     * Директория создается по пути: modules/nameModule/views
     * 
     */
    private function ensureModulesViewsDir()
    {
        // Формируем путь к модулю
        $moduleViewsPath = base_path($this->moduleData['path']['views']);

        if (!File::exists($moduleViewsPath))
        {
            try {
                // Создаем директорию модуля
                File::makeDirectory($moduleViewsPath, 0755, true);
            } catch (\Exception $e)
            {
                $moduleDataCode = $this->moduleData['code_module'];

                throw new \RuntimeException("Не удалось создать директорию для views модуля '{$moduleDataCode}' по пути: {$moduleViewsPath}", 0, $e);
            }
        }
        return $moduleViewsPath;
    }

    /**
     * Главная страница модуля
     */
    public function createViewsIndex()
    {
        // Полный путь к файлу views
        $viewsFilePath = $this->moduleViewsPath . '/index.blade.php';

        $content = <<<'BLADE'
@extends('admin.layouts.default')

@section('content')
    <!-- Hero -->
    <div class="content">
        <div
            class="d-md-flex justify-content-md-between align-items-md-center py-3 pt-md-3 pb-md-0 text-center text-md-start">
            <div>
                <h1 class="h3 mb-1">{{ module_trans($moduleData->code_module, 'name_module') }}</h1>
            </div>
            <div class="mt-4 mt-md-0">
                @if(auth()->user()->hasPermission('module_'.$moduleData['code_module'].'_create'))
                    <a href="{{ route('modules.' . $moduleData['code_module'] . '.create') }}">
                        <button type="button" class="btn btn-alt-success me-1 mb-3">
                            <i class="fa fa-fw fa-plus opacity-50 me-1"></i> Добавить запись
                        </button>
                    </a>
                @endif

                @if ($moduleData['section_categories'] == true)
                    <a href="{{ route('modules.' . $moduleData['code_module'] . '.category.create') }}">
                        <button type="button" class="btn btn btn-alt-primary me-1 mb-3">
                            <i class="fa fa-fw fa-plus opacity-50 me-1"></i> Создать категорию
                        </button>
                    </a>
                @endif

                <!-- Кнопка для открытия модального окна для интеграции модулей -->
                <button type="button" class="btn btn-dark me-1 mb-3" data-bs-toggle="modal"
                    data-bs-target="#integrationModal">
                    <i class="si si-rocket me-1"></i> Интеграция
                </button>

                <!-- Модальное окно для интеграции модулей -->
                <div class="modal-properties modal fade" id="integrationModal" tabindex="-1" aria-labelledby="integrationModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form
                                action="{{ route('modules.integration.' . $moduleData['code_module'] . '.integrationStore', ['item' => $moduleData['id']]) }}"
                                method="POST" enctype="multipart/form-data" id="integrationModuleForm">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title" id="integrationModalLabel">Интеграция с модулями</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    @if (session('error'))
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            {{ session('error') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                aria-label="Close"></button>
                                        </div>
                                    @endif

                                    <div class="mb-3">
                                        <label for="integration-module" class="form-label">Модуль</label>
                                        <select class=" form-control form-select" name="selected_module"
                                            aria-label="Floating label select example" required>
                                            <option selected="" disabled="">Выберите модуль</option>
                                            @foreach ($getModulesIntegration as $module)
                                                <option value="{{ $module }}">{{ module_trans($module, 'name_module') }}</option>
                                            @endforeach
                                        </select>
                                        @error('selected_module')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        свойства
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                    <button class="btn btn-primary">Сохранить</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- END Модальное окно для интеграции модулей -->
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Хлебные крошки -->
    <div class="content">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ module_trans($moduleData->code_module, 'name_module') }}</li>
            </ol>
        </nav>
    </div>

    <div class="content">
        <div class="row">
            <div class="col-md-6 col-xl-3">
              <a class="block block-rounded block-link-pop" href="javascript:void(0)">
                <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                  <div class="me-3">
                    <p class="fs-3 fw-medium mb-0">{{ $items->total() }}</p>
                    <p class="text-muted mb-0">Всего записей</p>
                  </div>
                  <div>
                    <i class="fa fa-2x fa-box text-warning"></i>
                  </div>
                </div>
              </a>
            </div>
            @if ($moduleData['section_categories'] == true)
                <div class="col-md-6 col-xl-3">
                    <a class="block block-rounded block-link-pop" href="{{ route('modules.' . $moduleData->code_module . '.category.index') }}">
                    <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                    <div class="me-3">
                        <p class="fs-3 fw-medium mb-0">{{ $categories->count() }}</p>
                        <p class="text-muted mb-0">Всего категорий</p>
                    </div>
                    <div>
                        <i class="fa fa-2x fa-box text-warning"></i>
                    </div>
                    </div>
                </a>
                </div>
            @endif
            <div class="col-md-6 col-xl-3">
              <a href="/api/{{ $moduleData['code_module'] }}/get" target="_blank" class="block block-rounded block-link-pop">
                <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                  <div class="me-3">
                    <p class="fs-3 fw-medium mb-0">API модуля</p>
                    <p class="text-muted mb-0">/api/{{ $moduleData['code_module'] }}/get</p>
                  </div>
                  <div>
                    <i class="fa fa-2x fa-chart-area text-danger"></i>
                  </div>
                </div>
              </a>
            </div>
        </div>

        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Записи в модуле</h3>
            </div>
            <div class="block-content">
                <div class="table-responsive">
                    @if (!$items || count($items) === 0)
                        Вы ещё не создали не одной записи
                    @else
                        <table class="table table-bordered table-striped table-vcenter">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 10%;">#</th>
                                    <th style="width: 70%;">Название</th>
                                    <th class="text-center" style="width: 100px;">Опции</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $index => $item)
                                    @php
                                        $itemsDetal = $item->getAttributes();
                                    
                                        // Получаем все ключи массива
                                        $keys = array_keys($itemsDetal);

                                        // Получаем ID записи
                                            // Берем первый ключ (индекс 0)
                                            $secondKeyID = $keys[0] ?? null;

                                            // Получаем значение первого ключа (ID)
                                            $secondValueID = $secondKeyID ? $item[$secondKeyID] : 'Нет значения';

                                        // Получаем первое название
                                            // Берем второй ключ (индекс 1)
                                            $secondKey = $keys[1] ?? null;
                                            // Получаем значение второго ключа
                                            $secondValueTitle = $secondKey ? $item[$secondKey] : 'Нет значения';
                                    @endphp

                                    <tr>
                                        <td class="text-center">
                                            {{ $secondValueID }}
                                        </td>
                                        <td>{{ $secondValueTitle }}</td>
                                        <td class="text-center">
                                            <div class="btn-group">

                                                @can('update', $item)
                                                <a href="{{ route('modules.'.$moduleData->code_module.'.edit', $item->id) }}" type="button"
                                                    class="btn btn-sm btn-alt-secondary js-bs-tooltip-enabled"
                                                    data-bs-toggle="tooltip" aria-label="Edit" data-bs-original-title="Edit">
                                                    <i class="fa fa-pencil-alt"></i>
                                                </a>
                                                @endcan

                                                @can('delete', $item)
                                                <form action="{{ route('modules.' . $moduleData->code_module . '.delete', $item->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')

                                                    <input type="hidden" name="module_id" value="{{ $item->id }}">

                                                    <button type="submit"
                                                        class="btn btn-sm btn-alt-secondary js-bs-tooltip-enabled"
                                                        data-bs-toggle="tooltip" aria-label="Delete"
                                                        data-bs-original-title="Delete">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                <!-- Pagination -->
                @if($items->hasPages())
                  <div class="row">
                      <div class="col-sm-12 col-md-5">
                          <div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite">
                              Показано с {{ $items->firstItem() }} по {{ $items->lastItem() }} из {{ $items->total() }} записей
                          </div>
                      </div>
                      <div class="col-sm-12 col-md-7">
                          <div class="dataTables_paginate paging_simple_numbers" id="DataTables_Table_0_paginate">
                              <ul class="pagination">
                                  {{-- Previous Page Link --}}
                                  @if ($items->onFirstPage())
                                      <li class="paginate_button page-item previous disabled" id="DataTables_Table_0_previous">
                                          <a aria-controls="DataTables_Table_0" aria-disabled="true" role="link" data-dt-idx="previous" tabindex="0" class="page-link">
                                              <i class="fa fa-angle-left"></i>
                                          </a>
                                      </li>
                                  @else
                                      <li class="paginate_button page-item previous" id="DataTables_Table_0_previous">
                                          <a href="{{ $items->previousPageUrl() }}" aria-controls="DataTables_Table_0" role="link" data-dt-idx="previous" tabindex="0" class="page-link">
                                              <i class="fa fa-angle-left"></i>
                                          </a>
                                      </li>
                                  @endif

                                  {{-- Pagination Elements --}}
                                  @foreach ($items->getUrlRange(1, $items->lastPage()) as $page => $url)
                                      @if ($page == $items->currentPage())
                                          <li class="paginate_button page-item active">
                                              <a href="#" aria-controls="DataTables_Table_0" role="link" aria-current="page" data-dt-idx="{{ $page }}" tabindex="0" class="page-link">{{ $page }}</a>
                                          </li>
                                      @else
                                          <li class="paginate_button page-item">
                                              <a href="{{ $url }}" aria-controls="DataTables_Table_0" role="link" data-dt-idx="{{ $page }}" tabindex="0" class="page-link">{{ $page }}</a>
                                          </li>
                                      @endif
                                  @endforeach

                                  {{-- Next Page Link --}}
                                  @if ($items->hasMorePages())
                                      <li class="paginate_button page-item next" id="DataTables_Table_0_next">
                                          <a href="{{ $items->nextPageUrl() }}" aria-controls="DataTables_Table_0" role="link" data-dt-idx="next" tabindex="0" class="page-link">
                                              <i class="fa fa-angle-right"></i>
                                          </a>
                                      </li>
                                  @else
                                      <li class="paginate_button page-item next disabled" id="DataTables_Table_0_next">
                                          <a aria-controls="DataTables_Table_0" aria-disabled="true" role="link" data-dt-idx="next" tabindex="0" class="page-link">
                                              <i class="fa fa-angle-right"></i>
                                          </a>
                                      </li>
                                  @endif
                              </ul>
                          </div>
                      </div>
                  </div>
                @endif
                <!-- end Pagination -->
            </div>
        </div>
    </div>

@endsection

BLADE;

        // Записываем содержимое в файл view
        File::put($viewsFilePath, $content);

        if (!File::exists($viewsFilePath)) {
            throw new \Exception("Файл view не найден: ".$viewsFilePath);
        }
        return $this->moduleData['code_module'] . "::index";
    }
}