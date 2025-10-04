<footer class="footer py-2 footer-bg position-relative overflow-hidden fixed-bottom">
    
    <div class="container position-relative mt-5">
        <div class="row ">
            <div class="col-md-6 mb-5 mb-lg-0">
                <div class="img-box">
                    <img src="{{ asset($general_settings['footer_logo']) }}">
                </div>
                <p class="mb-1 pb-1">
                    <ul class="list-unstyled mb-md-4">
                        <li class="lh-lg">{!! $general_settings['company_address'] !!}</li>

                        <li class="lh-lg">

                            <a href="mailto:{!! $general_settings['support_email'] !!}" >
                                <i class="far fa-envelope"></i>
                                {{ $general_settings['support_email'] }}
                            </a>
                            </li>
                            </ul>
                </p>

            </div>

            <div class="col-md-6 ms-lg-0 mb-md-0 mb-4">
                <div class="d-flex justify-content-center row">

                    <div class="col-4">
                        <ul class="list-unstyled mb-md-2 mx-3">
                            <li class="lh-lg"><a href="{{ route('frontend.index') }}" class="d-inline-flex align-items-center ">{{ get_label('home', 'Home') }}</a></li>
                            <li class="lh-lg"><a href="{{url('/#contact-us-section')}}" class="d-inline-flex align-items-center">{{ get_label('contact_us', 'Contact Us') }}</a>


                            </li>
                            <li class="lh-lg"><a href="{{url('/#about-section')}}" class="d-inline-flex align-items-center">{{ get_label('about_us', 'About Us') }}</a>


                            </li>
                            <li class="lh-lg"><a href="{{ route('frontend.refund_policy') }}" class="d-inline-flex align-items-center">{{ get_label('refund_policy', 'Refund Policy') }}</a>

                            </li>
                        </ul>
                    </div>
                    <div class="col-5">

                        <ul class="list-unstyled mb-md-2 mx-3">
                            <li class="lh-lg"><a href="{{url('/#features-section')}}" class="d-inline-flex align-items-center">{{ get_label('features', 'Features') }}</a>


                            </li>
                            <li class="lh-lg"><a href="{{url('/#pricing-plans-section')}}" class="d-inline-flex align-items-center">{{ get_label('pricing_plans', 'Pricing') }}</a>


                            </li>
                            <li class="lh-lg"><a href="{{ route('frontend.privacy_policy') }}" class="d-inline-flex align-items-center">{{ get_label('privacy_policy', 'Privacy Policy') }}</a>


                            </li>
                            <li class="lh-lg"><a href="{{ route('frontend.terms_and_condition') }}" class="d-inline-flex align-items-center">{{ get_label('terms_and_conditions', 'Terms & Conditions') }}</a>


                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
        <hr class="custom-hr my-4">

        <div class="row d-flex justify-content-center row text-capitalize text-center ">
            <div class="col-md-auto">
                <p class="">&copy; {{ date('Y') }} {{ $general_settings['company_title'] }}</p>
            </div>
            <div class="col-md-auto ">

                {!! $general_settings['footer_text'] !!}

            </div>
        </div>

    </div>
</footer>
