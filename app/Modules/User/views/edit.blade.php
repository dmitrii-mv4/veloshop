@extends('admin.layouts.default')

@section('content')
    <!-- Hero -->
    <div class="content">
        <div
            class="d-md-flex justify-content-md-between align-items-md-center py-3 pt-md-3 pb-md-0 text-center text-md-start">
            <div>
                <h1 class="h3 mb-1">{{ trans('app.user.edit') }}</h1>
            </div>
            <div class="mt-4 mt-md-0">

            </div>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Хлебные крошки -->
    <div class="content">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ trans('app.user.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users') }}">{{ trans('app.user.users') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ trans('app.user.edit') }}</li>
            </ol>
        </nav>
    </div>

    <!-- Page Content -->
    <div class="content">

        <div class="block block-rounded">
            <div class="block-content">
                <form action="{{ route('admin.users.update', $user['id']) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('patch')
                    <!-- Basic Elements -->
                    <div class="row push">
                        <div class="col-lg-3">
                            <label class="form-label" for="example-text-input">{{ trans('app.name') }}:</label>
                        </div>
                        <div class="col-lg-8 col-xl-5">
                            <div class="mb-4">
                                <input type="text" class="@error('name') is-invalid @enderror form-control" id="example-text-input" name="name" placeholder="" value="{{ $user['name'] }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row push">
                        <div class="col-lg-3">
                            <label class="form-label" for="example-text-input">{{ trans('app.email') }}:</label>
                        </div>
                        <div class="col-lg-8 col-xl-5">
                            <div class="mb-4">
                                <input type="text" class="@error('email') is-invalid @enderror form-control" id="example-text-input" name="email" placeholder="" value="{{ $user['email'] }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row push">
                        <div class="col-lg-3">
                            <label class="form-label" for="example-text-input">{{ trans('app.role.name') }}:</label>
                        </div>
                        <div class="col-lg-8 col-xl-5">
                            <div class="mb-4">
                                <select class="form-select" id="example-select" name="role_id">
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" 
                                            {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row push">
                        <div class="col-lg-3">
                            <label class="form-label" for="password">{{ trans('app.user.new_password') }}:</label>
                        </div>
                        <div class="col-lg-8 col-xl-5">
                            <div class="mb-4 position-relative">
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="" style="padding-right: 40px;">
                                <span class="position-absolute top-50 end-0 translate-middle-y me-3" style="cursor: pointer; z-index: 5;" onclick="togglePasswordVisibility('password')">
                                    <i class="fa-solid fa-eye" id="password-eye"></i>
                                </span>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-lg-1">
                            <i class="fa-solid fa-circle-question" 
                            data-bs-toggle="tooltip" 
                            data-bs-placement="top" 
                            title="Оставьте поле пустым, если не требуется смена пароля"
                            style="cursor: pointer; margin-top: 10px; color: #6c757d;"></i>
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
