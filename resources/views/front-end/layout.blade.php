<!DOCTYPE html>

<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('assets/') }}" data-template="vertical-menu-template-free">

<head>
    <!-- PWA  -->

    <link rel="manifest" href="{{ route('manifest') }}">

    <meta name="theme-color" content="{{ $pwa_settings['pwa_theme_color'] }}">

    <link rel="apple-touch-icon" href="{{ asset($general_settings['full_logo']) }}">

    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>@yield('title') - {{ $general_settings['company_title'] ?? 'Taskify - Saas' }}</title>


    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon"
        href="{{ asset($general_settings['favicon'] ?? 'storage/logos/default_favicon.png') }}" />
    @include('front-end.include-css')
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/sw.js')
                    .then(function (registration) {
                        console.log('Service Worker registered with scope:', registration.scope);
                    }, function (err) {
                        console.log('Service Worker registration failed:', err);
                    });
            });
        }
    </script>
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script>
        window.themeSwitchRoute = "{{ route('theme.switch') }}";
    </script>

</head>

<body class="">
    <div class="custom-hero-header-bg-img">
        @if(config('constants.ALLOW_MODIFICATION') == '0')
            <!-- Floating Buy Plan Button -->
            <div class="floating-buy-plan">
                <div class="attention-ring"></div>
                <a href="https://codecanyon.net/item/taskify-saas-project-management-system-in-laravel/52126963"
                    class="buy-button" target="_blank" title="Get Your Copy Now!">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart"
                        viewBox="0 0 16 16">
                        <path
                            d="M0 1.5A.5.5 0 0 1 .5 1h1a.5.5 0 0 1 .485.379L2.89 5H14.5a.5.5 0 0 1 .49.598l-1.5 7A.5.5 0 0 1 13 13H4a.5.5 0 0 1-.49-.402L1.61 2H.5a.5.5 0 0 1-.5-.5zM5 12a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm7 1a1 1 0 1 1-2 0 1 1 0 0 1 2 0z" />
                    </svg>
                    <span>Buy Now</span>
                </a>
            </div>
        @endif

        <?php if (config('constants.ALLOW_MODIFICATION') === 0): ?>
        <button class="template-customizer-open-btn" id="templateCustomizerOpenBtn">
            <i class="fas fa-cog"></i>
        </button>
        <?php endif; ?>

        <?php if (config('constants.ALLOW_MODIFICATION') === 0): ?>
        <div class="template-customizer" id="templateCustomizer">

            <div class="template-customizer-header">
                <h4>
                    <i class="fas fa-palette"></i>
                    Theme Customizer
                </h4>

                <button class="template-customizer-close-btn" id="templateCustomizerCloseBtn">
                    <i class="fas fa-times"></i>
                </button>

            </div>

            <div class="template-customizer-inner">
                <div class="customizer-section">
                    <h6>Theme Preferences</h6>
                    <div class="theme-options">
                        <div class="theme-option theme-modern switch-theme-btn" data-theme="new">
                            <div class="theme-icon">
                                <i class="fas fa-rocket"></i>
                            </div>
                            <div class="theme-info">
                                <div class="theme-name">Modern Theme</div>
                                <div class="theme-description">Clean, contemporary design with modern elements</div>
                            </div>
                        </div>

                        <div class="theme-option theme-classic switch-theme-btn" data-theme="old">
                            <div class="theme-icon">
                                <i class="fas fa-gem"></i>
                            </div>
                            <div class="theme-info">
                                <div class="theme-name">Classic Theme</div>
                                <div class="theme-description">Traditional, timeless design with elegant styling</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <div class="customizer-overlay" id="customizerOverlay"></div>



        @if(($active_theme ?? 'new') === 'old')
            @include('front-end.old.navbar')
        @else
            @include('front-end.navbar')
        @endif
    </div>
    @include('labels')
    <header>

    </header>
    <main class="mt-4 ">

        @yield('content')

    </main>
    <footer>
        @if(($active_theme ?? 'new') === 'old')
            @include('front-end.old.footer')
        @else
            @include('front-end.footer')
        @endif
    </footer>


    @include('front-end.include-js')

</body>


</html>