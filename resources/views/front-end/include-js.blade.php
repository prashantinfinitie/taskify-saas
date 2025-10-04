<script src="{{ asset('assets/front-end/assets/js/core/popper.js') }}"></script>
<script src="{{ asset('assets/front-end/assets/js/core/bootstrap.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/front-end/assets/js/soft-design-system.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/front-end/assets/js/plugins/countup.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/front-end/assets/js/plugins/flatpickr.min.js') }}"></script>
<script src="{{ 'assets/front-end/assets/js/plugins/typedjs.js' }}"></script>
<script src="{{ asset('assets/front-end/assets/js/custom.js') }}"></script>
<script src="{{ asset('assets/js/tinymce.min.js') }}"></script>
<script src="{{ asset('assets/js/tinymce-jquery.min.js') }}"></script>

<!-- Date picker -->
<script src="{{ asset('assets/js/moment.min.js') }}"></script>

<script src="{{ asset('assets/js/daterangepicker.js') }}"></script>

<script src="{{ asset('assets/lightbox/lightbox.min.js') }}"></script>

<script src="{{ asset('assets/js/dropzone.min.js') }}"></script>
<script>
    var csrf_token = '{{ csrf_token() }}';
    var js_date_format = '{{ $js_date_format ?? 'YYYY-MM-DD' }}';
</script>


<script src="{{ asset('assets/front-end/assets/js/loopple/loopple.js') }}"></script>
<script src="{{ asset('assets/js/toastr.min.js') }}"></script>
<script src="{{ asset('assets/front-end/assets/js/plugins/lottie.js') }}"></script>

<script>
    var toastTimeOut = {{ isset($general_settings['toast_time_out']) ? $general_settings['toast_time_out'] : 5 }};
    var toastPosition = "{{ isset($general_settings['toast_position']) ? $general_settings['toast_position'] : 'toast-top-right' }}";
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

