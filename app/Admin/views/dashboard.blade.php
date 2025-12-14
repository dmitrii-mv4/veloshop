@extends('admin.layouts.default')

@section('content')
    <!-- Hero -->
    <div class="content">
        <div
            class="d-md-flex justify-content-md-between align-items-md-center py-3 pt-md-3 pb-md-0 text-center text-md-start">
            <div>
                <h1 class="h3 mb-1">{{ trans('app.dashboard') }}</h1>
            </div>
            <div class="mt-4 mt-md-0">
            </div>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Page Content -->
    <div class="content">
        <div class="row items-push">
            {{-- <div class="row items-push">
                <div class="col-md-6 col-xl-4">
                <!-- Project Overview #6 -->
                <a class="block block-rounded block-transparent block-link-pop bg-gd-sea h-100 mb-0" href="javascript:void(0)">
                    <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                    <div>
                        <p class="fs-lg fw-semibold mb-0 text-white">
                        Версия ядра
                        </p>
                        <p class="text-white-75 mb-0">
                        1.0 v
                        </p>
                    </div>
                    <div class="ms-3 item">
                        <i class="fa fa-2x fa-vector-square text-white-50"></i>
                    </div>
                    </div>
                </a>
                <!-- END Project Overview #6 -->
                </div>
            </div> --}}

            <div class="col-sm-6 col-xl-3">
                <div class="block block-rounded text-center d-flex flex-column h-100 mb-0">
                    <div class="block-content block-content-full flex-grow-1">
                        <div class="item rounded-3 bg-body mx-auto my-3">
                            <i class="fa fa-users fa-lg text-primary"></i>
                        </div>
                        <div class="fs-1 fw-bold">{{ $users_count }}</div>
                        <div class="text-muted mb-3">{{ trans('app.user.users') }}</div>
                        {{-- <div class="d-inline-block px-3 py-1 rounded-pill fs-sm fw-semibold text-success bg-success-light">
                            <i class="fa fa-caret-up me-1"></i>
                            19.2%
                        </div> --}}
                    </div>
                    <div class="block-content block-content-full block-content-sm bg-body-light fs-sm">
                        <a href="{{ route('admin.users') }}" class="fw-medium">
                            {{ trans('app.user.go_to_users') }}
                            <i class="fa fa-arrow-right ms-1 opacity-25"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- content -->
        </div>
    </div>
    <!-- END Page Content -->
@endsection
