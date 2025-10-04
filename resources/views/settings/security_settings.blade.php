@extends('layout')

@section('title')
    <?= get_label('security_settings', 'Security Settings') ?>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item">
                            <?= get_label('settings', 'Settings') ?>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('security_settings', 'Security Settings') ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('settings.security.store') }}" class="form-submit-event" method="POST">
                    <input type="hidden" name="dnr">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="max_login_attempts" class="form-label">
                                <?= get_label('max_login_attempts', 'Max Login Attempts') ?>
                                <span class="text-muted">
                                    (<?= get_label('max_login_attempts_info', 'Leave it blank if you do not want to lock the account') ?>)</span>
                                <span data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="Maximum number of login attempts before locking the account.">
                                    <i class="bx bxs-info-circle text-primary cursor-pointer"></i>
                                </span>
                            </label>
                            <input class="form-control" min="0" type="number" name="max_login_attempts"
                                placeholder="Enter max login attempts"
                                value="{{ $security_settings['max_login_attempts'] ?? '' }}">
                            @error('max_login_attempts')
                                <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="time_decay" class="form-label">
                                <?= get_label('time_decay', 'Time Decay') ?>
                                <span class="text-muted">
                                    (<?= get_label('time_decay_info', 'This will not apply if login attempts are not locked') ?>)</span>
                                <span data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="Time (in minutes) after which the login attempts are reset.">
                                    <i class="bx bxs-info-circle text-primary cursor-pointer"></i>
                                </span>
                            </label>
                            <input class="form-control" type="number" min="0" name="time_decay"
                                placeholder="Enter time decay"
                                value="<?= config('constants.ALLOW_MODIFICATION') === 0 ? str_repeat('*', strlen($security_settings['time_decay'] ?? '')) : ($security_settings['time_decay'] ?? '') ?>">
                            @error('time_decay')
                                <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- reCAPTCHA Settings -->
                        <div class="col-md-12 my-3">
                            <h5><?= get_label('recaptcha_settings', 'reCAPTCHA Settings') ?></h5> 
                        </div>

                        <!-- reCAPTCHA switch -->
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="enable_recaptcha"
                                    name="enable_recaptcha" value="1" {{ isset($security_settings['enable_recaptcha']) && $security_settings['enable_recaptcha'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_recaptcha">
                                    <?= get_label('enable_recaptcha', 'Enable reCAPTCHA') ?>
                                    <span data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Enable reCAPTCHA verification">
                                        <i class="bx bxs-info-circle text-primary cursor-pointer"></i>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3"></div>

                        <div class="col-md-6 mb-3">
                            <label for="recaptcha_site_key" class="form-label">
                                <?= get_label('recaptcha_site_key', 'reCAPTCHA Site Key') ?>
                                <span data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="Your reCAPTCHA site key from Google.">
                                    <i class="bx bxs-info-circle text-primary cursor-pointer"></i>
                                </span>
                            </label>
                            <input class="form-control" type="text" name="recaptcha_site_key"
                                placeholder="Enter reCAPTCHA site key"
                                value="<?= config('constants.ALLOW_MODIFICATION') === 0 ? str_repeat('*', strlen($security_settings['recaptcha_site_key'] ?? '')) : ($security_settings['recaptcha_site_key'] ?? '') ?>">
                            @error('recaptcha_site_key')
                                <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="recaptcha_secret_key" class="form-label">
                                <?= get_label('recaptcha_secret_key', 'reCAPTCHA Secret Key') ?>
                                <span data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="Your reCAPTCHA secret key from Google.">
                                    <i class="bx bxs-info-circle text-primary cursor-pointer"></i>
                                </span>
                            </label>
                            <input class="form-control" type="text" name="recaptcha_secret_key"
                                placeholder="Enter reCAPTCHA secret key"
                                value="<?= config('constants.ALLOW_MODIFICATION') === 0 ? str_repeat('*', strlen($security_settings['recaptcha_secret_key'] ?? '')) : ($security_settings['recaptcha_secret_key'] ?? '') ?>">
                            @error('recaptcha_secret_key')
                                <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- <div class="col-md-12 mb-3">
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-2"></i>
                                <?= get_label('recaptcha_info', 'To get reCAPTCHA keys, visit') ?>
                                <a href="https://www.google.com/recaptcha/admin/create" target="_blank">Google reCAPTCHA
                                    Admin Console</a>
                                <?= get_label('and_create_new_site', ' and create a new site.') ?>
                            </div>
                        </div> --}}

                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary me-2" id="submit_btn">
                                <?= get_label('update', 'Update') ?>
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <?= get_label('cancel', 'Cancel') ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection