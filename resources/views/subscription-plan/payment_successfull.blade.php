@extends('layout')

@section('title')
    <?= get_label('subscription_plan', 'Subscription Plan') ?>
@endsection

@section('content')
    @php
        $superadmin = getSuperAdmin();
    @endphp
    <div class="container-fluid">
        <div class="d-flex justify-content-between mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('subscription_plan', 'Subscription Plan') ?>
                        </li>

                    </ol>
                </nav>
            </div>
        </div>

        @if ($data['status'] === 'PAYMENT_SUCCESS')
            <div class="container-fluid d-flex justify-content-center">
                <div class ="row">
                    <div class = "col-md-12">
                        <div class="card">
                            <div class="misc-wrapper text-center card-body">
                                <h2 class="mb-2 mx-2">{{ get_label('payment_successfull', 'Payment Successfull !!!') }}</h2>
                                <p class="mb-4 mx-2">
                                    {{ get_label('subscription_added', 'Your subscription request has been received successfully. We\'re working on activating your account right now, and this process typically takes up to 30 minutes to complete.') }}
                                </p>
                                <p class="mb-4 mx-2">
                                    {{ get_label('subscription_support', 'If you haven\'t received your subscription after 30 minutes, please don\'t hesitate to contact our friendly support team at ') }}
                                    <a href="tel:{{ $superadmin->phone }}">{{ $superadmin->phone }}</a>
                                    {{ get_label('subscription_support_email', ' or through our ') }}
                                    <a href="mailto:{{ $superadmin->email }}" target="_blank">Support Email</a>.
                                    {{ get_label('subscription_support_closing', ' We\'re here to ensure a smooth onboarding experience for you.') }}
                                </p>
                                <p class="mb-4 mx-2">
                                    {{ get_label('subscription_closing', 'We\'re thrilled to have you as a new subscriber and look forward to providing you with an exceptional [product/service name] experience. Thank you for choosing us!') }}
                                </p>

                                <a href="{{ route('subscription-plan.index') }}"
                                    class="btn btn-primary">{{ get_label('subcription_plan', 'Subscription Plan') }}</a>
                            </div>


                            <div class="mt-3 text-center">
                                <img src="/assets/img/illustrations/Mobile Payment.gif" alt="page-misc-error-light"
                                    width="500" class="img-fluid">
                            </div>
                            <div>
                            </div>

                        </div>
                    </div>
        @endif
        @if ($data['status'] === 'PAYMENT_ERROR')
            <div class="container-p-y container-xxl d-flex justify-content-center">
                <div class="misc-wrapper text-center">
                    <h2 class="mb-2 mx-2">{{ get_label('payment_failed', 'Payment Failed !!!') }}</h2>
                    <p class="mb-4 mx-2">
                        {{ get_label('subscription_failed', 'Your Subcription Is Not Successfully Added , Some Error Occured ') }}
                    </p>
                    <div>
                        <a href="{{ route('subscription-plan.index') }}"
                            class="btn btn-primary">{{ get_label('subcription_plan', 'Subscription Plan') }}</a>
                    </div>
                    <div class="mt-3">
                        <img src="/assets/img/illustrations/page-misc-error-light.png" alt="page-misc-error-light"
                            width="500" class="img-fluid">
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
