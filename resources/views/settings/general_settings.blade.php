@extends('layout')

@section('title')
    <?= get_label('general_settings', 'General settings') ?>
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
                            <?= get_label('settings', 'Settings') ?>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('general', 'General') ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('settings.store_general') }}" class="form-submit-event" method="POST"
                    enctype="multipart/form-data">
                    <input type="hidden" name="redirect_url" value="{{ route('settings.index') }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="company_title" class="form-label"><?= get_label('company_title', 'Company title') ?>
                                <span class="asterisk">*</span></label>
                            <input class="form-control" type="text" id="company_title" name="company_title"
                                placeholder="Enter company title" value="{{ $general_settings['company_title'] }}">

                            @error('company_title')
                                <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                            @enderror

                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="support_email" class="form-label"><?= get_label('support_email', 'Support Email') ?>
                                <span class="asterisk">*</span></label>
                            <input class="form-control" type="text" id="support_email" name="support_email"
                                placeholder="{{ get_label('support_email_desc', 'Enter Your Support Email') }}"
                                value="{{ $general_settings['support_email'] }}">

                            @error('support_email')
                                <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="full_logo" class="form-label"><?= get_label('full_logo', 'Full logo') ?> <a
                                    data-bs-toggle="tooltip" data-bs-placement="right"
                                    data-bs-original-title="<?= get_label('view_current_full_logo', 'View current full logo') ?>"
                                    href="{{ asset($general_settings['full_logo']) }}" data-lightbox="full logo"
                                    data-title="<?= get_label('current_full_logo', 'Current full logo') ?>"> <i
                                        class='bx bx-show-alt'></i></a></label>
                            <input type="file" accept="image/*" class="form-control" id="inputGroupFile02" name="full_logo">

                            @error('full_logo')
                                <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                            @enderror

                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="half_logo" class="form-label"><?= get_label('half_logo', 'Half logo') ?> <a
                                    data-bs-toggle="tooltip" data-bs-placement="right"
                                    data-bs-original-title="<?= get_label('view_current_half_logo', 'View current half logo') ?>"
                                    href="{{ asset($general_settings['half_logo']) }}" data-lightbox="half_logo"
                                    data-title="<?= get_label('current_half_logo', 'Current half logo') ?>"> <i
                                        class='bx bx-show-alt'></i></a></label>
                            <input type="file" accept="image/*" class="form-control" id="inputGroupFile02" name="half_logo">

                            @error('half_logo')
                                <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                            @enderror

                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="favicon" class="form-label"><?= get_label('favicon', 'Favicon') ?> <a
                                    data-bs-toggle="tooltip" data-bs-placement="right"
                                    data-bs-original-title="<?= get_label('view_current_favicon', 'View current favicon') ?>"
                                    href="{{ asset($general_settings['favicon']) }}" data-lightbox="favicon"
                                    data-title="<?= get_label('current_favicon', 'Current favicon') ?>"> <i
                                        class='bx bx-show-alt'></i></a></label>
                            <input type="file" accept="image/*" class="form-control" id="inputGroupFile02" name="favicon">

                            @error('favicon')
                                <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="footer_logo" class="form-label"><?= get_label('footer_logo', 'Footer Logo') ?> <a
                                    data-bs-toggle="tooltip" data-bs-placement="right"
                                    data-bs-original-title="<?= get_label('view_current_footer_logo', 'View current footer logo') ?>"
                                    href="{{ asset($general_settings['footer_logo']) ? asset($general_settings['footer_logo']) : asset('oim.png') }}"
                                    data-lightbox="footer_logo"
                                    data-title="<?= get_label('current_favicon', 'Current favicon') ?>"> <i
                                        class='bx bx-show-alt'></i></a></label>
                            <input type="file" accept="image/*" class="form-control" id="inputGroupFile02" name="footer_logo">
                            @error('footer_logo')
                                <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="currency_full_form" class="form-label">

                                <?= get_label('currency_full_form', 'Currency full form') ?> <span
                                    class="asterisk">*</span></label>
                            <input class="form-control" type="text" id="currency_full_form" name="currency_full_form"
                                placeholder="Enter currency full form"
                                value="{{ $general_settings['currency_full_form'] }}">

                            @error('currency_full_form')
                                <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="currency_symbol"
                                class="form-label"><?= get_label('currency_symbol', 'Currency symbol') ?> <span
                                    class="asterisk">*</span></label>
                            <input class="form-control" type="text" id="currency_symbol" name="currency_symbol"
                                placeholder="Enter currency symbol" value="{{ $general_settings['currency_symbol'] }}">

                            @error('currency_symbol')
                                <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                            @enderror

                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="currency_code"
                                class="form-label"><?= get_label('currency_code', 'Currency code') ?>
                                <span class="asterisk">*</span></label>
                            <input class="form-control" type="text" id="currency_code" name="currency_code"
                                placeholder="Enter currency code" value="{{ $general_settings['currency_code'] }}">

                            @error('currency_code')
                                <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                            @enderror

                        </div>
                        <div class="col-md-4 mb-3">
                            <label for=""
                                class="form-label"><?= get_label('currency_symbol_position', 'Currency symbol position') ?></label>
                            <div class="input-group">
                                <select class="form-select" name="currency_symbol_position">
                                    <option value="before"
                                        {{ old('currency_symbol_position', $general_settings['currency_symbol_position']) == 'before' ? 'selected' : '' }}>
                                        <?= get_label('before', 'Before') ?> - $100</option>
                                    <option value="after"
                                        {{ old('currency_symbol_position', $general_settings['currency_symbol_position']) == 'after' ? 'selected' : '' }}>
                                        <?= get_label('after', 'After') ?> - 100$</option>
                                </select>
                            </div>
                            <p class="text-danger error-message mt-1 text-xs"></p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for=""
                                class="form-label"><?= get_label('currency_formate', 'Currency formate') ?></label>
                            <div class="input-group">
                                <select class="form-select" name="currency_format">
                                    <option value="comma_separated"
                                        {{ old('currency_format', $general_settings['currency_format']) == 'comma_separated' ? 'selected' : '' }}>
                                        <?= get_label('comma_separated', 'Comma separated') ?> - 100,000</option>
                                    <option value="dot_separated"
                                        {{ old('currency_format', $general_settings['currency_format']) == 'dot_separated' ? 'selected' : '' }}>
                                        <?= get_label('dot_separated', 'Dot separated') ?> - 100.000</option>
                                </select>
                            </div>
                            <p class="text-danger error-message mt-1 text-xs"></p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for=""
                                class="form-label"><?= get_label('decimal_points_in_currency', 'Decimal points in currency') ?></label>
                            <input class="form-control" type="number" name="decimal_points_in_currency" step="1"
                                placeholder="Any number value - Example: if 2 - 100.00"
                                value="{{ $general_settings['decimal_points_in_currency'] }}"
                                oninput="this.value = Math.floor(this.value)" min="1">

                            @error('decimal_points_in_currency')
                                <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"
                                for="user_id"><?= get_label('system_time_zone', 'System time zone') ?>
                                <span class="asterisk">*</span></label>
                            <div class="input-group">
                                <select class="form-control js-example-basic-multiple" type="text" id="timezone"
                                    name="timezone"
                                    data-placeholder="<?= get_label('type_to_search', 'Type to search') ?>">
                                    <option value=""><?= get_label('select_time_zone', 'Select time zone') ?>
                                    </option>
                                    @foreach ($timezones as $timezone)
                                        <option value="{{ $timezone['2'] }}" data-gmt="<?= $timezone[1] ?>"
                                            {{ $general_settings['timezone'] == $timezone[2] ? 'selected' : '' }}>
                                            <span class="lh-lg">
                                                {{ $timezone['2'] }} &nbsp; - &nbsp; GMT &nbsp; {{ $timezone['1'] }}
                                                &nbsp; - &nbsp; {{ $timezone['0'] }}
                                            </span>
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('timezone')
                                <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for=""><?= get_label('date_format', 'Date format') ?> <span
                                    class="text-muted">(<?= get_label('this_date_format_will_be_used_in_the_system_everywhere', 'This date format will be used in the system everywhere') ?>)</span>
                                <span class="asterisk">*</span></label>
                            <div class="input-group">
                                <select class="form-control js-example-basic-multiple" type="text" id="date_format"
                                    name="date_format"
                                    data-placeholder="<?= get_label('type_to_search', 'Type to search') ?>">
                                    <option value=""><?= get_label('select_date_format', 'Select date format') ?>
                                    </option>
                                    <option value="DD-MM-YYYY|d-m-Y"
                                        <?= $general_settings['date_format'] == 'DD-MM-YYYY|d-m-Y' ? 'selected' : '' ?>>
                                        Day-Month-Year with leading zero (04-08-2023)</option>
                                    <option value="D-M-YY|j-n-y"
                                        <?= $general_settings['date_format'] == 'D-M-YY|j-n-y' ? 'selected' : '' ?>>
                                        Day-Month-Year with no leading zero (4-8-23)</option>
                                    <option value="MM-DD-YYYY|m-d-Y"
                                        <?= $general_settings['date_format'] == 'MM-DD-YYYY|m-d-Y' ? 'selected' : '' ?>>
                                        Month-Day-Year with leading zero (08-04-2023)</option>
                                    <option value="M-D-YY|n-j-y"
                                        <?= $general_settings['date_format'] == 'M-D-YY|n-j-y' ? 'selected' : '' ?>>
                                        Month-Day-Year with no leading zero (8-4-23)</option>
                                    <option value="YYYY-MM-DD|Y-m-d"
                                        <?= $general_settings['date_format'] == 'YYYY-MM-DD|Y-m-d' ? 'selected' : '' ?>>
                                        Year-Month-Day with leading zero (2023-08-04)</option>
                                    <option value="YY-M-D|Y-n-j"
                                        <?= $general_settings['date_format'] == 'YY-M-D|Y-n-j' ? 'selected' : '' ?>>
                                        Year-Month-Day with no leading zero (23-8-4)</option>
                                    <option value="MMMM DD, YYYY|F d, Y"
                                        <?= $general_settings['date_format'] == 'MMMM DD, YYYY|F d, Y' ? 'selected' : '' ?>>
                                        Month name-Day-Year with leading zero
                                        (August 04, 2023)</option>

                                    <option value="MMM DD, YYYY|M d, Y"
                                        <?= $general_settings['date_format'] == 'MMM DD, YYYY|M d, Y' ? 'selected' : '' ?>>
                                        Month abbreviation-Day-Year with leading zero
                                        (Aug 04, 2023)</option>

                                    <option value="DD-MMM-YYYY|d-M-Y"
                                        <?= $general_settings['date_format'] == 'DD-MMM-YYYY|d-M-Y' ? 'selected' : '' ?>>Day
                                        with leading zero, Month abbreviation, Year (04-Aug-2023)</option>
                                    <option value="DD MMM, YYYY|d M, Y"
                                        <?= $general_settings['date_format'] == 'DD MMM, YYYY|d M, Y' ? 'selected' : '' ?>>
                                        Day with leading zero, Month abbreviation, Year (04 Aug, 2023)</option>
                                    <option value="YYYY-MMM-DD|Y-M-d"
                                        <?= $general_settings['date_format'] == 'YYYY-MMM-DD|Y-M-d' ? 'selected' : '' ?>>
                                        Year, Month abbreviation, Day with leading zero (2023-Aug-04)</option>
                                    <option value="YYYY, MMM DD|Y, M d"
                                        <?= $general_settings['date_format'] == 'YYYY, MMM DD|Y, M d' ? 'selected' : '' ?>>
                                        Year, Month abbreviation, Day with leading zero (2023, Aug 04)</option>
                                </select>
                            </div>
                            @error('date_format')
                                <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="col-md-5 mb-3">
                            <label for=""
                                class="form-label"><?= get_label('toast_message_position', 'Toast message position') ?></label>
                            <i class='bx bx-info-circle text-primary' data-bs-toggle="tooltip" data-bs-placement="top"
                                title="<?= get_label('toast_position_info', 'Choose where on the screen toast messages will appear.') ?>"></i>
                            <select id="toastPosition" class="form-select" name="toast_position">
                                <option value="toast-top-right"
                                    {{ isset($general_settings['toast_position']) && $general_settings['toast_position'] == 'toast-top-right' ? 'selected' : '' }}>
                                    {{ get_label('top_right', 'Top Right') }}</option>
                                <option value="toast-top-left"
                                    {{ isset($general_settings['toast_position']) && $general_settings['toast_position'] == 'toast-top-left' ? 'selected' : '' }}>
                                    {{ get_label('top_left', 'Top Left') }}</option>
                                <option value="toast-bottom-right"
                                    {{ isset($general_settings['toast_position']) && $general_settings['toast_position'] == 'toast-bottom-right' ? 'selected' : '' }}>
                                    {{ get_label('bottom_right', 'Bottom Right') }}</option>
                                <option value="toast-bottom-left"
                                    {{ isset($general_settings['toast_position']) && $general_settings['toast_position'] == 'toast-bottom-left' ? 'selected' : '' }}>
                                    {{ get_label('bottom_left', 'Bottom Left') }}</option>
                                <option value="toast-top-full-width"
                                    {{ isset($general_settings['toast_position']) && $general_settings['toast_position'] == 'toast-top-full-width' ? 'selected' : '' }}>
                                    {{ get_label('top_full_width', 'Top Full Width') }}</option>
                                <option value="toast-bottom-full-width"
                                    {{ isset($general_settings['toast_position']) && $general_settings['toast_position'] == 'toast-bottom-full-width' ? 'selected' : '' }}>
                                    {{ get_label('bottom_full_width', 'Bottom Full Width') }}</option>
                                <option value="toast-top-center"
                                    {{ isset($general_settings['toast_position']) && $general_settings['toast_position'] == 'toast-top-center' ? 'selected' : '' }}>
                                    {{ get_label('top_center', 'Top Center') }}</option>
                                <option value="toast-bottom-center"
                                    {{ isset($general_settings['toast_position']) && $general_settings['toast_position'] == 'toast-bottom-center' ? 'selected' : '' }}>
                                    {{ get_label('bottom_center', 'Bottom Center') }}</option>
                            </select>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label for=""
                                class="form-label"><?= get_label('toast_message_time_out', 'Toast message time out') ?></label>
                            <i class='bx bx-info-circle text-primary' data-bs-toggle="tooltip" data-bs-placement="top"
                                title="<?= get_label('toast_time_out_info', 'Set the duration (in seconds) for how long toast messages will be displayed. The default is 5 seconds.') ?>"></i>
                            <input id="toastTimeout" class="form-control" type="number" name="toast_time_out"
                                step="0.1" placeholder="5"
                                value="{{ isset($general_settings['toast_time_out']) ? $general_settings['toast_time_out'] : '5' }}"
                                min="0.1">
                        </div>
                        <div class="col-md-2 d-flex align-items-end mb-3">
                            <button id="previewToast" class="btn btn-primary"
                                type="button">{{ get_label('preview_toast', 'Preview Toast') }}</button>
                        </div>


                        <div class="col-md-12 mb-3">
                            <label for="currency_symbol"
                                class="form-label"><?= get_label('footer_text', 'Footer text') ?></label>
                            <textarea id="footer_text" name="footer_text" class="form-control"><?= $general_settings['footer_text'] ?></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="address" class="form-label"><?= get_label('address', 'Address') ?></label>
                            <textarea id="company_address" name="company_address" class="form-control"><?= $general_settings['company_address'] ?></textarea>
                        </div>





                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary me-2"
                                id="submit_btn"><?= get_label('update', 'Update') ?></button>
                            <button type="reset"
                                class="btn btn-outline-secondary"><?= get_label('cancel', 'Cancel') ?></button>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    @endsection
