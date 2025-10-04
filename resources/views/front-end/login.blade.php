@extends('front-end.layout')
@section('title')
    {{ get_label('login', 'Login') }}
@endsection

@section('content')
    <section class="section-py mt-7 py-2">
        <div class="container">
            <h1 class="display-5 fw-semi-bold mt-5 text-center">
                {{ get_label('login_register_heading', 'Login or Register') }}
            </h1>
            <p class="fs-0 fs-md-1 text-center">
                {{ get_label('login_register_subheading', 'Access your account or create a new one to start managing your projects.') }}
            </p>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="img-container">
                        <lottie-player src="/assets/front-end/img/gallery/Animation - 1712314757629.json"
                            background="transparent" speed="1" class="lottie" loop autoplay>
                        </lottie-player>
                    </div>
                </div>
                <div class="col-md-6 mt-8">
                    <ul class="nav nav-pills nav-justified mb-4" id="auth-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="login-tab" data-bs-toggle="tab" href="#login" role="tab"
                                aria-controls="login" aria-selected="true">{{ get_label('login', 'Login') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="register-tab" data-bs-toggle="tab" href="#register" role="tab"
                                aria-controls="register" aria-selected="false">{{ get_label('register', 'Register') }}</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="auth-tab-content">
                        <div class="tab-pane fade show active" id="login" role="tabpanel" aria-labelledby="login-tab">
                            <div class="card showcase-page-gradient shadow">
                                <div class="card-body">
                                    <form id="formAuthentication" class="form-submit-event mb-3"
                                        action="{{ route('users.authenticate') }}" method="POST">
                                        <input type="hidden" name="redirect_url" value="{{ route('home.index') }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="email" class="form-label">{{ get_label('email', 'Email') }} <span
                                                    class="asterisk">*</span></label>
                                            <input type="text" class="form-control" id="email" name="email"
                                                placeholder="{{ get_label('enter_your_email', 'Please enter your email') }}"
                                                value="<?= config('constants.ALLOW_MODIFICATION') === 0 ? 'superadmin@gmail.com' : '' ?>"
                                                autofocus />

                                            <p class="text-danger error-message mt-1 text-xs"></p>

                                            @error('email')
                                                <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                            @enderror

                                        </div>
                                        <div class="form-password-toggle mb-3">
                                            <div class="d-flex justify-content-between">
                                                <label class="form-label"
                                                    for="password">{{ get_label('password', 'Password') }}
                                                    <span class="asterisk">*</span></label>
                                                <a href="{{ route('forgot-password') }}">
                                                    <small
                                                        class="text-dark">{{ get_label('forgot_password', 'Forgot Password?') }}</small>
                                                </a>
                                            </div>
                                            <div class="input-group">
                                                <input type="password" id="password" class="form-control" name="password"
                                                    placeholder="{{ get_label('enter_your_password', 'Please enter your password') }}"
                                                    value="<?= config('constants.ALLOW_MODIFICATION') === 0 ? '12345678' : '' ?>"
                                                    aria-describedby="password" />
                                                <span class="m-2 text-end text-sm" id="eyeicon">
                                                    <i class="far fa-eye-slash"></i>
                                                </span>
                                            </div>

                                            <p class="text-danger error-message mt-1 text-xs"></p>

                                            @error('password')
                                                <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                            @enderror

                                        </div>

                                        @if(!empty($security_settings['enable_recaptcha']))
                                            <div class="mb-3">
                                                {!! NoCaptcha::renderJs() !!}
                                                {!! NoCaptcha::display() !!}
                                                @error('g-recaptcha-response')
                                                    <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        @endif

                                        <div class="mb-4">
                                            <button class="btn bg-gradient-primary d-grid w-100" type="submit"
                                                id="loginBtn">{{ get_label('login', 'Login') }}</button>
                                        </div>
                                        @if (config('constants.ALLOW_MODIFICATION') === 0)
                                            <div class="mb-3">
                                                <button class="btn bg-gradient-danger d-grid w-100 superadmin-login"
                                                    type="button">{{ get_label('login_as_superadmin', 'Login As Super Admin') }}</button>
                                            </div>
                                            <div class="mb-3">
                                                <button class="btn bg-gradient-success d-grid w-100 admin-login"
                                                    type="button">{{ get_label('login_as_admin', 'Login As Admin') }}</button>
                                            </div>
                                            <div class="mb-3">
                                                <button class="btn bg-gradient-info d-grid w-100 member-login"
                                                    type="button">{{ get_label('login_as_team_member', 'Login As  Team Member') }}</button>
                                            </div>
                                            <div class="mb-3">
                                                <button class="btn bg-gradient-warning d-grid w-100 client-login"
                                                    type="button">{{ get_label('login_as_client', 'Login As Client') }}</button>
                                            </div>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="register" role="tabpanel" aria-labelledby="register-tab">
                            <div class="card">
                                <div class="card-body">
                                    <form id="formRegister" action="{{ route('users.register') }}" method="POST">
                                        @csrf
                                        <!-- Name input -->
                                        <div class="row mt-3">
                                            <div class="col-lg-6 mb-3">
                                                <label for="first_name"
                                                    class="form-label"><?= get_label('first_name', 'First Name') ?>:</label><span
                                                    class="asterisk">*</span>
                                                <input type="text" class="form-control" id="first_name" name="first_name"
                                                    placeholder="{{ get_label('first_name', 'First Name') }}"
                                                    value="{{ old('first_name') }}" required>
                                            </div>
                                            <div class="col-lg-6 mb-3">
                                                <label for="last_name"
                                                    class="form-label"><?= get_label('last_name', 'Last Name') ?>:</label><span
                                                    class="asterisk">*</span>
                                                <input type="text" class="form-control" id="last_name" name="last_name"
                                                    placeholder=<?= get_label('last_name', 'Last Name') ?>
                                                    value="{{ old('last_name') }}" required>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-lg-6 mb-3">
                                                <label for="email"
                                                    class="form-label"><?= get_label('email', 'Email') ?>:</label><span
                                                    class="asterisk">*</span>
                                                <input type="email" class="form-control" id="email" name="email"
                                                    value="{{ old('email') }}"
                                                    placeholder="<?= get_label('enter_your_email', 'Email') ?>" required>
                                            </div>
                                            <div class="col-lg-6 mb-3">
                                                <label for="phone"
                                                    class="form-label"><?= get_label('phone_number', 'Phone Number') ?>:</label><span
                                                    class="asterisk">*</span>
                                                <input type="text" class="form-control" id="phone_number" name="phone"
                                                    placeholder="<?= get_label('enter_your_phone_number', 'Please Enter YourPhone Number') ?>"
                                                    value="{{ old('phone') }}" required>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-lg-6 mb-3">
                                                <label for="password"
                                                    class="form-label"><?= get_label('password', 'Password') ?>:</label><span
                                                    class="asterisk">*</span>
                                                <input type="password" class="form-control" id="password" name="password"
                                                    placeholder="<?= get_label('enter_your_password', 'Password') ?>"
                                                    required>
                                            </div>
                                            <div class="col-lg-6 mb-3">
                                                <label for="password_confirmation"
                                                    class="form-label"><?= get_label('confirm_password', 'Confirm Password') ?>:</label><span
                                                    class="asterisk">*</span>
                                                <input type="password" class="form-control" id="password_confirmation"
                                                    placeholder="<?= get_label('confirm_password', 'Confirm Password') ?>"
                                                    name="password_confirmation" required>
                                            </div>
                                        </div>
                                        {{-- @dd($security_settings) --}}
                                        @if(!empty($security_settings['enable_recaptcha']))
                                            <div class="row mt-3">
                                                <div class="col-lg-12 mb-3">
                                                    @if(empty($security_settings['enable_recaptcha']))
                                                        {!! NoCaptcha::renderJs() !!}
                                                    @endif
                                                    {!! NoCaptcha::display() !!}
                                                    @error('g-recaptcha-response')
                                                        <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>
                                        @endif

                                        <div class="row mt-3">
                                            <div class="col-lg-12">
                                                <button type="submit" id="registerCustomer"
                                                    class="btn btn-primary w-100"><?= get_label('register', 'Register ') ?></button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection