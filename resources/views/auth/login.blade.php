@extends('layout')
<title>Login - {{ $general_settings['company_title'] }}</title>
@section('content')
    <!-- Content -->


    <div class="container-fluid">
        @if (config('constants.ALLOW_MODIFICATION') === 0)
            <div class="col-12 text-center mt-4">
                <div class="alert alert-warning mb-0">
                    <b>Note:</b> If you cannot log in here, please close the codecanyon frame by clicking on <b>x Remove
                        Frame</b> button from the top right corner of the page or <a href="{{ url('/') }}"
                        target="_blank">&gt;&gt; Click here &lt;&lt;</a>
                </div>
            </div>
        @endif
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <!-- Register -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center">
                            <a href="/" class="app-brand-link gap-2">
                                <span class="app-brand-logo demo">
                                    <img src="{{ asset($general_settings['full_logo']) }}" width="300px" alt="" />

                                </span>
                            </a>
                        </div>
                        <!-- /Logo -->
                        <h4 class="mb-2">Welcome to <?= $general_settings['company_title'] ?>! 👋</h4>
                        <p class="mb-4">Sign into your account</p>

                        <form id="formAuthentication" class="mb-3 form-submit-event"
                            action="{{ route('users.authenticate') }}" method="POST">
                            <input type="hidden" name="redirect_url" value="{{ route('home.index') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="asterisk">*</span></label>
                                <input type="text" class="form-control" id="email" name="email"
                                    placeholder="Please enter your email"
                                    value="<?= config('constants.ALLOW_MODIFICATION') === 0 ? 'admin@gmail.com' : '' ?>"
                                    autofocus />

                                @error('email')
                                    <p class="text-danger text-xs mt-1">{{ $message }}</p>
                                @enderror

                            </div>
                            <div class="mb-3 form-password-toggle">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label" for="password">Password <span
                                            class="asterisk">*</span></label>
                                    <a href="{{ route('forgot-password') }}">
                                        <small>Forgot Password?</small>
                                    </a>
                                </div>
                                <div class="input-group ">
                                    <input type="password" id="password" class="form-control" name="password"
                                        placeholder="Please enter your password"
                                        value="<?= config('constants.ALLOW_MODIFICATION') === 0 ? '12345678' : '' ?>"
                                        aria-describedby="password" />

                                </div>
                                <p class="text-danger text-xs mt-1 error-message"></p>

                                @error('password')
                                    <p class="text-danger text-xs mt-1">{{ $message }}</p>
                                @enderror


                            </div>

                            <div class="mb-4">
                                <button class="btn btn-primary d-grid w-100" id="submit_btn" type="submit">Login</button>
                            </div>
                            @if (config('constants.ALLOW_MODIFICATION') === 0)
                                <div class="mb-3">
                                    <button class="btn btn-danger d-grid w-100 superadmin-login"
                                        type="button">{{ get_label('login_as_superadmin', 'Login As Super Admin') }}</button>
                                </div>
                                <div class="mb-3">
                                    <button class="btn btn-success d-grid w-100 admin-login"
                                        type="button">{{ get_label('login_as_admin', 'Login As Admin') }}</button>
                                </div>
                                <div class="mb-3">
                                    <button class="btn btn-info d-grid w-100 member-login"
                                        type="button">{{ get_label('login_as_team_member', 'Login As  Team Member') }}</button>
                                </div>
                                <div class="mb-3">
                                    <button class="btn btn-warning d-grid w-100 client-login"
                                        type="button">{{ get_label('login_as_client', 'Login As Client') }}</button>
                                </div>
                            @endif
                        </form>


                        <p>Not have an account? <a href="{{ route('register') }}">Register</a></p>
                    </div>
                </div>
                <!-- /Register -->
            </div>
        </div>
    </div>
    <footer class="footer mb-3">
        <div class="container text-center">
            <div class="row d-flex flex-wrap justify-content-center">
                <div class="col-md-12 col-lg-8">
                    &copy; {{ date('Y') }} ,{!! str_replace(['<p>', '</p>'], '', $general_settings['footer_text']) !!}
                </div>
            </div>



            <div class="row justify-content-center mt-3"> <!-- Added row and justify-content-center for the links -->
                <div class="col-md-12 col-lg-8"> <!-- Adjusted column width for larger screens -->
                    <a href="{{ route('frontend.privacy_policy') }}"
                        class="text-decoration-none">{{ get_label('privacy_policy', 'Privacy Policy') }}</a>
                    <span class="mx-2">|</span>
                    <a href="{{ route('frontend.refund_policy') }}"
                        class="text-decoration-none">{{ get_label('refund_policy', 'Refund Policy') }}</a>
                    <span class="mx-2">|</span>
                    <a href="{{ route('frontend.terms_and_condition') }}"
                        class="text-decoration-none">{{ get_label('terms_and_conditions', 'Terms and Conditions') }}</a>
                </div>
            </div>
        </div>
    </footer>


    <!-- / Content -->
@endsection
