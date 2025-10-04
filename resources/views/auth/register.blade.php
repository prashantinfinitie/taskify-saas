@extends('layout')
<title>Register - {{ $general_settings['company_title'] }}</title>
@section('content')
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
                    <p class="mb-4">Create your account</p>

                    <!-- Registration Form -->
                    <form id = "formRegister" action = "{{ route('users.register') }}" method = "POST">
                        @csrf
                        <!-- Name input -->
                        <div class="row mt-3">
                            <div class="col-lg-12 mb-3">
                                <label for="first_name"
                                    class="form-label"><?= get_label('first_name', 'First Name') ?>:</label><span
                                    class="asterisk">*</span>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                    placeholder = "{{ get_label('first_name', 'First Name') }}"
                                    value="{{ old('first_name') }}" required>
                            </div>
                            <div class="col-lg-12 mb-3">
                                <label for="last_name"
                                    class="form-label"><?= get_label('last_name', 'Last Name') ?>:</label><span
                                    class="asterisk">*</span>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                                    placeholder=<?= get_label('last_name', 'Last Name') ?> value="{{ old('last_name') }}"
                                    required>
                            </div>

                            <div class="col-lg-12 mb-3">
                                <label for="email" class="form-label"><?= get_label('email', 'Email') ?>:</label><span
                                    class="asterisk">*</span>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="{{ old('email') }}"
                                    placeholder = "<?= get_label('enter_your_email', 'Email') ?>" required>
                            </div>
                            <div class="col-lg-12 mb-3">
                                <label for="phone"
                                    class="form-label"><?= get_label('phone_number', 'Phone Number') ?>:</label><span
                                    class="asterisk">*</span>
                                <input type="text" class="form-control" id="phone_number" name="phone"
                                    placeholder ="<?= get_label('enter_your_phone_number', 'Please Enter YourPhone Number') ?>"
                                    value="{{ old('phone') }}" required>
                            </div>

                            <div class="col-lg-12 mb-3">
                                <label for="password"
                                    class="form-label"><?= get_label('password', 'Password') ?>:</label><span
                                    class="asterisk">*</span>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder = "<?= get_label('enter_your_password', 'Password') ?>" required>
                                    <span class="input-group-text cursor-pointer"data-bs-toggle="tooltip"
                                        data-bs-placement="bottom"
                                        data-bs-original-title="{{ get_label('generate_password', 'Generate Password') }}"
                                        id="generate-password"><i class="bx bxs-magic-wand"></i></span>
                                    <span class="input-group-text cursor-pointer" id="show_password">
                                        <i id="eyeicon" class="bx bx-hide"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="col-lg-12 mb-3">
                                <label for="password_confirmation"
                                    class="form-label"><?= get_label('confirm_password', 'Confirm Password') ?>:</label><span
                                    class="asterisk">*</span>
                                <input type="password" class="form-control" id="password_confirmation"
                                    placeholder ="<?= get_label('confirm_password', 'Confirm Password') ?>"
                                    name="password_confirmation" required>
                            </div>
                            <button type="button" id="registerCustomer"
                                class="btn btn-primary"><?= get_label('register', 'Register ') ?></button>
                        </div>
                    </form>
                    <!-- /Registration Form -->

                    <p>Already have an account? <a href="{{ route('login') }}">Login</a></p>
                </div>
            </div>
            <!-- /Register -->
        </div>
    </div>
    <footer class="footer">
        <div class="container text-center">
            <div class="row justify-content-center"> <!-- Added row and justify-content-center -->
                <div class="col-md-12 col-lg-8"> <!-- Adjusted column width for larger screens -->
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
@endsection
