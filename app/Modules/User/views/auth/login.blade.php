@extends('layouts.auth')

@section('content')
    <div id="page-container">

        <!-- Main Container -->
        <main id="main-container">

            <!-- Page Content -->
            <div class="bg-image" style="background-image: url('assets/media/photos/photo14@2x.jpg');">
                <div class="row g-0 justify-content-center bg-black-75">
                    <div class="hero-static col-sm-8 col-md-6 col-xl-4 d-flex align-items-center p-2 px-sm-0">
                        <!-- Sign Up Block -->
                        <div class="block block-transparent block-rounded w-100 mb-0 overflow-hidden">
                            <div
                                class="block-content block-content-full px-lg-5 px-xl-6 py-4 py-md-5 py-lg-6 bg-body-extra-light">
                                <!-- Header -->
                                <div class="mb-2 text-center">
                                    <span class="text-dark link-fx fw-bold fs-1">{{ $settings['name_site'] }}</span>
                                    {{-- <span class="text-primary link-fx fw-bold fs-1">CMS</span> --}}
                                    <p class="text-uppercase fw-bold fs-sm text-muted">Войти в систему</p>
                                </div>
                                <!-- END Header -->

                                <!-- Sign Up Form -->
                                <form method="POST" action="{{ route('login') }}" class="js-validation-signup">
                                    @csrf

                                    <div class="mb-4">
                                        <div class="input-group input-group-lg">
                                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                                id="signup-email" name="email" placeholder="Email"
                                                value="{{ old('email') }}" required autocomplete="email" autofocus>
                                            <span class="input-group-text">
                                                <i class="fa fa-user-circle"></i>
                                            </span>

                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <div class="input-group input-group-lg">
                                            <input type="password"
                                                class="form-control @error('password') is-invalid @enderror"
                                                id="signup-password" required autocomplete="current-password"
                                                name="password" placeholder="Password">
                                            <span class="input-group-text">
                                                <i class="fa fa-asterisk"></i>
                                            </span>

                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div
                                        class="d-sm-flex justify-content-sm-between align-items-sm-center mb-4 bg-body rounded py-2 px-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                                {{ old('remember') ? 'checked' : '' }}>

                                            <label class="form-check-label" for="remember">
                                                {{ __('Запомнить меня') }}
                                            </label>
                                        </div>
                                    </div>

                                    <div class="text-center mb-4">
                                        <button type="submit" class="btn btn-hero btn-primary">
                                            <i class="fa fa-fw fa-sign-in-alt opacity-50 me-1"></i> {{ __('Войти') }}
                                        </button>
                                    </div>

                                </form>
                                <!-- END Sign Up Form -->
                            </div>
                        </div>
                    </div>
                    <!-- END Sign Up Block -->
                </div>
            </div>
            <!-- END Page Content -->
        </main>
        <!-- END Main Container -->
    </div>
@endsection
