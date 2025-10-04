<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('assets/') }}" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>@yield('title') - {{ $general_settings['company_title'] ?? 'Taskify - Saas' }}</title>
    <meta name="description" content="" />
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon"
        href="{{ asset($general_settings['favicon'] ?? 'storage/logos/default_favicon.png') }}" />
    <!-- Fonts -->
    <link rel="stylesheet" href="{{ asset('assets/css/google-fonts.css') }}" />
    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <!-- Drawing Tool For Notes  -->
    <link rel="stylesheet" href="{{ asset('assets/css/editor.min.css') }}" />
    <script src="{{ asset('assets/js/bundle.min.js') }}"></script>

    <!-- Core CSS -->
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"> --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    @if (Request::is('master-panel/projects/kanban-view'))
        <link
            href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Open+Sans:wght@400;700&family=Montserrat:wght@400;600;700&family=Source+Sans+Pro:wght@400;600;700&family=Nunito:wght@400;700&family=Raleway:wght@400;600;700&family=Ubuntu:wght@400;700&family=Work+Sans:wght@400;700&display=swap"
            rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('assets/css/project-kanban.css') }}" />
    @endif
    @if (Request::is('master-panel/projects/mind-map/*'))
        <link rel="stylesheet" href="{{ asset('assets/css/mind-map.css') }}" />
    @endif
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/int_tel_input.css') }}" /> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/lib/libphonenumber/build/utils.js"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}" />
    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/apex-charts.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/lightbox/lightbox.min.css') }}" />
    <!-- Page CSS -->
    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <!-- Date picker -->
    <link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}" />
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/bootstrap-table.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/dragula.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/toastr.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/dropzone.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/fullcalendar/core/main.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/fullcalendar/daygrid/main.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/fullcalendar/list/main.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/frappe-gannt.css') }}" rel="stylesheet" />

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <!-- PWA  -->
    <script>
          var baseUrl = "{{ url('/') }}";
    </script>
        <link rel="manifest" href="{{ route('manifest') }}">

        @php
            $theme_color = '#000000'; // default color
            if (isset($pwa_settings) && isset($pwa_settings['pwa_theme_color'])) {
                $theme_color = $pwa_settings['pwa_theme_color'];
            }

            $full_logo = 'storage/logos/default_favicon.png'; // default logo
            if (isset($general_settings) && isset($general_settings['full_logo'])) {
                $full_logo = $general_settings['full_logo'];
            }
        @endphp
        <meta name="theme-color" content="{{ $theme_color }}">
        <link rel="apple-touch-icon" href="{{ asset($full_logo) }}">
</head>

<body>

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
    <!-- Layout wrapper -->
    @if (Request::is('forgot-password') ||
            Request::is('login') ||
            Request::segment(1) == 'reset-password' ||
            Request::is('install') ||
            Request::is('privacy-policy') ||
            Request::is('terms-and-condition') ||
            Request::is('refund-policy') ||
            Request::is('register'))
        @yield('content')
        @include('labels')
    @else
        <div class="layout-wrapper layout-content-navbar">
            <div class="layout-container">
                <!-- Menu -->
                @authBoth
                <x-menu />
                <!-- Layout container -->
                <div class="layout-page">
                    @include('partials._navbar')
                    <!-- Content wrapper -->
                    <div class="content-wrapper">
                        @yield('content')
                        @include('modals')
                        @include('labels')
                    </div>
                    <!-- Content wrapper -->
                    <!-- footer -->
                    <x-footer />
                    <!-- / footer -->
                @else
                    <!-- Layout page -->

                    <div class="container-fluid container-p-y">
                        <div class="misc-wrapper d-flex flex-column align-items-center justif-content-center">
                            <h2 class="mx-2 mb-2">
                                {{ get_label('session_expired', 'Session expired') }}!!!
                            </h2>
                            <div class="my-5">
                                <img src="/assets/img/illustrations/page-misc-error-light.png"
                                    alt="page-misc-error-light" width="500" class="img-fluid"
                                    data-app-dark-img="illustrations/page-misc-error-dark.png"
                                    data-app-light-img="illustrations/page-misc-error-light.png" />
                            </div>
                            <a href="{{ url('/') }}" class="btn btn-primary">
                                {{ get_label('log_in', 'Log in') }}
                            </a>
                        </div>
                    </div>
                @endauth
            </div>
            <!-- / Layout page -->
        </div>
        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    @php
        $manage_notifications =
            auth()->check() && auth()->user()->can('manage_system_notifications') ? 'true' : 'false';
    @endphp
    <script>
        var manage_notifications = '{{ $manage_notifications }}';
        var manage_announcements = 'true';
    </script>
