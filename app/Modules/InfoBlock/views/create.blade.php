@extends('admin.layouts.default')

@section('content')

    <!-- Hero -->
    <div class="content">
        <div
            class="d-md-flex justify-content-md-between align-items-md-center py-3 pt-md-3 pb-md-0 text-center text-md-start">
            <div>
                <h1 class="h3 mb-1">{{ admin_trans('app.info_block.name') }}</h1>
                <p class="text-muted">{{ admin_trans('app.info_block.description') }}</p>
            </div>
            <div class="mt-4 mt-md-0">
                <a href="{{ route('admin.info_block.create') }}">
                    <button type="button" class="btn btn-alt-success me-1 mb-3">
                        <i class="fa fa-fw fa-plus opacity-50 me-1"></i> {{ admin_trans('app.info_block.create_block') }}
                    </button>
                </a>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Хлебные крошки -->
    <div class="content">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ admin_trans('app.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a
                        href="{{ route('admin.info_block.index') }}">{{ admin_trans('app.info_block.name') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ admin_trans('app.info_block.create_block') }}</li>
            </ol>
        </nav>
    </div>

    <!-- Page Content -->
    <div class="content">

        <form action="{{ route('admin.info_block.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="block block-rounded">
                <ul class="nav nav-tabs nav-tabs-block" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link active" id="main-tab" data-bs-toggle="tab" data-bs-target="#main-classic" role="tab" aria-controls="main" aria-selected="true">
                        Общая
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
                                    
                                    <!-- Basic Elements -->
                                    <div class="row push">
                                        <div class="col-lg-3">
                                            <label class="form-label" for="example-text-input">Название инфоблока:</label>
                                        </div>
                                        <div class="col-lg-8 col-xl-5">
                                            <div class="mb-4">
                                                <input type="text" class="@error('name') is-invalid @enderror form-control" id="example-text-input" name="name" placeholder="" value="{{ old('name') }}">
                                                @error('name')
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

                                                    {{-- <optgroup label="Числовые типы">
                                                        <option value="integer">Целое число</option>
                                                        <option value="float">Дробное число</option>
                                                        <option value="bigint">Большие целые числа</option>
                                                        <option value="decimal">Десятичное число</option>
                                                    </optgroup> --}}

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

@endsection
