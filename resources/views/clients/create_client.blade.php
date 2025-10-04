@extends('layout')

@section('title')
    <?= get_label('create_client', 'Create client') ?>
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
                            <a href="{{ route('clients.index') }}"><?= get_label('clients', 'Clients') ?></a>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('create', 'Create') ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
        @role('admin')
            @php
                $account_creation_template = App\Models\Template::where('type', 'email')
                    ->where('name', 'account_creation')
                    ->first();
            @endphp
            @if (!$account_creation_template || ($account_creation_template && $account_creation_template->status == 1))
                <div class="alert alert-primary" role="alert">
                    {{ get_label('acc_crea_email_enabled_inf', 'As Account Creation Email Status Is Active, Please Ensure Email Settings Are Configured and Operational (Not Applicable If the Client Is for Internal Purposes).') }}

                </div>
            @endif
        @endrole

        <div class="card">
            <div class="card-body">
                <form action="{{ route('clients.store') }}" method="POST" class="form-submit-event"
                    enctype="multipart/form-data">
                    <input type="hidden" name="redirect_url" value="{{ route('clients.index') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="internal_client"
                                    name="internal_purpose">
                                <label class="form-check-label"
                                    for="internal_client"><?= get_label('internal_client', 'Is this a client for internal purpose only?') ?></label>
                                <i class='bx bx-info-circle text-primary' data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="<?= get_label('internal_client_info', 'Select this option if you want to create a client for internal use only, without granting account access to the client.') ?>"></i>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="firstName" class="form-label"><?= get_label('first_name', 'First name') ?> <span
                                    class="asterisk">*</span></label>
                            <input class="form-control" type="text" id="first_name" name="first_name"
                                placeholder="<?= get_label('please_enter_first_name', 'Please enter first name') ?>"
                                value="{{ old('first_name') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastName" class="form-label"><?= get_label('last_name', 'Last name') ?> <span
                                    class="asterisk">*</span></label>
                            <input class="form-control" type="text" name="last_name" id="last_name"
                                placeholder="<?= get_label('please_enter_last_name', 'Please enter last name') ?>"
                                value="{{ old('last_name') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label"><?= get_label('email', 'E-mail') ?> <span
                                    class="asterisk">*</span></label>
                            <input class="form-control" type="text" id="email" name="email"
                                placeholder="<?= get_label('please_enter_email', 'Please enter email') ?>"
                                value="{{ old('email') }}" autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label
                                class="form-label"><?= get_label('country_code_and_phone_number', 'Country code and phone number') ?></label>
                            <div class="input-group">
                                <input type="tel" class="form-control" id="phone-input" name="phone">
                                <input type="hidden" name="country_code" id="country_code">
                                <input type="hidden" name="phone" id="phone_number">
                                <input type="hidden" name="country_iso_code" id="country_iso_code" value="">

                            </div>
                        </div>
                        <div class="col-md-6 form-password-toggle mb-3" id="passDiv">
                            <label for="password" class="form-label"><?= get_label('password', 'Password') ?> <span
                                    class="asterisk">*</span></label>
                            <div class="input-group input-group-merge">
                                <input class="form-control" type="password" id="password" name="password"
                                    placeholder="<?= get_label('please_enter_password', 'Please enter password') ?>"
                                    autocomplete="new-password">
                                <span class="input-group-text toggle-password cursor-pointer"><i
                                        class="bx bx-hide"></i></span>
                                <span class="input-group-text cursor-pointer" id="generate-password"><i
                                        class="bx bxs-magic-wand"></i></span>
                            </div>
                        </div>
                        <div class="col-md-6 form-password-toggle mb-3" id="confirmPassDiv">
                            <label for="password_confirmation"
                                class="form-label"><?= get_label('confirm_password', 'Confirm password') ?> <span
                                    class="asterisk">*</span></label>
                            <div class="input-group input-group-merge">
                                <input class="form-control" type="password" id="password_confirmation"
                                    name="password_confirmation"
                                    placeholder="<?= get_label('please_re_enter_password', 'Please re enter password') ?>"
                                    autocomplete="new-password">
                                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                            </div>
                        </div>
                        <div class="col-md-6 d-none mb-3">
                            <label for="dob" class="form-label"><?= get_label('dob', 'Date of birth') ?>
                                <span class="asterisk">*</span></label>
                            <input class="form-control" type="text" id="dob" name="dob"
                                placeholder="<?= get_label('please_select', 'Please select') ?>" autocomplete="off">
                        </div>
                        <div class="col-md-6 d-none mb-3">
                            <label for="doj" class="form-label"><?= get_label('doj', 'Date of joining') ?>
                                <span class="asterisk">*</span></label>
                            <input class="form-control" type="text" id="doj" name="doj"
                                placeholder="<?= get_label('please_select', 'Please select') ?>" autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="company" class="form-label"><?= get_label('company', 'Company') ?></label>
                            <input class="form-control" type="text" id="company" name="company"
                                placeholder="<?= get_label('please_enter_company_name', 'Please enter company name') ?>"
                                value="{{ old('company') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="address" class="form-label"><?= get_label('address', 'Address') ?></label>
                            <input class="form-control" type="text" id="address" name="address"
                                placeholder="<?= get_label('please_enter_address', 'Please enter address') ?>"
                                value="{{ old('address') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label"><?= get_label('city', 'City') ?></label>
                            <input class="form-control" type="text" id="city" name="city"
                                placeholder="<?= get_label('please_enter_city', 'Please enter city') ?>"
                                value="{{ old('city') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="state" class="form-label"><?= get_label('state', 'State') ?></label>
                            <input class="form-control" type="text" id="state" name="state"
                                placeholder="<?= get_label('please_enter_state', 'Please enter state') ?>"
                                value="{{ old('state') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label"><?= get_label('country', 'Country') ?></label>
                            <input class="form-control" type="text" id="country" name="country"
                                placeholder="<?= get_label('please_enter_country', 'Please enter country') ?>"
                                value="{{ old('country') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="zip" class="form-label"><?= get_label('zip_code', 'Zip code') ?></label>
                            <input class="form-control" type="text" id="zip" name="zip"
                                placeholder="<?= get_label('please_enter_zip_code', 'Please enter ZIP code') ?>"
                                value="{{ old('zip') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="profile"
                                class="form-label"><?= get_label('profile_picture', 'Profile picture') ?></label>
                            <input class="form-control" type="file" id="profile" name="profile">
                            <p class="text-muted mt-2"><?= get_label('allowed_jpg_png', 'Allowed JPG or PNG.') ?></p>
                        </div>
                        @if (isAdminOrHasAllDataAccess())
                            <div class="col-md-6 mb-3" id="statusDiv">
                                <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small
                                        class="text-muted mt-2"><?= get_label('deactivated_client_login_restricted', 'If Deactivated, the Client Won\'t Be Able to Log In to Their Account') ?></small>)</label>
                                <div class="">
                                    <div class="btn-group btn-group d-flex justify-content-center" role="group"
                                        aria-label="Basic radio toggle button group">
                                        <input type="radio" class="btn-check" id="client_active" name="status"
                                            value="1">
                                        <label class="btn btn-outline-primary"
                                            for="client_active"><?= get_label('active', 'Active') ?></label>
                                        <input type="radio" class="btn-check" id="client_deactive" name="status"
                                            value="0" checked>
                                        <label class="btn btn-outline-primary"
                                            for="client_deactive"><?= get_label('deactive', 'Deactive') ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3" id="requireEvDiv">
                                <label class="form-label" for="">
                                    <?= get_label('require_email_verification', 'Require email verification?') ?>
                                    <i class='bx bx-info-circle text-primary' data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="<?= get_label('client_require_email_verification_info', 'If Yes is selected, client will receive a verification link via email. Please ensure that email settings are configured and operational.') ?>"></i>
                                </label>
                                <div class="">
                                    <div class="btn-group btn-group d-flex justify-content-center" role="group"
                                        aria-label="Basic radio toggle button group">
                                        <input type="radio" class="btn-check" id="require_ev_yes" name="require_ev"
                                            value="1" checked>
                                        <label class="btn btn-outline-primary"
                                            for="require_ev_yes"><?= get_label('yes', 'Yes') ?></label>
                                        <input type="radio" class="btn-check" id="require_ev_no" name="require_ev"
                                            value="0">
                                        <label class="btn btn-outline-primary"
                                            for="require_ev_no"><?= get_label('no', 'No') ?></label>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary me-2"
                                id="submit_btn"><?= get_label('create', 'Create') ?></button>
                            <button type="reset"
                                class="btn btn-outline-secondary"><?= get_label('cancel', 'Cancel') ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/js/pages/clients.js') }}"></script>
@endsection