@endif
<script src="{{ asset('assets/js/time-tracker.js') }}"></script>
@if (getAuthenticatedUser() &&
        !getAuthenticatedUser()->hasRole('superadmin') &&
        !getAuthenticatedUser()->hasRole('manager'))
    @if (Auth::guard('web')->check())
        <div onclick="toggleChatIframe()">
            <span>
                <a href="javascript:void(0);">
                    <img src="{{ asset('storage/chat-icon.png') }}" class="chat-img" alt="Chat">
                </a>
            </span>
        </div>
        <div id="chatIframeContainer" class="chat-iframe-container">
            <iframe src="/{{ 'chat' }}" frameborder="0" class="chat-iframe"></iframe>
        </div>
    @endif
@endif
<!-- / Layout wrapper -->
@if (getAuthenticatedUser() && getAuthenticatedUser()->can('create_timesheet'))
    <!-- Timer image -->
    <div onclick="open_timer_section()">
        <span>
            <a href="javascript:void(0);" title="<?= get_label('time_tracker', 'Time tracker') ?>">
                <img src="{{ asset('storage/94150-clock.png') }}" class="timer-img" id="timer-image"
                    alt="Timer" data-bs-toggle="modal" data-bs-target="#timerModal">
            </a>
        </span>
    </div>
@endif
<!-- Core JS -->
<!-- build:js assets/vendor/js/core.js -->
<script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
<!-- endbuild -->
<!-- Main JS -->
<script src="{{ asset('assets/js/main.js') }}"></script>
<script src="{{ asset('assets/js/ui-toasts.js') }}"></script>
<!-- Place this tag in your head or just before your close body tag. -->
<script async defer src="{{ asset('assets/js/buttons.js') }}"></script>
<!-- select 2 js !-->
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<!-- Bootstrap-table -->
<script src="{{ asset('assets/js/bootstrap-table/bootstrap-table.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap-table/bootstrap-table-export.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap-table/tableExport.min.js') }}"></script>
<!-- Dragula -->
<script src="{{ asset('assets/js/dragula.min.js') }}"></script>
<script src="{{ asset('assets/js/popper.js') }}"></script>
<!-- Toastr -->
<script src="{{ asset('assets/js/toastr.min.js') }}"></script>
<script src="{{ asset('assets/js/tinymce.min.js') }}"></script>
<script src="{{ asset('assets/js/tinymce-jquery.min.js') }}"></script>
<!-- Date picker -->
<script src="{{ asset('assets/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/js/daterangepicker.js') }}"></script>
<script src="{{ asset('assets/lightbox/lightbox.min.js') }}"></script>
<script src="{{ asset('assets/js/dropzone.min.js') }}"></script>
<script src="{{ asset('assets/js/extended-ui-perfect-scrollbar.js') }}"></script>
<script src="{{ asset('assets/js/fullcalendar/core/main.js') }}"></script>
<script src="{{ asset('assets/js/fullcalendar/interaction/main.js') }}"></script>
<script src="{{ asset('assets/js/fullcalendar/daygrid/main.js') }}"></script>
<script src="{{ asset('assets/js/fullcalendar/list/main.js') }}"></script>
<script src="{{ asset('assets/js/fullcalendar/google-calendar/main.js') }}"></script>
@if (Request::is('master-panel/projects/mind-map/*'))
    <script src="{{ asset('assets/js/mind-map.js') }}"></script>
    <script src="{{ asset('assets/js/dom-to-image.js') }}"></script>
    <!-- screenshot plugin -->
    <script src="{{ asset('assets/js/mindmap-screenshot.js') }}"></script>
@endif
@if (Request::is('master-panel/preferences'))
    <script src="{{ asset('assets/js/Sortable.min.js') }}"></script>
@endif
@authBoth
<script>
    var authUserId = '<?= getAuthenticatedUser()->id ?>';
</script>
@endauth
<!-- Custom js -->
<script>
    var csrf_token = '{{ csrf_token() }}';
    var js_date_format = '{{ $js_date_format ?? 'YYYY - MM - DD' }}';
</script>
<script src="{{ asset('assets/js/frappe-gantt.js') }}"></script>
{{-- <script src="{{ asset('assets/js/int_tel_input.js') }}"></script> --}}
<script src="{{ asset('assets/js/tribute.js') }}"></script>
<script>
    var toastTimeOut = {{ isset($general_settings['toast_time_out']) ? $general_settings['toast_time_out'] : 5 }};
    var toastPosition =
        "{{ isset($general_settings['toast_position']) ? $general_settings['toast_position'] : 'toast-top-right' }}";
</script>

<script src="{{ asset('assets/js/custom.js') }}"></script>
@if (session()->has('message'))
<script>
    toastr.options = {
        "positionClass": toastPosition,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": parseFloat(toastTimeOut) * 1000,
        "progressBar": true,
        "extendedTimeOut": "1000",
        "closeButton": true
    };
    toastr.success('{{ session('message') }}', 'Success');
</script>
@elseif(session()->has('error'))
<script>
    toastr.options = {
        "positionClass": toastPosition,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": parseFloat(toastTimeOut) * 1000,
        "progressBar": true,
        "extendedTimeOut": "1000",
        "closeButton": true
    };
    toastr.error('{{ session('error') }}', 'Error');
</script>
@endif
</body>

</html>
