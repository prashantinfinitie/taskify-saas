@extends('layout')
<title>Forgot password - {{ $general_settings['company_title'] }}</title>
@section('content')
    <!-- Content -->

    <div class="container-fluid">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-4">
                <!-- Forgot Password -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center">
                            <a href="/home" class="app-brand-link">
                                <span class="app-brand-logo demo">
                                    <img src="{{ asset($general_settings['full_logo']) }}" width="300px" alt="" />
                                </span>
                                <!-- <span class="app-brand-text demo menu-text fw-bolder ms-2">Taskify</span> -->
                            </a>
                        </div>
                        <h3 class="text-center display-5 fw-semi-bold mt-5">
                            {{ get_label('forgot_password', 'Forgot Password?') }} 🔒
                        </h3>
                        <p class="text-center mb-4">
                            {{ get_label('enter_your_emailDesc', 'Enter your email and we will send you password reset link') }}
                        </p>

                        <form id="formAuthentication" class="mb-3 form-submit-event"
                            action="{{ route('forgot-password-mail') }}" method="POST">
                            <input type="hidden" name="redirect_url" value="{{ route('forgot-password') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">{{ get_label('email', 'Email') }} <span
                                        class="asterisk">*</span></label>
                                <input type="text" class="form-control" id="email" name="email"
                                    placeholder="{{ get_label('enter_your_email', 'Enter your email') }}"
                                    value="{{ old('email') }}" autofocus />
                            </div>
                            @error('email')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <button type="submit" id="submit_btn"
                                class="btn btn-primary d-grid w-100">{{ get_label('submit', 'Submit') }}</button>
                        </form>
                        <div class="text-center">
                            <a href="{{ url('/login') }}" class="d-flex align-items-center justify-content-center">
                                <i class="bx bx-chevron-left scaleX-n1-rtl bx-sm"></i>
                                Back to login
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /Forgot Password -->
            </div>
        </div>
    </div>
    <script>
        var label_please_wait = 'Please wait';
    </script>
    <!-- / Content -->
@endsection
