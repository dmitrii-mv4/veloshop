@extends('admin.layouts.default')

@section('content')
    <!-- Hero -->
    <div class="content">
        <div
            class="d-md-flex justify-content-md-between align-items-md-center py-3 pt-md-3 pb-md-0 text-center text-md-start">
            <div>
                <h1 class="h3 mb-1">{{ trans('app.settings') }}</h1>
            </div>
            <div class="mt-4 mt-md-0">
                <nav class="flex-shrink-0 my-2 my-sm-0 ms-sm-3" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ trans('app.dashboard') }}</a></li>
                        <li class="breadcrumb-item active"><a href="{{ route('admin.settings') }}">{{ trans('app.settings') }}</a></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Page Content -->
    <div class="content">

        <div class="row">
            <div class="col-md-6 col-xl-3">
              <a href="/api/app/site" target="_blank" class="block block-rounded block-link-pop">
                <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                  <div class="me-3">
                    <p class="fs-3 fw-medium mb-0">{{ trans('app.site_api') }}</p>
                    <p class="text-muted mb-0">/api/app/site</p>
                  </div>
                  <div>
                    <i class="fa fa-2x fa-chart-area text-danger"></i>
                  </div>
                </div>
              </a>
            </div>
        </div>

        <div class="block block-rounded">
            <div class="block-content">
                <form action="{{ route('admin.settings.update', $settings['id']) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('patch')
                    <!-- Basic Elements -->
                    <div class="row push">
                        <div class="col-lg-3">
                            <label class="form-label" for="example-text-input">{{ trans('app.site_name') }}:</label>
                        </div>
                        <div class="col-lg-8 col-xl-5">
                            <div class="mb-4">
                                <input type="text" class="@error('name_site') is-invalid @enderror form-control" id="name_site" name="name_site" placeholder="" value="{{ $settings['name_site'] }}">
                                @error('name_site')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row push">
                        <div class="col-lg-3">
                            <label class="form-label" for="example-text-input">{{ trans('app.link_to_site') }}:</label>
                        </div>
                        <div class="col-lg-8 col-xl-5">
                            <div class="mb-4">
                                <input type="text" class="@error('url_site') is-invalid @enderror form-control" id="url_site" name="url_site" placeholder="" value="{{ $settings['url_site'] }}">
                                @error('url_site')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row push">
                        <div class="col-lg-3">
                            <label class="form-label" for="example-text-input">{{ trans('app.site_description') }}:</label>
                        </div>
                        <div class="col-lg-8 col-xl-5">
                            <div class="mb-4">
                                <textarea class="@error('description_site') is-invalid @enderror form-control" id="description_site" name="description_site" rows="4">{{ $settings['description_site'] }}</textarea>
                                @error('description_site')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row push">
                        <div class="col-lg-3">
                            <label class="form-label" for="example-text-input">{{ trans('app.administration_panel_language') }}:</label>
                        </div>
                        <div class="col-lg-8 col-xl-5">
                            <div class="mb-4">
                                <select class="form-select" id="example-select" name="lang_admin">
                                    <option value="ru" {{ app()->getLocale() == 'ru' ? 'selected' : '' }}>{{ trans('app.lang.russian') }}</option>
                                    <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>{{ trans('app.lang.english') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-alt-success me-1 mb-3">
                        <i class="fa fa-fw fa-pencil-alt"></i> {{ trans('app.save') }}
                    </button>

                </form>
            </div>
        </div>

    </div>
    <!-- END Page Content -->
@endsection
