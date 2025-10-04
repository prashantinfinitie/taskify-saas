<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap"
    rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Asap:ital,wght@0,100..900;1,100..900&display=swap"
    rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />

<script src="{{ asset('assets/front-end/assets/js/loopple/kit.fontawesome.js') }}" crossorigin="anonymous"></script>

<link href="{{ asset('assets/front-end/assets/css/nucleo-icons.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/front-end/assets/css/nucleo-svg.css') }}" rel="stylesheet" />
{{--
<link rel="stylesheet" href="{{ asset('assets/front-end/assets/css/theme.css') }}"> --}}
<link rel="stylesheet" href="{{ asset('assets/front-end/assets/css/loopple/loopple.css') }}">
<link rel="stylesheet" href="{{ asset('assets/front-end/assets/css/custom.css') }}">

@if($active_theme === 'old' || (isset($theme) && $theme === 'old'))
    <!-- Old Theme Styles -->
    <link rel="stylesheet" href="{{ asset('assets/front-end/assets/old/css/theme.css') }}">
@else
    <!-- New Theme Styles (Current) -->
    <link rel="stylesheet" href="{{ asset('assets/front-end/assets/css/theme.css') }}">
@endif
<link href="{{ asset('assets/css/toastr.min.css') }}" rel="stylesheet" />