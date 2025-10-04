@extends('front-end.layout')
@section('title')
    {{ get_label('contact_us', 'Contact Us') }}
@endsection
@section('content')
    <section class="section py-2 mt-6 " id="contact-us">
        <div class="container mt-3">

            <h2 class="text-center display-5 fw-semi-bold">{{ get_label('contact_us', 'Contact Us') }}</h2>
            <p class="text-center fs-0 fs-md-1">
                {{ get_label('contact_us_subheading', 'Have questions or need support? Reach out to us!') }}</p>
            <div class="row justify-content-center mt-5">
                <div class= "col-md-4 col-lg-6">
                    <div class = "img-fluid">
                        <img class = "w-100 h-100" src ="assets/front-end/img/gallery/contact-us.png">
                    </div>
                </div>
                <div class="col-md-8 col-lg-6"> <!-- Adjust column size for different screen sizes -->
                    <form action="{{ route('frontend.send_mail') }}" id="contact_us_form" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ get_label('your_name', 'Your Name') }}<span
                                 class="asterisk">*</span></label>
                         
                            <input name="name" type="text" class="form-control" id="name"
                                placeholder="{{ get_label('enter_your_name', 'Enter your name') }}">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">{{ get_label('your_email', 'Your Email') }}<span
                                 class="asterisk">*</span></label>
                            <input type="email" name="email" class="form-control" id="email"
                                placeholder="{{ get_label('enter_your_email', 'Enter your email') }}">
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">{{ get_label('your_message', 'Your Message') }}<span
                                 class="asterisk">*</span></label>
                            <textarea class="form-control" name="message" id="message" rows="4"
                                placeholder="{{ get_label('enter_your_message', 'Enter your message') }}"></textarea>
                        </div>
                        <button type="button" id="contactUsSubmit"
                            class="btn btn-primary">{{ get_label('submit', 'Submit') }}</button>
                        <div id="loading-overlay">
                            {{-- <div id="loading-indicator" class="text-center">
                                <img class="img-fluid" height="40px" src="assets/front-end/img/gallery/Opener Loading.gif"
                                    alt="Loading..."> --}}
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
