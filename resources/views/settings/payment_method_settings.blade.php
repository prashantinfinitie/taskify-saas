@extends('layout')
@section('title')
    <?= get_label('payment_method_settings', 'Payment method settings') ?>
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
                        <li class="breadcrumb-item active text-capitalize">
                            <?= get_label('payment_methods', 'Payment Methods') ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="col-12">
            <div class="nav-align-top nav-lg mb-4">
                <ul class="nav nav-tabs nav-fill" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link active text-capitalize text-dark" role="tab"
                            data-bs-toggle="tab" data-bs-target="#pay_pal" aria-controls="pay_pal" aria-selected="true">
                            <i class='bx bxl-paypal text-primary'></i>
                            {{ get_label('paypal', 'Pay Pal') }} </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link text-capitalize text-dark" role="tab"
                            data-bs-toggle="tab" data-bs-target="#phone_pe" aria-controls="phone_pe" aria-selected="false"
                            tabindex="-1"><i class='bx bx-rupee text-primary'></i>
                            {{ get_label('phonepe', 'PhonePe') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link text-capitalize text-dark" role="tab"
                            data-bs-toggle="tab" data-bs-target="#stripe" aria-controls="stripe" aria-selected="false"
                            tabindex="-1">
                            <i class='bx bxl-stripe text-primary'></i>
                            {{ get_label('stripe', 'Stripe') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link text-capitalize text-dark" role="tab"
                            data-bs-toggle="tab" data-bs-target="#pay_stack" aria-controls="pay_stack" aria-selected="false"
                            tabindex="-1">
                            <i class='bx bxs-coin-stack text-primary'></i>
                            {{ get_label('paystack', 'Pay Stack') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link text-capitalize text-dark" role="tab"
                            data-bs-toggle="tab" data-bs-target="#bank_transfer" aria-controls="bank_transfer"
                            aria-selected="false" tabindex="-1">
                            <i class='bx bxs-bank text-primary'></i>
                            {{ get_label('bank_transfer', 'Bank Transfer') }}</button>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade active show" id="pay_pal" role="tabpanel">
                        <div class="">
                            <form class="form-submit-event" action= "{{ route('payment_method.store_paypal_settings') }}"
                                id = "storePayPal_settings" method = "POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="redirect_url"
                                    value="{{ route('payment_method.index') . '#paypal' }}">
                                    
                                <div class ="row mb-3">
                                    <div class = "col-md-6 mb-3">
                                        <label for="paypal_client_id"
                                            class="form-label">{{ get_label('paypal_client_id', 'PayPal Client Id') }}</label>
                                        <input type="text" class="form-control" name="paypal_client_id"
                                            id="paypal_client_id" placeholder="Enter your Paypal client ID"
                                            value="<?= isset($pay_pal_settings['paypal_client_id']) ? (config('constants.ALLOW_MODIFICATION') === 0 ? str_repeat('*', strlen($pay_pal_settings['paypal_client_id'])) : $pay_pal_settings['paypal_client_id']) : '' ?>" />
                                        @error('paypal_client_id')
                                            <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class = "col-md-6 mb-3">
                                        <label for="paypal_secret_key"
                                            class="form-label">{{ get_label('paypal_secret_key', 'PayPal Secret Key') }}</label>
                                        <input type="text" class="form-control" name="paypal_secret_key"
                                            id="paypal_secret_key" placeholder="Enter Your Secret Key Here..."
                                            value="<?= isset($pay_pal_settings['paypal_secret_key']) ? (config('constants.ALLOW_MODIFICATION') === 0 ? str_repeat('*', strlen($pay_pal_settings['paypal_secret_key'])) : $pay_pal_settings['paypal_secret_key']) : '' ?>" />
                                        @error('paypal_secret_key')
                                            <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3">
                                        <label for="payment_mode"
                                            class="form-label">{{ get_label('payment_mode', 'Payment Mode') }}</label>
                                        <select class="form-select" name="payment_mode" id="payment_mode">
                                            <option value="sandbox"
                                                <?= isset($pay_pal_settings['payment_mode']) && $pay_pal_settings['payment_mode'] === 'sandbox' ? 'selected' : '' ?>>
                                                {{ get_label('sandbox', 'Sandbox (Testing)') }}</option>
                                            <option value="production"
                                                <?= isset($pay_pal_settings['payment_mode']) && $pay_pal_settings['payment_mode'] === 'production' ? 'selected' : '' ?>>
                                                {{ get_label('production', 'Production (Live)') }}</option>
                                        </select>
                                        @error('payment_mode')
                                            <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="paypal_business_email"
                                            class="form-label">{{ get_label('paypal_business_email', 'PayPal Business Email ID') }}
                                        </label>
                                        <input type="text" class="form-control" name="paypal_business_email"
                                            id="paypal_business_email" placeholder="Enter Your PayPal Business Email Id"
                                            value="<?= isset($pay_pal_settings['paypal_business_email']) ? (config('constants.ALLOW_MODIFICATION') === 0 ? str_repeat('*', strlen($pay_pal_settings['paypal_business_email'])) : $pay_pal_settings['paypal_business_email']) : '' ?>" />
                                        @error('paypal_business_email')
                                            <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-3">
                                        <label for="currency_code"
                                            class="form-label">{{ get_label('currency_code', 'Currency Code') }}</label>
                                        <select class="form-select" name="currency_code" id="currency_code">
                                            @if ($pay_pal_settings['currency_code'])
                                                <option value="{{ $pay_pal_settings['currency_code'] }}" selected>
                                                    {{ $pay_pal_settings['currency_code'] }}
                                                </option>
                                            @endif
                                            <option value="AED">United Arab Emirates Dirham (AED)</option>
                                            <option value="AFN">Afghan Afghani (AFN)</option>
                                            <option value="ALL">Albanian Lek (ALL)</option>
                                            <option value="AMD">Armenian Dram (AMD)</option>
                                            <option value="ANG">Netherlands Antillean Guilder (ANG)</option>
                                            <option value="AOA">Angolan Kwanza (AOA)</option>
                                            <option value="ARS">Argentine Peso (ARS)</option>
                                            <option value="AUD">Australian Dollar (AUD)</option>
                                            <option value="AWG">Aruban Florin (AWG)</option>
                                            <option value="AZN">Azerbaijani Manat (AZN)</option>
                                            <option value="BAM">Bosnia-Herzegovina Convertible Mark (BAM)</option>
                                            <option value="BBD">Barbadian Dollar (BBD)</option>
                                            <option value="BDT">Bangladeshi Taka (BDT)</option>
                                            <option value="BGN">Bulgarian Lev (BGN)</option>
                                            <option value="BHD">Bahraini Dinar (BHD)</option>
                                            <option value="BIF">Burundian Franc (BIF)</option>
                                            <option value="BMD">Bermudian Dollar (BMD)</option>
                                            <option value="BND">Brunei Dollar (BND)</option>
                                            <option value="BOB">Bolivian Boliviano (BOB)</option>
                                            <option value="BRL">Brazilian Real (BRL)</option>
                                            <option value="BSD">Bahamian Dollar (BSD)</option>
                                            <option value="BTN">Bhutanese Ngultrum (BTN)</option>
                                            <option value="BWP">Botswana Pula (BWP)</option>
                                            <option value="BYN">Belarusian Ruble (BYN)</option>
                                            <option value="BZD">Belize Dollar (BZD)</option>
                                            <option value="CAD">Canadian Dollar (CAD)</option>
                                            <option value="CDF">Congolese Franc (CDF)</option>
                                            <option value="CHF">Swiss Franc (CHF)</option>
                                            <option value="CLP">Chilean Peso (CLP)</option>
                                            <option value="CNY">Chinese Yuan (CNY)</option>
                                            <option value="COP">Colombian Peso (COP)</option>
                                            <option value="CRC">Costa Rican Colón (CRC)</option>
                                            <option value="CUP">Cuban Peso (CUP)</option>
                                            <option value="CVE">Cape Verdean Escudo (CVE)</option>
                                            <option value="CZK">Czech Koruna (CZK)</option>
                                            <option value="DJF">Djiboutian Franc (DJF)</option>
                                            <option value="DKK">Danish Krone (DKK)</option>
                                            <option value="DOP">Dominican Peso (DOP)</option>
                                            <option value="DZD">Algerian Dinar (DZD)</option>
                                            <option value="EGP">Egyptian Pound (EGP)</option>
                                            <option value="ERN">Eritrean Nakfa (ERN)</option>
                                            <option value="ETB">Ethiopian Birr (ETB)</option>
                                            <option value="EUR">Euro (EUR)</option>
                                            <option value="FJD">Fijian Dollar (FJD)</option>
                                            <option value="FKP">Falkland Islands Pound (FKP)</option>
                                            <option value="FOK">Faroese Króna (FOK)</option>
                                            <option value="GBP">British Pound Sterling (GBP)</option>
                                            <option value="GEL">Georgian Lari (GEL)</option>
                                            <option value="GGP">Guernsey Pound (GGP)</option>
                                            <option value="GHS">Ghanaian Cedi (GHS)</option>
                                            <option value="GIP">Gibraltar Pound (GIP)</option>
                                            <option value="GMD">Gambian Dalasi (GMD)</option>
                                            <option value="GNF">Guinean Franc (GNF)</option>
                                            <option value="GTQ">Guatemalan Quetzal (GTQ)</option>
                                            <option value="GYD">Guyanaese Dollar (GYD)</option>
                                            <option value="HKD">Hong Kong Dollar (HKD)</option>
                                            <option value="HNL">Honduran Lempira (HNL)</option>
                                            <option value="HRK">Croatian Kuna (HRK)</option>
                                            <option value="HTG">Haitian Gourde (HTG)</option>
                                            <option value="HUF">Hungarian Forint (HUF)</option>
                                            <option value="IDR">Indonesian Rupiah (IDR)</option>
                                            <option value="ILS">Israeli New Shekel (ILS)</option>
                                            <option value="IMP">Manx pound (IMP)</option>
                                            <option value="INR">Indian Rupee (INR)</option>
                                            <option value="IQD">Iraqi Dinar (IQD)</option>
                                            <option value="IRR">Iranian Rial (IRR)</option>
                                            <option value="ISK">Icelandic Króna (ISK)</option>
                                            <option value="JEP">Jersey Pound (JEP)</option>
                                            <option value="JMD">Jamaican Dollar (JMD)</option>
                                            <option value="JOD">Jordanian Dinar (JOD)</option>
                                            <option value="JPY">Japanese Yen (JPY)</option>
                                            <option value="KES">Kenyan Shilling (KES)</option>
                                            <option value="KGS">Kyrgystani Som (KGS)</option>
                                            <option value="KHR">Cambodian Riel (KHR)</option>
                                            <option value="KID">Kiribati Dollar (KID)</option>
                                            <option value="KMF">Comorian Franc (KMF)</option>
                                            <option value="KRW">South Korean Won (KRW)</option>
                                            <option value="KWD">Kuwaiti Dinar (KWD)</option>
                                            <option value="KYD">Cayman Islands Dollar (KYD)</option>
                                            <option value="KZT">Kazakhstani Tenge (KZT)</option>
                                            <option value="LAK">Laotian Kip (LAK)</option>
                                            <option value="LBP">Lebanese Pound (LBP)</option>
                                            <option value="LKR">Sri Lankan Rupee (LKR)</option>
                                            <option value="LRD">Liberian Dollar (LRD)</option>
                                            <option value="LSL">Lesotho Loti (LSL)</option>
                                            <option value="LYD">Libyan Dinar (LYD)</option>
                                            <option value="MAD">Moroccan Dirham (MAD)</option>
                                            <option value="MDL">Moldovan Leu (MDL)</option>
                                            <option value="MGA">Malagasy Ariary (MGA)</option>
                                            <option value="MKD">Macedonian Denar (MKD)</option>
                                            <option value="MMK">Myanmar Kyat (MMK)</option>
                                            <option value="MNT">Mongolian Tugrik (MNT)</option>
                                            <option value="MOP">Macanese Pataca (MOP)</option>
                                            <option value="MRU">Mauritanian Ouguiya (MRU)</option>
                                            <option value="MUR">Mauritian Rupee (MUR)</option>
                                            <option value="MVR">Maldivian Rufiyaa (MVR)</option>
                                            <option value="MWK">Malawian Kwacha (MWK)</option>
                                            <option value="MXN">Mexican Peso (MXN)</option>
                                            <option value="MYR">Malaysian Ringgit (MYR)</option>
                                            <option value="MZN">Mozambican Metical (MZN)</option>
                                            <option value="NAD">Namibian Dollar (NAD)</option>
                                            <option value="NGN">Nigerian Naira (NGN)</option>
                                            <option value="NIO">Nicaraguan Córdoba (NIO)</option>
                                            <option value="NOK">Norwegian Krone (NOK)</option>
                                            <option value="NPR">Nepalese Rupee (NPR)</option>
                                            <option value="NZD">New Zealand Dollar (NZD)</option>
                                            <option value="OMR">Omani Rial (OMR)</option>
                                            <option value="PAB">Panamanian Balboa (PAB)</option>
                                            <option value="PEN">Peruvian Nuevo Sol (PEN)</option>
                                            <option value="PGK">Papua New Guinean Kina (PGK)</option>
                                            <option value="PHP">Philippine Peso (PHP)</option>
                                            <option value="PKR">Pakistani Rupee (PKR)</option>
                                            <option value="PLN">Polish Zloty (PLN)</option>
                                            <option value="PYG">Paraguayan Guarani (PYG)</option>
                                            <option value="QAR">Qatari Rial (QAR)</option>
                                            <option value="RON">Romanian Leu (RON)</option>
                                            <option value="RSD">Serbian Dinar (RSD)</option>
                                            <option value="RUB">Russian Ruble (RUB)</option>
                                            <option value="RWF">Rwandan Franc (RWF)</option>
                                            <option value="SAR">Saudi Riyal (SAR)</option>
                                            <option value="SBD">Solomon Islands Dollar (SBD)</option>
                                            <option value="SCR">Seychellois Rupee (SCR)</option>
                                            <option value="SDG">Sudanese Pound (SDG)</option>
                                            <option value="SEK">Swedish Krona (SEK)</option>
                                            <option value="SGD">Singapore Dollar (SGD)</option>
                                            <option value="SHP">Saint Helena Pound (SHP)</option>
                                            <option value="SLL">Sierra Leonean Leone (SLL)</option>
                                            <option value="SOS">Somali Shilling (SOS)</option>
                                            <option value="SRD">Surinamese Dollar (SRD)</option>
                                            <option value="SSP">South Sudanese Pound (SSP)</option>
                                            <option value="STN">São Tomé and Príncipe Dobra (STN)</option>
                                            <option value="SYP">Syrian Pound (SYP)</option>
                                            <option value="SZL">Swazi Lilangeni (SZL)</option>
                                            <option value="THB">Thai Baht (THB)</option>
                                            <option value="TJS">Tajikistani Somoni (TJS)</option>
                                            <option value="TMT">Turkmenistani Manat (TMT)</option>
                                            <option value="TND">Tunisian Dinar (TND)</option>
                                            <option value="TOP">Tongan Pa'anga (TOP)</option>
                                            <option value="TRY">Turkish Lira (TRY)</option>
                                            <option value="TTD">Trinidad and Tobago Dollar (TTD)</option>
                                            <option value="TWD">New Taiwan Dollar (TWD)</option>
                                            <option value="TZS">Tanzanian Shilling (TZS)</option>
                                            <option value="UAH">Ukrainian Hryvnia (UAH)</option>
                                            <option value="UGX">Ugandan Shilling (UGX)</option>
                                            <option value="USD">United States Dollar (USD)</option>
                                            <option value="UYU">Uruguayan Peso (UYU)</option>
                                            <option value="UZS">Uzbekistan Som (UZS)</option>
                                            <option value="VES">Venezuelan Bolívar (VES)</option>
                                            <option value="VND">Vietnamese Dong (VND)</option>
                                            <option value="VUV">Vanuatu Vatu (VUV)</option>
                                            <option value="WST">Samoan Tala (WST)</option>
                                            <option value="XAF">CFA Franc BEAC (XAF)</option>
                                            <option value="XCD">East Caribbean Dollar (XCD)</option>
                                            <option value="XDR">Special Drawing Rights (XDR)</option>
                                            <option value="XOF">CFA Franc BCEAO (XOF)</option>
                                            <option value="XPF">CFP Franc (XPF)</option>
                                            <option value="YER">Yemeni Rial (YER)</option>
                                            <option value="ZAR">South African Rand (ZAR)</option>
                                            <option value="ZMW">Zambian Kwacha (ZMW)</option>
                                            <option value="ZWL">Zimbabwean Dollar (ZWL)</option>
                                        </select>
                                        @error('currency_code')
                                            <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                        <div class="col-md-12">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input payment-method-toggle" type="checkbox" id="paypal_is_active" name="is_active" value="1"
                                                    <?= isset($pay_pal_settings['is_active']) && $pay_pal_settings['is_active'] ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="paypal_is_active">{{ get_label('enable_paypal', 'Enable PayPal') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                <div class="row mb-3">
                                    <div class="col-sm-8">
                                        <button type="submit" class="btn btn-outline-primary">
                                            <?= get_label('save', 'Save') ?>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="phone_pe" role="tabpanel">
                        <div class="">
                            <form class="form-submit-event"
                                action= "{{ route('payment_method.store_phonepe_settings') }}"
                                id = "storePhonePe_settings" method = "POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="redirect_url"
                                    value="{{ route('payment_method.index') . '#phone_pe' }}">

                                    
                                <div class ="row mb-3">
                                    <div class = "col-md-6 mb-3">
                                        <label for="merchant_id"
                                            class="form-label">{{ get_label('merchant_id', 'Merchant Id') }}</label>
                                        <input type="text" class="form-control" name="merchant_id" id="merchant_id"
                                            placeholder="Enter your Merchant ID"
                                            value="<?= isset($phone_pe_settings['merchant_id']) ? (config('constants.ALLOW_MODIFICATION') === 0 ? str_repeat('*', strlen($phone_pe_settings['merchant_id'])) : $phone_pe_settings['merchant_id']) : '' ?>" />
                                        @error('merchant_id')
                                            <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class = "col-md-6 mb-3">
                                        <label for="app_id"
                                            class="form-label">{{ get_label('app_id', 'App Id') }}</label>
                                        <input type="text" class="form-control" name="app_id" id="app_id"
                                            placeholder="Enter Your PhonePe App Id Here..."
                                            value="<?= isset($phone_pe_settings['app_id']) ? (config('constants.ALLOW_MODIFICATION') === 0 ? str_repeat('*', strlen($phone_pe_settings['app_id'])) : $phone_pe_settings['app_id']) : '' ?>" />
                                        @error('app_id')
                                            <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class = "col-md-6 mb-3">
                                        <label for="salt_index"
                                            class="form-label">{{ get_label('salt_index', 'Salt Index') }}</label>
                                        <input type="text" class="form-control" name="salt_index" id="salt_index"
                                            placeholder="Enter your Salt Index value"
                                            value="<?= isset($phone_pe_settings['salt_index']) ? (config('constants.ALLOW_MODIFICATION') === 0 ? str_repeat('*', strlen($phone_pe_settings['salt_index'])) : $phone_pe_settings['salt_index']) : '' ?>" />
                                        @error('salt_index')
                                            <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class = "col-md-6 mb-3">
                                        <label for="salt_key"
                                            class="form-label">{{ get_label('salt_key', 'Salt Key') }}</label>
                                        <input type="text" class="form-control" name="salt_key" id="salt_key"
                                            placeholder="Enter your Salt Key value"
                                            value="<?= isset($phone_pe_settings['salt_key']) ? (config('constants.ALLOW_MODIFICATION') === 0 ? str_repeat('*', strlen($phone_pe_settings['salt_key'])) : $phone_pe_settings['salt_key']) : '' ?>" />
                                        @error('salt_key')
                                            <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class = "col-md-6 mb-3">
                                        <label for="phonepe_mode"
                                            class="form-label">{{ get_label('phonepe_mode', 'PhonePe Mode [ SANDBOX / UAT / PRODUCTION ]') }}</label>
                                        <select class="form-select" name="phonepe_mode" id="phonepe_mode">
                                            <option value="sandbox"
                                                <?= isset($phone_pe_settings['phonepe_mode']) && $phone_pe_settings['phonepe_mode'] === 'sandbox' ? 'selected' : '' ?>>
                                                {{ get_label('sandbox', 'Sandbox (Testing)') }}</option>
                                            <option value="production"
                                                <?= isset($phone_pe_settings['phonepe_mode']) && $phone_pe_settings['phonepe_mode'] === 'production' ? 'selected' : '' ?>>
                                                {{ get_label('production', 'Production (Live)') }}</option>
                                            <option value="UAT"
                                                <?= isset($phone_pe_settings['phonepe_mode']) && $phone_pe_settings['phonepe_mode'] === 'UAT' ? 'selected' : '' ?>>
                                                {{ get_label('UAT', 'UAT') }}</option>
                                        </select>
                                        @error('phonepe_mode')
                                            <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class = "col-md-6 mb-3">
                                        <label for="payment_endpoint_url"
                                            class="form-label">{{ get_label('payment_endpoint_url', 'Payment Endpoint Url') }}</label>
                                        <input type="text" class="form-control" name="payment_endpoint_url"
                                            id="payment_endpoint_url" placeholder="Enter your Payment Endpoint Url "
                                            value="<?= isset($phone_pe_settings['payment_endpoint_url']) ? (config('constants.ALLOW_MODIFICATION') === 0 ? str_repeat('*', strlen($phone_pe_settings['payment_endpoint_url'])) : $phone_pe_settings['payment_endpoint_url']) : '' ?>" />
                                        @error('payment_endpoint_url')
                                            <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                        <div class="col-md-12">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input payment-method-toggle" type="checkbox" id="phonepe_is_active" name="is_active" value="1"
                                                    <?= isset($phone_pe_settings['is_active']) && $phone_pe_settings['is_active'] ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="phonepe_is_active">{{ get_label('enable_phonepe', 'Enable PhonePe') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                <div class="row mb-3">
                                    <div class="col-sm-8">
                                        <button type="submit" class="btn btn-outline-primary">
                                            <?= get_label('save', 'Save') ?>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="stripe" role="tabpanel">
                        <div class="">
                            <form class="form-submit-event" action= "{{ route('payment_method.store_stripe_settings') }}"
                                id = "storeStripe_settings" method = "POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="redirect_url"
                                    value="{{ route('payment_method.index') . '#stripe' }}">

                                    

                                <div class ="row mb-3">
                                    <div class = "col-md-6 mb-3">
                                        <label for="stripe_publishable_key"
                                            class="form-label">{{ get_label('stripe_publishable_key', 'Stripe Publishable Key') }}</label>
                                        <input type="text" class="form-control" name="stripe_publishable_key"
                                            id="stripe_publishable_key" placeholder="Enter your Stripe Publishable Key"
                                            value="<?= isset($stripe_settings['stripe_publishable_key']) ? (config('constants.ALLOW_MODIFICATION') === 0 ? str_repeat('*', strlen($stripe_settings['stripe_publishable_key'])) : $stripe_settings['stripe_publishable_key']) : '' ?>" />
                                        @error('stripe_publishable_key')
                                            <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class = "col-md-6 mb-3">
                                        <label for="stripe_secret_key"
                                            class="form-label">{{ get_label('stripe_secret_key', 'Stripe Secret Key') }}</label>
                                        <input type="text" class="form-control" name="stripe_secret_key"
                                            id="stripe_secret_key" placeholder="Enter Your Stripe Secret Key Here..."
                                            value="<?= isset($stripe_settings['stripe_secret_key']) ? (config('constants.ALLOW_MODIFICATION') === 0 ? str_repeat('*', strlen($stripe_settings['stripe_secret_key'])) : $stripe_settings['stripe_secret_key']) : '' ?>" />
                                        @error('stripe_secret_key')
                                            <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class = "col-md-6 mb-3">
                                        <label for="payment_endpoint_url"
                                            class="form-label">{{ get_label('payment_endpoint_url', 'Payment Endpoint URL (Set this as Endpoint URL in your Stripe account)') }}</label>
                                        <input type="text" class="form-control" name="payment_endpoint_url"
                                            id="payment_endpoint_url" placeholder="Enter your Payment Endpoint URL"
                                            value="<?= isset($stripe_settings['payment_endpoint_url']) ? (config('constants.ALLOW_MODIFICATION') === 0 ? str_repeat('*', strlen($stripe_settings['payment_endpoint_url'])) : $stripe_settings['payment_endpoint_url']) : '' ?>"
                                            disabled />
                                        <input type="hidden" name="payment_endpoint_url"
                                            value="<?= $stripe_settings['payment_endpoint_url'] ?? '' ?>">
                                        @error('payment_endpoint_url')
                                            <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class = "col-md-6 mb-3">
                                        <label for="stripe_webhook_secret_key"
                                            class="form-label">{{ get_label('stripe_webhook_secret_key', 'Stripe Webhook Secret Key') }}</label>
                                        <input type="text" class="form-control" name="stripe_webhook_secret_key"
                                            id="stripe_webhook_secret_key"
                                            placeholder="Enter your Stripe Webhook Secret Key"
                                            value="<?= isset($stripe_settings['stripe_webhook_secret_key']) ? (config('constants.ALLOW_MODIFICATION') === 0 ? str_repeat('*', strlen($stripe_settings['stripe_webhook_secret_key'])) : $stripe_settings['stripe_webhook_secret_key']) : '' ?>" />
                                        @error('stripe_webhook_secret_key')
                                            <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class = "col-md-6 mb-3">
                                        <label for="payment_mode"
                                            class="form-label">{{ get_label('payment_mode', 'Payment Mode [ SANDBOX / PRODUCTION ]') }}</label>
                                        <select class="form-select" name="payment_mode" id="payment_mode">
                                            <option value="sandbox"
                                                <?= isset($stripe_settings['payment_mode']) && $stripe_settings['payment_mode'] === 'sandbox' ? 'selected' : '' ?>>
                                                {{ get_label('sandbox', 'Sandbox (Testing)') }}</option>
                                            <option value="production"
                                                <?= isset($stripe_settings['payment_mode']) && $stripe_settings['payment_mode'] === 'production' ? 'selected' : '' ?>>
                                                {{ get_label('production', 'Production (Live)') }}</option>
                                        </select>
                                        @error('payment_mode')
                                            <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="currency_code"
                                            class="form-label">{{ get_label('currency_code', 'Currency Code') }}</label>
                                        <select class="form-select" name="currency_code" id="currency_code">
                                            @if ($stripe_settings['currency_code'])
                                                <option value="{{ $stripe_settings['currency_code'] }}" selected>
                                                    {{ $stripe_settings['currency_code'] }}
                                                </option>
                                            @endif
                                            <option value="AED">United Arab Emirates Dirham (AED)</option>
                                            <option value="AFN">Afghan Afghani (AFN)</option>
                                            <option value="ALL">Albanian Lek (ALL)</option>
                                            <option value="AMD">Armenian Dram (AMD)</option>
                                            <option value="ANG">Netherlands Antillean Guilder (ANG)</option>
                                            <option value="AOA">Angolan Kwanza (AOA)</option>
                                            <option value="ARS">Argentine Peso (ARS)</option>
                                            <option value="AUD">Australian Dollar (AUD)</option>
                                            <option value="AWG">Aruban Florin (AWG)</option>
                                            <option value="AZN">Azerbaijani Manat (AZN)</option>
                                            <option value="BAM">Bosnia-Herzegovina Convertible Mark (BAM)</option>
                                            <option value="BBD">Barbadian Dollar (BBD)</option>
                                            <option value="BDT">Bangladeshi Taka (BDT)</option>
                                            <option value="BGN">Bulgarian Lev (BGN)</option>
                                            <option value="BHD">Bahraini Dinar (BHD)</option>
                                            <option value="BIF">Burundian Franc (BIF)</option>
                                            <option value="BMD">Bermudian Dollar (BMD)</option>
                                            <option value="BND">Brunei Dollar (BND)</option>
                                            <option value="BOB">Bolivian Boliviano (BOB)</option>
                                            <option value="BRL">Brazilian Real (BRL)</option>
                                            <option value="BSD">Bahamian Dollar (BSD)</option>
                                            <option value="BTN">Bhutanese Ngultrum (BTN)</option>
                                            <option value="BWP">Botswana Pula (BWP)</option>
                                            <option value="BYN">Belarusian Ruble (BYN)</option>
                                            <option value="BZD">Belize Dollar (BZD)</option>
                                            <option value="CAD">Canadian Dollar (CAD)</option>
                                            <option value="CDF">Congolese Franc (CDF)</option>
                                            <option value="CHF">Swiss Franc (CHF)</option>
                                            <option value="CLP">Chilean Peso (CLP)</option>
                                            <option value="CNY">Chinese Yuan (CNY)</option>
                                            <option value="COP">Colombian Peso (COP)</option>
                                            <option value="CRC">Costa Rican Colón (CRC)</option>
                                            <option value="CUP">Cuban Peso (CUP)</option>
                                            <option value="CVE">Cape Verdean Escudo (CVE)</option>
                                            <option value="CZK">Czech Koruna (CZK)</option>
                                            <option value="DJF">Djiboutian Franc (DJF)</option>
                                            <option value="DKK">Danish Krone (DKK)</option>
                                            <option value="DOP">Dominican Peso (DOP)</option>
                                            <option value="DZD">Algerian Dinar (DZD)</option>
                                            <option value="EGP">Egyptian Pound (EGP)</option>
                                            <option value="ERN">Eritrean Nakfa (ERN)</option>
                                            <option value="ETB">Ethiopian Birr (ETB)</option>
                                            <option value="EUR">Euro (EUR)</option>
                                            <option value="FJD">Fijian Dollar (FJD)</option>
                                            <option value="FKP">Falkland Islands Pound (FKP)</option>
                                            <option value="FOK">Faroese Króna (FOK)</option>
                                            <option value="GBP">British Pound Sterling (GBP)</option>
                                            <option value="GEL">Georgian Lari (GEL)</option>
                                            <option value="GGP">Guernsey Pound (GGP)</option>
                                            <option value="GHS">Ghanaian Cedi (GHS)</option>
                                            <option value="GIP">Gibraltar Pound (GIP)</option>
                                            <option value="GMD">Gambian Dalasi (GMD)</option>
                                            <option value="GNF">Guinean Franc (GNF)</option>
                                            <option value="GTQ">Guatemalan Quetzal (GTQ)</option>
                                            <option value="GYD">Guyanaese Dollar (GYD)</option>
                                            <option value="HKD">Hong Kong Dollar (HKD)</option>
                                            <option value="HNL">Honduran Lempira (HNL)</option>
                                            <option value="HRK">Croatian Kuna (HRK)</option>
                                            <option value="HTG">Haitian Gourde (HTG)</option>
                                            <option value="HUF">Hungarian Forint (HUF)</option>
                                            <option value="IDR">Indonesian Rupiah (IDR)</option>
                                            <option value="ILS">Israeli New Shekel (ILS)</option>
                                            <option value="IMP">Manx pound (IMP)</option>
                                            <option value="INR">Indian Rupee (INR)</option>
                                            <option value="IQD">Iraqi Dinar (IQD)</option>
                                            <option value="IRR">Iranian Rial (IRR)</option>
                                            <option value="ISK">Icelandic Króna (ISK)</option>
                                            <option value="JEP">Jersey Pound (JEP)</option>
                                            <option value="JMD">Jamaican Dollar (JMD)</option>
                                            <option value="JOD">Jordanian Dinar (JOD)</option>
                                            <option value="JPY">Japanese Yen (JPY)</option>
                                            <option value="KES">Kenyan Shilling (KES)</option>
                                            <option value="KGS">Kyrgystani Som (KGS)</option>
                                            <option value="KHR">Cambodian Riel (KHR)</option>
                                            <option value="KID">Kiribati Dollar (KID)</option>
                                            <option value="KMF">Comorian Franc (KMF)</option>
                                            <option value="KRW">South Korean Won (KRW)</option>
                                            <option value="KWD">Kuwaiti Dinar (KWD)</option>
                                            <option value="KYD">Cayman Islands Dollar (KYD)</option>
                                            <option value="KZT">Kazakhstani Tenge (KZT)</option>
                                            <option value="LAK">Laotian Kip (LAK)</option>
                                            <option value="LBP">Lebanese Pound (LBP)</option>
                                            <option value="LKR">Sri Lankan Rupee (LKR)</option>
                                            <option value="LRD">Liberian Dollar (LRD)</option>
                                            <option value="LSL">Lesotho Loti (LSL)</option>
                                            <option value="LYD">Libyan Dinar (LYD)</option>
                                            <option value="MAD">Moroccan Dirham (MAD)</option>
                                            <option value="MDL">Moldovan Leu (MDL)</option>
                                            <option value="MGA">Malagasy Ariary (MGA)</option>
                                            <option value="MKD">Macedonian Denar (MKD)</option>
                                            <option value="MMK">Myanmar Kyat (MMK)</option>
                                            <option value="MNT">Mongolian Tugrik (MNT)</option>
                                            <option value="MOP">Macanese Pataca (MOP)</option>
                                            <option value="MRU">Mauritanian Ouguiya (MRU)</option>
                                            <option value="MUR">Mauritian Rupee (MUR)</option>
                                            <option value="MVR">Maldivian Rufiyaa (MVR)</option>
                                            <option value="MWK">Malawian Kwacha (MWK)</option>
                                            <option value="MXN">Mexican Peso (MXN)</option>
                                            <option value="MYR">Malaysian Ringgit (MYR)</option>
                                            <option value="MZN">Mozambican Metical (MZN)</option>
                                            <option value="NAD">Namibian Dollar (NAD)</option>
                                            <option value="NGN">Nigerian Naira (NGN)</option>
                                            <option value="NIO">Nicaraguan Córdoba (NIO)</option>
                                            <option value="NOK">Norwegian Krone (NOK)</option>
                                            <option value="NPR">Nepalese Rupee (NPR)</option>
                                            <option value="NZD">New Zealand Dollar (NZD)</option>
                                            <option value="OMR">Omani Rial (OMR)</option>
                                            <option value="PAB">Panamanian Balboa (PAB)</option>
                                            <option value="PEN">Peruvian Nuevo Sol (PEN)</option>
                                            <option value="PGK">Papua New Guinean Kina (PGK)</option>
                                            <option value="PHP">Philippine Peso (PHP)</option>
                                            <option value="PKR">Pakistani Rupee (PKR)</option>
                                            <option value="PLN">Polish Zloty (PLN)</option>
                                            <option value="PYG">Paraguayan Guarani (PYG)</option>
                                            <option value="QAR">Qatari Rial (QAR)</option>
                                            <option value="RON">Romanian Leu (RON)</option>
                                            <option value="RSD">Serbian Dinar (RSD)</option>
                                            <option value="RUB">Russian Ruble (RUB)</option>
                                            <option value="RWF">Rwandan Franc (RWF)</option>
                                            <option value="SAR">Saudi Riyal (SAR)</option>
                                            <option value="SBD">Solomon Islands Dollar (SBD)</option>
                                            <option value="SCR">Seychellois Rupee (SCR)</option>
                                            <option value="SDG">Sudanese Pound (SDG)</option>
                                            <option value="SEK">Swedish Krona (SEK)</option>
                                            <option value="SGD">Singapore Dollar (SGD)</option>
                                            <option value="SHP">Saint Helena Pound (SHP)</option>
                                            <option value="SLL">Sierra Leonean Leone (SLL)</option>
                                            <option value="SOS">Somali Shilling (SOS)</option>
                                            <option value="SRD">Surinamese Dollar (SRD)</option>
                                            <option value="SSP">South Sudanese Pound (SSP)</option>
                                            <option value="STN">São Tomé and Príncipe Dobra (STN)</option>
                                            <option value="SYP">Syrian Pound (SYP)</option>
                                            <option value="SZL">Swazi Lilangeni (SZL)</option>
                                            <option value="THB">Thai Baht (THB)</option>
                                            <option value="TJS">Tajikistani Somoni (TJS)</option>
                                            <option value="TMT">Turkmenistani Manat (TMT)</option>
                                            <option value="TND">Tunisian Dinar (TND)</option>
                                            <option value="TOP">Tongan Pa'anga (TOP)</option>
                                            <option value="TRY">Turkish Lira (TRY)</option>
                                            <option value="TTD">Trinidad and Tobago Dollar (TTD)</option>
                                            <option value="TWD">New Taiwan Dollar (TWD)</option>
                                            <option value="TZS">Tanzanian Shilling (TZS)</option>
                                            <option value="UAH">Ukrainian Hryvnia (UAH)</option>
                                            <option value="UGX">Ugandan Shilling (UGX)</option>
                                            <option value="USD">United States Dollar (USD)</option>
                                            <option value="UYU">Uruguayan Peso (UYU)</option>
                                            <option value="UZS">Uzbekistan Som (UZS)</option>
                                            <option value="VES">Venezuelan Bolívar (VES)</option>
                                            <option value="VND">Vietnamese Dong (VND)</option>
                                            <option value="VUV">Vanuatu Vatu (VUV)</option>
                                            <option value="WST">Samoan Tala (WST)</option>
                                            <option value="XAF">CFA Franc BEAC (XAF)</option>
                                            <option value="XCD">East Caribbean Dollar (XCD)</option>
                                            <option value="XDR">Special Drawing Rights (XDR)</option>
                                            <option value="XOF">CFA Franc BCEAO (XOF)</option>
                                            <option value="XPF">CFP Franc (XPF)</option>
                                            <option value="YER">Yemeni Rial (YER)</option>
                                            <option value="ZAR">South African Rand (ZAR)</option>
                                            <option value="ZMW">Zambian Kwacha (ZMW)</option>
                                            <option value="ZWL">Zimbabwean Dollar (ZWL)</option>
                                        </select>
                                        @error('currency_code')
                                            <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                        <div class="col-md-12">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input payment-method-toggle" type="checkbox" id="stripe_is_active" name="is_active" value="1"
                                                    <?= isset($stripe_settings['is_active']) && $stripe_settings['is_active'] ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="stripe_is_active">{{ get_label('enable_stripe', 'Enable Stripe') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                <div class="row mb-3">
                                    <div class="col-sm-8">
                                        <button type="submit" class="btn btn-outline-primary">
                                            <?= get_label('save', 'Save') ?>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pay_stack" role="tabpanel">
                        <form class="form-submit-event" action= "{{ route('payment_method.store_paystack_settings') }}"
                            id = "storePayStack_settings" method = "POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="redirect_url"
                                value="{{ route('payment_method.index') . '#pay_stack' }}">

                               

                            <div class ="row mb-3">
                                <div class = "col-md-6 mb-3">
                                    <label for="paystack_key_id"
                                        class="form-label">{{ get_label('paystack_key_id', 'Paystack Key Id') }}</label>
                                    <input type="text" class="form-control" name="paystack_key_id"
                                        id="paystack_key_id" placeholder="Enter your Paystack Key ID"
                                        value="<?= isset($paystack_settings['paystack_key_id']) ? (config('constants.ALLOW_MODIFICATION') === 0 ? str_repeat('*', strlen($paystack_settings['paystack_key_id'])) : $paystack_settings['paystack_key_id']) : '' ?>" />
                                    @error('paystack_key_id')
                                        <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class = "col-md-6 mb-3">
                                    <label for="paystack_secret_key"
                                        class="form-label">{{ get_label('paystack_secret_key', 'Paystack Secret Key') }}</label>
                                    <input type="text" class="form-control" name="paystack_secret_key"
                                        id="paystack_secret_key" placeholder="Enter Your Secret Key Here..."
                                        value="<?= isset($paystack_settings['paystack_secret_key']) ? (config('constants.ALLOW_MODIFICATION') === 0 ? str_repeat('*', strlen($paystack_settings['paystack_secret_key'])) : $paystack_settings['paystack_secret_key']) : '' ?>" />
                                    @error('paystack_secret_key')
                                        <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class = "col-md-6 mb-3">
                                    <label for="payment_endpoint_url"
                                        class="form-label">{{ get_label('payment_endpoint_url', 'Payment Endpoint URL (Set this as Endpoint URL in your  account)') }}</label>
                                    <input type="text" class="form-control" name="payment_endpoint_url"
                                        id="payment_endpoint_url" placeholder="Enter your Payment Endpoint URL"
                                        value="<?= isset($paystack_settings['payment_endpoint_url']) ? (config('constants.ALLOW_MODIFICATION') === 0 ? str_repeat('*', strlen($paystack_settings['payment_endpoint_url'])) : $paystack_settings['payment_endpoint_url']) : '' ?>"
                                        disabled />
                                    <input type="hidden" name="payment_endpoint_url"
                                        value="<?= $paystack_settings['payment_endpoint_url'] ?? '' ?>">
                                    @error('payment_endpoint_url')
                                        <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                    @enderror
                                </div>
                                 <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input payment-method-toggle" type="checkbox" id="stripe_is_active" name="is_active" value="1"
                                                <?= isset($paystack_settings['is_active']) && $paystack_settings['is_active'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="stripe_is_active">{{ get_label('enable_Paystack', 'Enable Paystack') }}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class = "col-md-6 mb-3">
                                    <div class="col-sm-8 d-inline-flex justify-content-between mt-4 p-1">
                                        <button type="submit" class="btn btn-outline-primary">
                                            <?= get_label('save', 'Save') ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="bank_transfer" role="tabpanel">
                        <form class="form-submit-event"
                            action= "{{ route('payment_method.store_bank_transfer_settings') }}"
                            id = "storeBankTransferSettings" method = "POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="redirect_url"
                                value="{{ route('payment_method.index') . '#bank_transfer' }}">

                                
                            <div class ="row mb-3">
                                <div class = "col-md-6 mb-3">
                                    <label for="bank_name"
                                        class="form-label">{{ get_label('bank_name', 'Bank Name') }}</label>
                                    <input type="text" class="form-control" name="bank_name" id="bank_name"
                                        placeholder="Enter your Bank Name"
                                        value="<?= isset($bank_transfer_settings['bank_name']) ? (config('constants.ALLOW_MODIFICATION') === 0 ? str_repeat('*', strlen($bank_transfer_settings['bank_name'])) : $bank_transfer_settings['bank_name']) : '' ?>" />
                                    @error('bank_name')
                                        <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class = "col-md-6 mb-3">
                                    <label for="bank_code"
                                        class="form-label">{{ get_label('bank_code', 'Bank Code') }}</label>
                                    <input type="text" class="form-control" name="bank_code" id="bank_code"
                                        placeholder="Enter your Bank Code"
                                        value="<?= isset($bank_transfer_settings['bank_code']) ? (config('constants.ALLOW_MODIFICATION') === 0 ? str_repeat('*', strlen($bank_transfer_settings['bank_code'])) : $bank_transfer_settings['bank_code']) : '' ?>" />
                                    @error('bank_code')
                                        <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class = "col-md-4 mb-3">
                                    <label for="account_name"
                                        class="form-label">{{ get_label('account_name', 'Account Name') }}</label>
                                    <input type="text" class="form-control" name="account_name" id="account_name"
                                        placeholder="Enter Your Bank Account Name..."
                                        value="<?= isset($bank_transfer_settings['account_name']) ? (config('constants.ALLOW_MODIFICATION') === 0 ? str_repeat('*', strlen($bank_transfer_settings['account_name'])) : $bank_transfer_settings['account_name']) : '' ?>" />
                                    @error('account_name')
                                        <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class = "col-md-4 mb-3">
                                    <label for="account_number"
                                        class="form-label">{{ get_label('account_number', 'Account Number') }}</label>
                                    <input type="text" class="form-control" name="account_number" id="account_number"
                                        placeholder="Enter Your Bank Account Number..."
                                        value="<?= isset($bank_transfer_settings['account_number']) ? (config('constants.ALLOW_MODIFICATION') === 0 ? str_repeat('*', strlen($bank_transfer_settings['account_number'])) : $bank_transfer_settings['account_number']) : '' ?>" />
                                    @error('account_number')
                                        <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class = "col-md-4 mb-3">
                                    <label for="swift_code"
                                        class="form-label">{{ get_label('swift_code', 'Swift Code') }}</label>
                                    <input type="text" class="form-control" name="swift_code" id="swift_code"
                                        placeholder="Enter Your Bank Swift Code..."
                                        value="<?= isset($bank_transfer_settings['swift_code']) ? (config('constants.ALLOW_MODIFICATION') === 0 ? str_repeat('*', strlen($bank_transfer_settings['swift_code'])) : $bank_transfer_settings['swift_code']) : '' ?>" />
                                    @error('swift_code')
                                        <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                    @enderror
                                </div>

                            </div>
                            <div class="row">
                                <div class = "col-md-12 mb-1">
                                    <label for="extra_notes"
                                        class="form-label">{{ get_label('extra_notes', 'Extra Notes') }}</label>
                                    <textarea class="form-control" name="extra_notes" id="extra_notes" rows="3" placeholder="Enter Extra Notes">{{ $bank_transfer_settings['extra_notes'] ?? '' }}</textarea>
                                    @error('extra_notes')
                                        <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input payment-method-toggle" type="checkbox" id="paypal_is_active" name="is_active" value="1"
                                                <?= isset($bank_transfer_settings['is_active']) && $bank_transfer_settings['is_active'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="paypal_is_active">{{ get_label('enable_bank_transfer', 'Enable Bank Transfer') }}</label>
                                        </div>
                                    </div>
                                </div>
                            <div class="row mt-0">
                                <div class = "col-md-8 mb-3">
                                    <div class="col-sm-8 d-inline-flex justify-content-between mt-4 p-1">
                                        <button type="submit" class="btn btn-outline-primary">
                                            <?= get_label('save', 'Save') ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/js/pages/payment-methods.js') }}"></script>
@endsection
