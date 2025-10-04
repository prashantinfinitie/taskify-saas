<nav class="navbar navbar-expand-lg navbar-light bg-primary fixed-top py-3">
    <div class="container">
        <div class="img-box">
            <a class="navbar-brand" href="/"><img src="{{ asset($general_settings['full_logo']) }}"
                    class="img-box"></a>
        </div>
        <button class="navbar-toggler ms-2 shadow-none" type="button" data-bs-toggle="collapse"
            data-bs-target="#navigation" aria-controls="navigation" aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon mt-2">
                <span class="navbar-toggler-bar bar1"></span>
                <span class="navbar-toggler-bar bar2"></span>
                <span class="navbar-toggler-bar bar3"></span>
            </span>
        </button>

        <div class="navbar-collapse w-100 py-lg-0 ms-lg-4 ps-lg-5 collapse pb-2 pt-3" id="navigation">
            <ul class="justify-content-end navbar-nav navbar-nav-hover w-100">
                <li class="nav-item mx-2">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ Request::is('/') ? 'active text-primary fw-bold ' : '' }} cursor-pointer ps-2"
                        href="{{ route('frontend.index') }}">{{ get_label('home', 'Home') }}</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ Request::is('about-us') ? 'active text-primary fw-bold ' : '' }} cursor-pointer ps-2"
                        href="{{ route('frontend.about_us') }}">{{ get_label('about_us', 'About Us') }}</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ Request::is('pricing') ? 'active text-primary fw-bold ' : '' }} cursor-pointer ps-2"
                        href="{{ route('frontend.pricing') }}">{{ get_label('pricing_plans', 'Pricing Plans') }}</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ Request::is('features') ? 'active text-primary fw-bold ' : '' }} cursor-pointer ps-2"
                        href="{{ route('frontend.features') }}">{{ get_label('features', 'Features') }}</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ Request::is('contact-us') ? 'active text-primary fw-bold ' : '' }} cursor-pointer ps-2"
                        href="{{ route('frontend.contact_us') }}">{{ get_label('contact_us', 'Contact Us') }}</a>
                </li>

                <li class="nav-item dropdown dropdown-hover mx-2">
                    <a class="nav-link justify-content-between align-items-center cursor-pointer ps-2"
                        id="dropdownMenuPages1" data-bs-toggle="dropdown" aria-expanded="false" href="#">
                        <i class="fas fa-language" aria-hidden="true"></i>
                        <img src="https://demos.creative-tim.com/soft-ui-design-system/assets/img/down-arrow-dark.svg"
                            alt="down-arrow" class="arrow ms-1">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-animation mt-lg-3 border-radius-lg w-50 mt-0 p-3"
                        aria-labelledby="dropdownMenuDocs">
                        @foreach ($languages as $language)
                            <li
                                class="dropdown-item  rounded">
                                <a class="d-block w-100 text-decoration-none {{ $language->code == app()->getLocale() ? ' text-primary fw-bold' : 'text-dark' }}"
                                    href="{{ route('languages.switch', ['code' => $language->code]) }}">
                                    {{ $language->name }}
                                </a>
                            </li>
                        @endforeach
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                    </ul>
                </li>



            </ul>

            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 col-xl-2">
                @if (auth()->check())
                    @if (auth()->user()->hasRole('superadmin'))
                        <a href="{{ route('superadmin.panel') }}"
                            class="btn btn-sm btn-round mt-md-0 bg-gradient-primary mb-0 me-1 mt-2">{{ get_label('dashboard', 'Dashboard') }}</a>
                    @else
                        <a href="{{ route('home.index') }}"
                            class="btn btn-sm btn-round mt-md-0 bg-gradient-primary mb-0 me-1 mt-2">{{ get_label('dashboard', 'Dashboard') }}</a>
                    @endif
                @else
                    <a href="{{ route('login') }}"
                        class="btn btn-sm btn-round mt-md-0 bg-gradient-primary mb-0 me-1 mt-2">{{ get_label('login', 'Login') }}</a>
                @endif
            </div>


        </div>
    </div>
</nav>
