@extends('admin.layouts.default')

@section('content')
    <!-- Hero -->
    <div class="content">
        <div
            class="d-md-flex justify-content-md-between align-items-md-center py-3 pt-md-3 pb-md-0 text-center text-md-start">
            <div>
                <h1 class="h3 mb-1">{{ admin_trans('app.page.site_pages') }}</h1>
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Хлебные крошки -->
    <div class="content">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ admin_trans('app.dashboard') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ admin_trans('app.page.site_pages') }}</li>
            </ol>
        </nav>
    </div>

    <!-- Page Content -->
    <div class="content">

        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">{{ admin_trans('app.page.site_pages') }}</h3>
            </div>
            <div class="block-content">
                <div class="table-responsive">

                    @if ($pages->isNotEmpty())
                        <table class="table table-bordered table-striped table-vcenter">
                            <thead>
                                <tr>
                                    <th>{{ admin_trans('app.page.title') }}</th>
                                    <th class="text-center" style="width: 100px;">{{ admin_trans('app.options') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pages as $page)
                                    <tr>
                                        <td class="fw-semibold">
                                            <a href="{{ route('admin.page.edit', $page->id) }}">{{ $page->title }}</a>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="{{ route('admin.page.edit', $page->id) }}" type="button"
                                                    class="btn btn-sm btn-alt-secondary js-bs-tooltip-enabled"
                                                    data-bs-toggle="tooltip" aria-label="{{ admin_trans('app.edit') }}" data-bs-original-title="{{ admin_trans('app.edit') }}">
                                                    <i class="fa fa-pencil-alt"></i>
                                                </a>
                                                
                                                {{-- <form action="" method="POST">
                                                    @csrf
                                                    @method('DELETE')

                                                    <input type="hidden" name="user_id" value="">

                                                    <button type="submit"
                                                        class="btn btn-sm btn-alt-secondary js-bs-tooltip-enabled"
                                                        data-bs-toggle="tooltip" aria-label="Delete"
                                                        data-bs-original-title="{{ admin_trans('app.delete') }}">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </form> --}}
                                            
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div>Страницы пока не созданы</div>
                    @endif
                </div>
            </div>
        </div>

    </div>
    <!-- END Page Content -->
@endsection
