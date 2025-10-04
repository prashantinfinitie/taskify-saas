@extends('layout')
@section('title')
    <?= get_label('edit_customers', 'Edit Customers') ?>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('superadmin.panel') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('customers.index') }}">{{ get_label('customers', 'Customers') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('edit_customers', 'Edit Customers') ?>
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('customers.index') }}">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('customers', 'Customers') ?>">
                        <i class='bx bx-list-ul'></i>
                    </button>
                </a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form class="form-submit-event" method="POST"
                    action="{{ route('customers.update', ['id' => $customer->id]) }}">
                    @csrf
                    <input type="hidden" name="redirect_url" value="{{ route('customers.index') }}">
                    <h2 class="mb-4">{{ get_label('edit_customer', 'Edit Customer') }}</h2>
                    <div class="row mt-3">
                        <div class="col-lg-6 mb-3">
                            <label for="first_name" class="form-label"><?= get_label('first_name', 'First Name') ?></label>
                            <span class="asterisk">*</span>
                            <input type="text" class="form-control" id="first_name" name="first_name"
                                value="{{ $customer->first_name }}" required>
                        </div>
                        <div class="col-lg-6 mb-3">
                            <label for="last_name" class="form-label"><?= get_label('last_name', 'Last Name') ?></label>
                            <span class="asterisk">*</span>
                            <input type="text" class="form-control" id="last_name" name="last_name"
                                value="{{ $customer->last_name }}" required>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-lg-6 mb-3">
                            <label for="email" class="form-label"><?= get_label('email', 'Email') ?></label> <span
                                class="asterisk">*</span>
                            <input type="email" class="form-control" id="email" name="email"
                                value="{{ $customer->email }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label
                                class="form-label"><?= get_label('country_code_and_phone_number', 'Country code and phone number') ?>
                                <span class="asterisk">*</span></label>
                            <div class="input-group">
                                <input type="tel" class="form-control" id="phone-input-edit"
                                    value="{{ $customer->phone }}" name="phone">
                                <input type="hidden" name="country_code" id="country_code"
                                    value="{{ $customer->country_code }}">
                                <input type="hidden" name="phone" id="phone_number">
                                <input type="hidden" name="country_iso_code" id="country_iso_code"
                                    value="{{ $customer->country_iso_code }}">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="alert alert-info alert-dismissible" role="alert">
                                {{ get_label('leave_it_blank_if_do_not_want_change_the_password', 'Leave it blank if do not want to change the password') }}
                                !!
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                </button>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <label for="password" class="form-label"><?= get_label('password', 'Password') ?>:</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password">
                                    <span class="input-group-text cursor-pointer"data-bs-toggle="tooltip"
                                        data-bs-placement="bottom"
                                        data-bs-original-title="{{ get_label('generate_password', 'Generate Password') }}"
                                        id="generate-password"><i class="bx bxs-magic-wand"></i></span>
                                    <span class="input-group-text cursor-pointer" id="show_password">
                                        <i id="eyeicon" class="bx bx-hide"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <label for="password_confirmation"
                                    class="form-label"><?= get_label('confirm_password', 'Confirm Password') ?>:</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation">
                                    <span class="input-group-text cursor-pointer" id="show_confirm_password">
                                        <i id="eyeicon" class='bx bx-hide'></i>
                                    </span>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <label for="status" class="form-label">{{ get_label('status', 'Status') }}</label>
                                <br>
                                <div class="btn-group" role="group">
                                    <input type="radio" class="btn-check" name="status" id="active"
                                        value="1" {{ $customer->status == 1 ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary {{ $customer->status == 1 ? 'active' : '' }}"
                                        for="active">
                                        {{ get_label('active', 'Active') }}
                                    </label>

                                    <input type="radio" class="btn-check" name="status" id="inactive"
                                        value="0" {{ $customer->status == 0 ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary {{ $customer->status == 0 ? 'active' : '' }}"
                                        for="inactive">
                                        {{ get_label('inactive', 'Inactive') }}
                                    </label>
                                </div>

                            </div>
                        </div>
                        <div class="col-lg-4">
                            <button type="submit"
                                class="btn btn-primary"><?= get_label('update_customer', 'Update Customer') ?></button>
                        </div>
                </form>
            </div>
        </div>
    </div>
    </div>
    <script src="{{ asset('assets/js/pages/customers.js') }}"></script>
@endsection
