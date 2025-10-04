@extends('front-end.layout')
@section('title')
    <?= get_label('privacy_policy', 'Privacy Policy') ?>
@endsection
@section('content')

    <section class="section-py mt-6 py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <h1 class="fs-4 fs-md-5 fs-xxl-4">
                        {{ get_label('privacy_policy', 'Privacy Policy') }}</h1>
                    <p class = "font-size-18 text-black">
                        {!! $privacy_policy['privacy_policy'] !!}
                    </p>
                </div>
            </div>
        </div>
    </section>
    {{-- <footer class="footer">
        <div class="container text-center">
            <div class="row justify-content-center"> <!-- Added row and justify-content-center -->
                <div class="col-md-12 col-lg-8">
                    &copy; {{ date('Y') }} ,{!! str_replace(['<p>', '</p>'], '', $general_settings['footer_text']) !!}
                </div>
            </div>
            <div class="row justify-content-center mt-3"> <!-- Added row and justify-content-center for the links -->
                <div class="col-md-12 col-lg-8"> <!-- Adjusted column width for larger screens -->
                    <a href="{{ route('frontend.privacy_policy') }}"
                        class="text-decoration-none">{{ get_label('privacy_policy', 'Privacy Policy') }}</a>
                    <span class="mx-2">|</span>
                    <a href="{{ route('frontend.refund_policy') }}"
                        class="text-decoration-none">{{ get_label('refund_policy', 'Refund Policy') }}</a>
                    <span class="mx-2">|</span>
                    <a href="{{ route('frontend.terms_and_condition') }}"
                        class="text-decoration-none">{{ get_label('terms_and_conditions', 'Terms and Conditions') }}</a>
                </div>
            </div>
        </div>
    </footer> --}}
@endsection
