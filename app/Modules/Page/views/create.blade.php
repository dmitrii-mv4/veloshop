@extends('admin.layouts.default')

@section('content')
    <!-- Hero -->
    <div class="content">
        <div
            class="d-md-flex justify-content-md-between align-items-md-center py-3 pt-md-3 pb-md-0 text-center text-md-start">
            <div>
                <h1 class="h3 mb-1">{{ trans('app.page.site_pages') }}</h1>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Хлебные крошки -->
    <div class="content">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ trans('app.dashboard') }}</a></li>
                <li class="breadcrumb-item" aria-current="page"><a href="{{ route('admin.page.index') }}">{{ trans('app.page.site_pages') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ trans('app.page.create_page') }}</li>
            </ol>
        </nav>
    </div>

    <!-- Page Content -->
    <div class="content">

        <form action="{{ route('admin.page.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="block block-rounded">
                <ul class="nav nav-tabs nav-tabs-block" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link active" id="main-tab" data-bs-toggle="tab" data-bs-target="#main-classic" role="tab" aria-controls="main" aria-selected="true">
                        {{ trans('app.main') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" role="tab" aria-controls="seo" aria-selected="false" tabindex="-1">
                        {{ trans('app.seo') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link" id="infoblok-tab" data-bs-toggle="tab" data-bs-target="#infoblok" role="tab" aria-controls="infoblok" aria-selected="false" tabindex="-1">
                        {{ trans('app.info_block.name') }}
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
                                            <label class="form-label" for="example-text-input">{{ trans('app.page.title') }}:</label>
                                        </div>

                                        <div class="col-lg-8 col-xl-5">
                                            <div class="mb-4">
                                                <input type="text" class="@error('name') is-invalid @enderror form-control" id="example-text-input" name="title" placeholder="" value="{{ old('title') }}">
                                                @error('title')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row push">
                                        <div class="col-lg-3">
                                            <label class="form-label" for="example-text-input">{{ trans('app.page.content') }}:</label>
                                        </div>

                                        <div class="col-lg-8 col-xl-5">
                                            <div class="mb-4">
                                                <textarea id="js-ckeditor" name="content" class="form-control @error('name') is-invalid @enderror form-control" rows="4">{{ old('content') }}</textarea>
                                                @error('content')
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

                    <!-- SEO -->
                    <div class="tab-pane fade" id="seo" role="tabpanel" aria-labelledby="seo-tab" tabindex="0">
                        <div class="row">

                            <div class="block block-rounded">
                                <div class="block-content">
                                    
                                    <!-- Basic Elements -->
                                    <div class="row push">
                                        <div class="col-lg-3">
                                            <label class="form-label" for="example-text-input">{{ trans('app.page.slug') }}:</label>
                                        </div>

                                        <div class="col-lg-8 col-xl-5">
                                            <div class="mb-4">
                                                <input type="text" class="@error('meta_slug') is-invalid @enderror form-control" id="example-text-input" name="meta_slug" placeholder="" value="{{ old('meta_slug') }}">
                                                @error('meta_slug')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row push">
                                        <div class="col-lg-3">
                                            <label class="form-label" for="example-text-input">title:</label>
                                        </div>

                                        <div class="col-lg-8 col-xl-5">
                                            <div class="mb-4">
                                                <input type="text" class="@error('meta_title') is-invalid @enderror form-control" id="example-text-input" name="meta_title" placeholder="" value="{{ old('meta_title') }}">
                                                @error('meta_title')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row push">
                                        <div class="col-lg-3">
                                            <label class="form-label" for="example-text-input">description:</label>
                                        </div>

                                        <div class="col-lg-8 col-xl-5">
                                            <div class="mb-4">
                                                <textarea name="meta_description" class="form-control @error('meta_description') is-invalid @enderror form-control" rows="4">{{ old('meta_description') }}</textarea>
                                                @error('meta_description')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row push">
                                        <div class="col-lg-3">
                                            <label class="form-label" for="example-text-input">{{ trans('app.page.keywords') }}:</label>
                                        </div>

                                        <div class="col-lg-8 col-xl-5">
                                            <div class="mb-4">
                                                <input type="text" class="@error('meta_keys') is-invalid @enderror form-control" id="example-text-input" name="meta_keys" placeholder="" value="{{ old('meta_keys') }}">
                                                @error('meta_keys')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        
                        </div>
                    </div>
                    <!-- END SEO -->

                    <!-- Infoblok -->
                    <div class="tab-pane fade" id="infoblok" role="tabpanel" aria-labelledby="infoblok-tab" tabindex="0">
                        <div class="row g-sm push">
                            <div class="block-content">

                                <!-- Контейнер для строк -->
                                {{-- <div id="infoblok-container">
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

                                                    <optgroup label="Специальные типы">
                                                        <option value="file">Файл</option>
                                                    </optgroup>

                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-8 col-xl-3">
                                            <div class="mb-4">
                                                <input type="text" class="form-control code-property-input" name="code_property" placeholder="Код" value="">
                                            </div>
                                        </div>
                                    </div>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                    <!-- END Infoblok -->

                    <button class="btn btn-alt-success me-1 mb-3">
                        <i class="fa fa-fw fa-plus opacity-50 me-1"></i> {{ trans('app.save') }}
                    </button>

                </div>
            </div>

        </form>

    </div>
    <!-- END Page Content -->
@endsection
