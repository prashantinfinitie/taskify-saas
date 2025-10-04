@extends('layout')
@section('title')
    {{ get_label('create_manager', 'Create Manager') }}
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
                        <li class="breadcrumb-item active">
                            <?= get_label('create_manager', 'Create Manager') ?>
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('managers.index') }}">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('managers', 'Managers') ?>">
                        <i class='bx bx-list-ul'></i>
                    </button>
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="alert alert-primary mt-3" role="alert">
                    {{ get_label('manager_alert', 'As a Manager, user can access and manage Plans, Subscriptions, Transactions, and Customers And Support') }}
                </div>
                <form class="form-submit-event" method="POST" action="{{ route('managers.store') }}">
                    @csrf
                    <input type="hidden" name="redirect_url" value="{{ route('managers.index') }}">
                    <h2 class="mb-4">{{ get_label('create_manager', 'Create Manager') }}</h2>
                    <div class="row mt-3">
                        <div class="col-lg-6 mb-3">
                            <label for="first_name" class="form-label"><?= get_label('first_name', 'First Name') ?>:</label>
                            <input type="text" class="form-control" id="first_name" name="first_name"
                                value="{{ old('first_name') }}" required>
                        </div>
                        <div class="col-lg-6 mb-3">
                            <label for="last_name" class="form-label"><?= get_label('last_name', 'Last Name') ?>:</label>
                            <input type="text" class="form-control" id="last_name" name="last_name"
                                value="{{ old('last_name') }}" required>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-lg-6 mb-3">
                            <label for="email" class="form-label"><?= get_label('email', 'Email') ?>:</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label
                                class="form-label"><?= get_label('country_code_and_phone_number', 'Country code and phone number') ?>
                                <span class="asterisk">*</span></label>
                            <div class="input-group">
                                <!-- Country Code Input -->
                                <input type="tel" class="form-control" id="phone-input" name="phone">
                                <input type="hidden" name="country_code" id="country_code">
                                <input type="hidden" name="phone" id="phone_number">
                                <input type="hidden" name="country_iso_code" id="country_iso_code" value="">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-lg-6 mb-3">
                                <label for="password" class="form-label"><?= get_label('password', 'Password') ?>:</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" required>
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
                                        name="password_confirmation" required>


                                    <span class="input-group-text cursor-pointer" id="show_confirm_password">
                                        <i id="eyeicon" class='bx bx-hide'></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit"
                        class="btn btn-primary"><?= get_label('register_manager', 'Register Manager') ?></button>
                </form>
            </div>
        </div>
    </div>
    @php
        $routePrefix = Route::getCurrentRoute()->getPrefix();
    @endphp
    <script>
        var routePrefix = '/' + '{{ $routePrefix }}';
    </script>
    <script src="{{ asset('assets/js/pages/managers.js') }}"></script>
@endsection
