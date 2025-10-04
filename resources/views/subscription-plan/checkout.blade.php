@extends('layout')
@section('title')
    <?= get_label('checkout', 'Checkout') ?>
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
                            <a
                                href="{{ route('subscription-plan.index') }}"><?= get_label('subscription_plan', 'Subscription Plan') ?></a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('subscription-plan.buy-plan') }}"><?= get_label('buy_plan', 'Buy Plan') ?></a>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('checkout', 'Checkout') ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="container-fluid">
            <div class="card">
                <div class="card-body row">
                    <div class="col-lg-6 card-body border-end">
                        <h4 class="mb-2">{{ get_label('checkout', 'Checkout') }}</h4>
                        <p class="mb-0">
                            {{ get_label('checkoutDescription1', 'All plans include advanced tools and features to boost your product.') }}
                            <!-- Display Plan Details -->
                        <div class="row my-2 mb-2 py-4">
                            <div class="col-md mb-md-0 mb-2">
                                <h5 class="mb-1">{{ get_label('plan_details', 'Plan Details') }}</h5>
                                <ul class="list-unstyled">
                                    <li>
                                        <h3 class="text-primary me-2">{{ $plan->name }}<small class="text-muted"> -
                                                {{ $plan->description }}</small> </h3>
                                    </li>
                                    @php
                                        $modules = json_decode($plan->modules);
                                        $checkedModules = [];
                                        $uncheckedModules = [];
                                        foreach (config('taskify.modules') as $moduleName => $moduleData) {
                                            $included = in_array($moduleName, $modules);
                                            if ($included) {
                                                $checkedModules[] = [
                                                    'name' => $moduleName,
                                                    'icon' => $moduleData['icon'],
                                                ];
                                            } else {
                                                $uncheckedModules[] = [
                                                    'name' => $moduleName,
                                                    'icon' => $moduleData['icon'],
                                                ];
                                            }
                                        }
                                        $sortedModules = array_merge($checkedModules, $uncheckedModules);
                                    @endphp
                                    @foreach ($sortedModules as $module)
                                        @php
                                            $iconClass = in_array($module['name'], $modules)
                                                ? 'bx bx-check-circle text-success'
                                                : 'bx bxs-x-circle text-danger';
                                        @endphp
                                        <li class="text-dark mb-2">
                                            <i class="{{ $iconClass }} me-2"></i>
                                            <i class="{{ $module['icon'] }}"></i>
                                            {{ ucfirst($module['name']) }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-md-0 mb-2">
                                    <h6 class="text-capitalize">
                                        {{ get_label('max_projects', 'Maximum Number of projects') }}:
                                        {!! $plan->max_projects == -1
        ? '<span class="text-primary fw-semibold">' . get_label('unlimited', 'Unlimited') . '</span>'
        : '<span class="text-primary fw-semibold">' . $plan->max_projects . '</span>' !!}
                                    </h6>
                                </div>
                                <div class="col-md-6 mb-md-0 mb-2">
                                    <h6 class="text-capitalize">
                                        {{ get_label('max_workspaces', 'Maximum Number of workspaces') }}:
                                        {!! $plan->max_worksapces == -1
        ? '<span class="text-primary fw-semibold">' . get_label('unlimited', 'Unlimited') . '</span>'
        : '<span class="text-primary fw-semibold">' . $plan->max_worksapces . '</span>' !!}
                                    </h6>
                                </div>
                                <div class="col-md-6 mb-md-0 mb-2">
                                    <h6 class="text-capitalize">
                                        {{ get_label('max_team_members', 'Maximum Number of team members') }}:
                                        {!! $plan->max_team_members == -1
        ? '<span class="text-primary fw-semibold">' . get_label('unlimited', 'Unlimited') . '</span>'
        : '<span class="text-primary fw-semibold">' . $plan->max_team_members . '</span>' !!}
                                    </h6>
                                </div>
                                <div class="col-md-6 mb-md-0 mb-2">
                                    <h6 class="text-capitalize">
                                        {{ get_label('max_clients', 'Maximum Number of clients') }}:
                                        {!! $plan->max_clients == -1
        ? '<span class="text-primary fw-semibold">' . get_label('unlimited', 'Unlimited') . '</span>'
        : '<span class="text-primary fw-semibold">' . $plan->max_clients . '</span>' !!}
                                    </h6>
                                </div>
                            </div>
                        </div>
                        <h5 class="mb-4 mt-2">{{ get_label('billing_details', 'Billing Details') }}</h5>
                        <div class="row">
                            <div class="col-md mb-md-0 mb-2">
                                <h5 class="text-dark"> {{ get_label('tenure', 'Tenure') }} :
                                    <span class="text-primary text-capitalize">{{ get_label($tenure, $tenure) }}</span>
                                </h5>
                            </div>
                            <div class="col-md mb-md-0 mb-2">
                                <h5 class="text-dark"> {{ get_label('price', 'Price') }}:
                                    <span class="text-primary text-capitalize">
                                        @php
                                            $selectedTenure = $tenure; // Default tenure (you can change this based on your implementation)
                                            $selectedPrice = '';
                                            $finalPrice = ''; // Initialize selected price variable
                                            switch ($selectedTenure) {
                                                case 'monthly':
                                                    $originalPrice = $plan->monthly_price;
                                                    $discountedPrice = $plan->monthly_discounted_price;
                                                    break;
                                                case 'yearly':
                                                    $originalPrice = $plan->yearly_price;
                                                    $discountedPrice = $plan->yearly_discounted_price;
                                                    break;
                                                case 'lifetime':
                                                    $originalPrice = $plan->lifetime_price;
                                                    $discountedPrice = $plan->lifetime_discounted_price;
                                                    break;
                                                default:
                                                    $originalPrice = $plan->monthly_price; // Default to monthly price
                                                    $discountedPrice = $plan->monthly_discounted_price;
                                            }
                                            // Check if discounted price is greater than 0
                                            if ($discountedPrice > 0) {
                                                echo $currency_symbol .
                                                    '<del>' .
                                                    $originalPrice .
                                                    '</del> ' .
                                                    $discountedPrice;
                                                $finalPrice = $discountedPrice;
                                            } else {
                                                echo $currency_symbol . $originalPrice;
                                                $finalPrice = $originalPrice;
                                            }
                                        @endphp
                                    </span>
                                </h5>
                            </div>
                        </div>
                        <!-- Payment Methods -->
                        @if ($finalPrice > 0)
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="text-capitalize mb-4">
                                        {{ get_label('payment_methods', 'Payment Methods') }}
                                    </h5>
                                    <div class="row g-3">
                                        @php
                                            $pay_pal_settings = get_settings('pay_pal_settings');
                                        @endphp
                                        @if(isset($pay_pal_settings['is_active']) && $pay_pal_settings['is_active'])
                                            <div class="col-md-4">
                                                <input type="radio" class="btn-check" value="pay_pal" name="options" id="pay_pal"
                                                    autocomplete="off">
                                                <label class="payment-option d-flex align-items-center rounded-3 w-100 border p-3"
                                                    for="pay_pal">
                                                    <div class="rounded-circle bg-label-primary me-3 p-2">
                                                        <i class="bx bxl-paypal fs-4 text-primary"></i>
                                                    </div>
                                                    <span class="fw-semibold">{{ get_label('paypal', 'Paypal') }}</span>
                                                </label>
                                            </div>
                                        @endif
                                        @php
                                            $phone_pe_settings = get_settings('phone_pe_settings');
                                        @endphp
                                        @if(isset($phone_pe_settings['is_active']) && $phone_pe_settings['is_active'])
                                            <div class="col-md-4">
                                                <input type="radio" class="btn-check" name="options" value="phonepe" id="phonepe"
                                                    autocomplete="off">
                                                <label class="payment-option d-flex align-items-center rounded-3 w-100 border p-3"
                                                    for="phonepe">
                                                    <div class="rounded-circle bg-label-primary me-3 p-2">
                                                        <i class="bx bx-rupee fs-4 text-primary"></i>
                                                    </div>
                                                    <span class="fw-semibold">{{ get_label('phonepe', 'PhonePe') }}</span>
                                                </label>
                                            </div>
                                        @endif
                                            @php
                                                $stripe_settings = get_settings('stripe_settings');
                                            @endphp
                                        @if(isset($stripe_settings['is_active']) && $stripe_settings['is_active'])
                                            <div class="col-md-4">
                                                <input type="radio" class="btn-check" name="options" value="stripe" id="stripe"
                                                    autocomplete="off">
                                                <label class="payment-option d-flex align-items-center rounded-3 w-100 border p-3"
                                                    for="stripe">
                                                    <div class="rounded-circle bg-label-primary me-3 p-2">
                                                        <i class="bx bxl-stripe fs-4 text-primary"></i>
                                                    </div>
                                                    <span class="fw-semibold">{{ get_label('stripe', 'Stripe') }}</span>
                                                </label>
                                            </div>
                                        @endif
                                            @php
                                                $paystack_settings =  get_settings('paystack_settings');
                                            @endphp
                                        @if(isset($paystack_settings['is_active']) && $paystack_settings['is_active'])
                                            <div class="col-md-4">
                                                <input type="radio" class="btn-check" name="options" value="paystack" id="paystack"
                                                    autocomplete="off">
                                                <label class="payment-option d-flex align-items-center rounded-3 w-100 border p-3"
                                                    for="paystack">
                                                    <div class="rounded-circle bg-label-primary me-3 p-2">
                                                        <i class="bx bx-coin-stack fs-4 text-primary"></i>
                                                    </div>
                                                    <span class="fw-semibold">{{ get_label('paystack', 'Paystack') }}</span>
                                                </label>
                                            </div>
                                        @endif

                                        @php
                                            $bank_transfer_settings = get_settings('bank_transfer_settings');
                                        @endphp
                                        @if(isset($bank_transfer_settings['is_active']) && $bank_transfer_settings['is_active'])
                                            <div class="col-md-4">
                                                <input type="radio" class="btn-check" name="options" value="bank_transfer"
                                                    id="bank_transfer" autocomplete="off">
                                                <label class="payment-option d-flex align-items-center rounded-3 w-100 border p-3"
                                                    for="bank_transfer">
                                                    <div class="rounded-circle bg-label-primary me-3 p-2">
                                                        <i class="bx bxs-bank fs-4 text-primary"></i>
                                                    </div>
                                                    <span
                                                        class="fw-semibold">{{ get_label('bank_transfer', 'Bank Transfer') }}</span>
                                                </label>
                                            </div>
                                        @endif
                                    </div>

                                    @php
                                        $activePaymentMethods = 0;
                                        if (isset($pay_pal_settings['is_active']) && $pay_pal_settings['is_active'])
                                            $activePaymentMethods++;
                                        if (isset($phone_pe_settings['is_active']) && $phone_pe_settings['is_active'])
                                            $activePaymentMethods++;
                                        if (isset($stripe_settings['is_active']) && $stripe_settings['is_active'])
                                            $activePaymentMethods++;
                                        if (isset($paystack_settings['is_active']) && $paystack_settings['is_active'])
                                            $activePaymentMethods++;
                                        if (isset($bank_transfer_settings['is_active']) && $bank_transfer_settings['is_active'])
                                            $activePaymentMethods++;
                                    @endphp

                                    @if($activePaymentMethods == 0)
                                        <div class="alert alert-primary mt-3">
                                            {{ get_label('no_payment_methods', 'No payment methods available.') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        <!-- Credit Card Info -->
                        <div id="form-credit-card">
                            <!-- Display credit card info form here -->
                        </div>
                    </div>
                    <div class="col-lg-6 card-body">
                        <h4 class="mb-2">{{ get_label('order_summary', 'Order Summary') }}</h4>
                        <p class="mb-0 pb-2">{!! get_label(
        'orderSummaryDecs',
        'It can help you manage and service orders before,<br> during and after fulfilment',
    ) !!}.</p>
                        <!-- Display Order Summary -->
                        <div id="orderSummaryDiv" class="text-capitalize mt-4 rounded p-4">
                            <input type="hidden" name="plan_name" value="{{ $plan->name }}" />
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}" />
                            <input type="hidden" name="tenure" value="{{ $tenure }}" />
                            <input type="hidden" name="currency_symbol" value="{{ $currency_symbol }}" />
                            <input type="hidden" name="total_price" value="{{ $finalPrice }}" />
                            <input type="hidden" name="user_id" value="{{ Auth::id() }}" />
                            <!-- Display order summary details here dynamically -->
                            <h5 class="text-primary" id="finalPlan"> </h5>
                            <h5 class="text-primary" id="finalPrice"> </h5>
                            <h5 class="text-primary" id="paymentMethod"></h5>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('subscription-plan.buy-plan') }}" id="changePlanBtn"
                                class="btn btn-outline-primary btn-sm d-none">{{ get_label('change_plan', 'Change Plan') }}</a>
                        </div>
                        <!-- Total and Proceed with Payment Button -->
                        <p class="mt-4 pt-2">
                            {{ get_label('order_accept', 'By continuing, you accept to our Terms of Services and Privacy Policy.Please note that payments are non-refundable') }}.
                        </p>
                        <div class="d-none mt-4" id="proceedPaymentBtn">
                            <button data-url="{{ route('subscription-plan.store') }}" id="paymentIntializeBtn" type="submit"
                                class="btn btn-outline-primary col-12">{{ get_label('proceed_with_payment', 'Proceed with Payment') }}</button>
                        </div>
                        <div class="mb-3 mt-3" id="paypal_div">
                            <div id="paypal-button-container"></div>
                        </div>
                        <!-- Modal for Stripe payment form -->
                        <div id="stripe_checkout">
                            <!-- Checkout will insert the payment form here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://www.paypal.com/sdk/js?client-id={{ $paypal_settings['paypal_client_id'] }}"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script src="https://mercury.phonepe.com/web/bundle/checkout.js"></script>
    <script src="{{ asset('assets/js/pages/subscription-plan.js') }}"></script>
@endsection
