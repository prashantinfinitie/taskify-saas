@extends('layout')

@section('title')
    <?= get_label('update_user_profile', 'Update user profile') ?>
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
                            <a href="{{ route('users.index') }}"><?= get_label('users', 'Users') ?></a>
                        </li>
                        <li class="breadcrumb-item">
                            <a
                                href="{{ route('users.show', [$user->id]) }}">{{ $user->first_name . ' ' . $user->last_name }}</a>

                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('update', 'Update') ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('users.update_user', [$user->id]) }}" class="form-submit-event" method="POST"
                    enctype="multipart/form-data">
                    <input type="hidden" name="redirect_url" value="{{ route('users.index') }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstName" class="form-label"><?= get_label('first_name', 'First name') ?> <span
                                    class="asterisk">*</span></label>
                            <input class="form-control" type="text" id="first_name" name="first_name"
                                placeholder="<?= get_label('please_enter_first_name', 'Please enter first name') ?>"
                                value="{{ $user->first_name }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastName" class="form-label"><?= get_label('last_name', 'Last name') ?> <span
                                    class="asterisk">*</span></label>
                            <input class="form-control" type="text" name="last_name"
                                placeholder="<?= get_label('please_enter_last_name', 'Please enter last name') ?>"
                                id="last_name" value="{{ $user->last_name }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="role"><?= get_label('role', 'Role') ?> <span
                                    class="asterisk">*</span></label>
                            <div class="input-group">

                                <select class="form-select text-capitalize js-example-basic-multiple" id="role"
                                    name="role" {{ $user->hasRole('admin') ? 'disabled' : '' }}>
                                    @foreach ($roles as $role)
    <option value="{{ $role->id }}"
        {{ $user->hasRole($role->name) ? 'selected' : '' }}>
        {{ ucfirst($role->name) }}
    </option>
@endforeach

                                </select>
                            </div>
                            @if ($user->hasRole('admin'))
                                <small class="text-danger">Role cannot be changed for an admin user.</small>
                            @endif

                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label"><?= get_label('email', 'E-mail') ?> <span
                                    class="asterisk">*</span></label>
                            <input class="form-control" type="text" id="email" name="email"
                                placeholder="<?= get_label('please_enter_email', 'Please enter email') ?>"
                                value="{{ $user->email }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label
                                class="form-label"><?= get_label('country_code_and_phone_number', 'Country code and phone number') ?></label>
                            <div class="input-group">
                                <!-- Country Code Input -->

                                <input type="tel" class="form-control" id="phone-input-edit"
                                    value="{{ $user->phone }}" name="phone">
                                <input type="hidden" name="country_code" id="country_code"
                                    value="{{ $user->country_code }}">
                                <input type="hidden" name="phone" id="phone_number">
                                <input type="hidden" name="country_iso_code" id="country_iso_code"
                                    value="{{ $user->country_iso_code }}">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="address" class="form-label"><?= get_label('address', 'Address') ?></label>
                            <input class="form-control" type="text" id="address" name="address"
                                placeholder="<?= get_label('please_enter_address', 'Please enter address') ?>"
                                value="{{ $user->address }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label"><?= get_label('city', 'City') ?></label>
                            <input class="form-control" type="text" id="city" name="city"
                                placeholder="<?= get_label('please_enter_city', 'Please enter city') ?>"
                                value="{{ $user->city }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="state" class="form-label"><?= get_label('state', 'State') ?></label>
                            <input class="form-control" type="text" id="state" name="state"
                                placeholder="<?= get_label('please_enter_state', 'Please enter state') ?>"
                                value="{{ $user->state }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label"><?= get_label('country', 'Country') ?></label>
                            <input class="form-control" type="text" id="country" name="country"
                                placeholder="<?= get_label('please_enter_country', 'Please enter country') ?>"
                                value="{{ $user->country }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="zip" class="form-label"><?= get_label('zip_code', 'ZIP code') ?></label>
                            <input class="form-control" type="text" id="zip" name="zip"
                                placeholder="<?= get_label('please_enter_zip_code', 'Please enter ZIP code') ?>"
                                value="{{ $user->zip }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="dob" class="form-label"><?= get_label('dob', 'Date of birth') ?></label>
                            <input class="form-control" type="text" id="dob" name="dob"
                                value="{{ $user->dob ? format_date($user->dob) : '' }}"
                                placeholder="<?= get_label('please_select', 'Please select') ?>" autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="doj" class="form-label"><?= get_label('doj', 'Date of joining') ?></label>
                            <input class="form-control" type="text" id="doj" name="doj"
                                value="{{ $user->doj ? format_date($user->doj) : '' }}"
                                placeholder="<?= get_label('please_select', 'Please select') ?>" autocomplete="off">
                        </div>
                        @if (isAdminOrHasAllDataAccess())
                            <div class="col-md-6 form-password-toggle mb-3">
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
                            <div class="col-md-6 form-password-toggle mb-3">
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
                        @endif
                        <div class="col-md-12 mb-3">
                            <label for="photo"
                                class="form-label"><?= get_label('profile_picture', 'Profile picture') ?></label>
                            <div class="d-flex align-items-start align-items-sm-center my-3 gap-4">
                                <img src="{{ $user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg') }}"
                                    alt="user-avatar" class="d-block rounded" height="100" width="100"
                                    id="uploadedAvatar" />
                                <div class="button-wrapper">
                                    <div class="input-group d-flex">
                                        <input type="file" accept="image/*" class="form-control"
                                            id="inputGroupFile02" name="upload">
                                    </div>
                                    <p class="text-muted mt-2"><?= get_label('allowed_jpg_png', 'Allowed JPG or PNG.') ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        @if (isAdminOrHasAllDataAccess() && $user->getRoleNames()->first() !== 'admin')
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small
                                        class="text-muted mt-2"><?= get_label('deactivated_user_login_restricted', 'If Deactivated, the User Won\'t Be Able to Log In to Their Account') ?></small>)</label>
                                <div class="">
                                    <div class="btn-group btn-group d-flex justify-content-center" role="group"
                                        aria-label="Basic radio toggle button group">
                                        <input type="radio" class="btn-check" id="user_active" name="status"
                                            value="1" <?= $user->status == 1 ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-primary"
                                            for="user_active"><?= get_label('active', 'Active') ?></label>
                                        <input type="radio" class="btn-check" id="user_deactive" name="status"
                                            value="0" <?= $user->status == 0 ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-primary"
                                            for="user_deactive"><?= get_label('deactive', 'Deactive') ?></label>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="mt-4">
                            <button type="submit" id="submit_btn"
                                class="btn btn-primary me-2"><?= get_label('update', 'Update') ?></button>
                            <button type="reset"
                                class="btn btn-outline-secondary"><?= get_label('cancel', 'Cancel') ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/js/pages/users.js') }}"></script>
@endsection
