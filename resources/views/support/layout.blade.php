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
    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}"
        class="template-customizer-theme-css" />
            <link rel="stylesheet" href="{{ asset('assets/lightbox/lightbox.min.css') }}" />
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
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>

<body>

        <div class="container">
            @yield('content')
            @include('modals')
            @include('labels')
             <x-footer />
        </div>

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
    <script src="{{ asset('assets/lightbox/lightbox.min.js') }}"></script>
    <!-- Toastr -->
    <script src="{{ asset('assets/js/toastr.min.js') }}"></script>
    @authBoth
    <script>
        var authUserId = '<?= getAuthenticatedUser()->id ?>';
    </script>
@endauth
<script>
    var csrf_token = '{{ csrf_token() }}';
    var js_date_format = '{{ $js_date_format ?? 'YYYY - MM - DD' }}';
</script>
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
