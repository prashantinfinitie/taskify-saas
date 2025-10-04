<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>@yield('title')  {{ $general_settings['company_title'] ?? 'Taskify - Saas' }}</title>


    <link rel="icon" type="image/x-icon"
        href="{{ asset($general_settings['favicon'] ?? 'storage/logos/default_favicon.png') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/google-fonts.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}" />
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/toastr.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@24.3.4/build/css/intlTelInput.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@24.3.4/build/js/intlTelInput.min.js"></script>

    <style>
        body {
            background: transparent;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
        }

        .form-section {
            background: #ffffff;
            padding: 1.5rem;
            border-radius: 8px;
            /* box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); */
            /* max-width: 500px; */
            width: 100%;
            /* border: 1px solid #e5e7eb; */
            /* margin:2rem */
        }

        .form-section h5 {
            color: #111827;
            font-weight: 600;
            font-size: 1.25rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .form-label {
            color: #374151;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 0.625rem;
            font-size: 0.875rem;
            background: #fff;
            transition: border-color 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: none;
            outline: none;
        }

        .form-check-input {
            border: 1px solid #d1d5db;
            width: 1rem;
            height: 1rem;
            margin-top: 0.25rem;
        }

        .form-check-input:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        .form-check-label {
            color: #374151;
            font-size: 0.875rem;
            margin-left: 0.25rem;
        }

        .btn-primary {
            background: #3b82f6;
            border: none;
            border-radius: 6px;
            padding: 0.625rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .text-danger {
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        @media (max-width: 576px) {
            .form-section {
                padding: 1rem;
            }

            #leadForm {
                margin: 0;
                max-width: none;
            }
        }

       /* Default: full width for iframe and everything */
#leadForm {
    width: 100%;
}

/* When NOT in iframe (standalone view), center with max-width */
body.standalone-form #leadForm {
    max-width: 600px;
    margin: auto;
}

body.standalone-form .form-section {
    padding: 1.5rem;
    border-radius: 8px;
    /* box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); */
    /* border: 1px solid #e5e7eb; */
}

@media (max-width: 768px) {
    #leadForm {
        max-width: 100%;
        margin: 0;
    }

    .form-section {
        border-radius: 0;
        box-shadow: none;
        border: none;
    }
}

    </style>

    <script>
        var baseUrl = "{{ url('/') }}";
        var currencySymbol = "{{ $general_settings['currency_symbol'] ?? '' }}";
    </script>

    
</head>

<body>
    <div class="form-section mx-auto">


        <div class="mb-3 text-center">
            <h5>{{ $form->title }}</h5>
        </div>

        <form id="leadForm" action="{{ route('public.form.submit', $form->slug) }}" method="POST">
            @csrf
            @foreach ($form->leadFormFields as $field)
                <div class="mb-3">
                    <label for="{{ $field->name ?: 'field_' . $field->id }}" class="form-label">
                        {{ $field->label }} {!! $field->is_required ? '<span class="text-danger">*</span>' : '' !!}
                    </label>

                    @if ($field->type == 'textarea')
                        <textarea class="form-control" id="{{ $field->name ?: 'field_' . $field->id }}"
                            name="{{ $field->name ?: 'field_' . $field->id }}" placeholder="{{ $field->placeholder ?? '' }}"
                            {{ $field->is_required ? 'required' : '' }}></textarea>
                    @elseif($field->type == 'select')
                        <select class="form-select" id="{{ $field->name ?: 'field_' . $field->id }}"
                            name="{{ $field->name ?: 'field_' . $field->id }}"
                            {{ $field->is_required ? 'required' : '' }}>
                            <option value="">{{ $field->placeholder ?? 'Select an option' }}</option>
                            @foreach (json_decode($field->options, true) ?? [] as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    @elseif($field->type == 'checkbox')
                        @if (json_decode($field->options, true))
                            @foreach (json_decode($field->options, true) as $option)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox"
                                        id="{{ $field->name ?: 'field_' . $field->id }}_{{ $loop->index }}"
                                        name="{{ $field->name ?: 'field_' . $field->id }}[]"
                                        value="{{ $option }}"
                                        {{ $field->is_required ? 'data-required="true"' : '' }}>
                                    <label class="form-check-label"
                                        for="{{ $field->name ?: 'field_' . $field->id }}_{{ $loop->index }}">{{ $option }}</label>
                                </div>
                            @endforeach
                        @else
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox"
                                    id="{{ $field->name ?: 'field_' . $field->id }}"
                                    name="{{ $field->name ?: 'field_' . $field->id }}" value="1"
                                    {{ $field->is_required ? 'required' : '' }}>
                                <label class="form-check-label"
                                    for="{{ $field->name ?: 'field_' . $field->id }}">{{ $field->placeholder ?? $field->label }}</label>
                            </div>
                        @endif
                    @elseif($field->type == 'radio')
                        @foreach (json_decode($field->options, true) ?? [] as $option)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio"
                                    id="{{ $field->name ?: 'field_' . $field->id }}_{{ $loop->index }}"
                                    name="{{ $field->name ?: 'field_' . $field->id }}" value="{{ $option }}"
                                    {{ $field->is_required ? 'data-required="true"' : '' }}>
                                <label class="form-check-label"
                                    for="{{ $field->name ?: 'field_' . $field->id }}_{{ $loop->index }}">{{ $option }}</label>
                            </div>
                        @endforeach
                    @else
                        <input type="{{ $field->type }}" class="form-control"
                            id="{{ $field->name ?: 'field_' . $field->id }}"
                            name="{{ $field->name ?: 'field_' . $field->id }}"
                            placeholder="{{ $field->placeholder ?? '' }}" {{ $field->is_required ? 'required' : '' }}>
                    @endif

                    <div class="text-danger small error-message mt-1"
                        id="error_{{ $field->name ?: 'field_' . $field->id }}"></div>
                </div>
            @endforeach

            <button type="submit" class="btn btn-primary w-100">Submit</button>
        </form>


    </div>

    <div class="toast-container position-fixed end-0 top-0 p-3">
        <div id="formToast" class="toast align-items-center bg-primary text-white" role="alert" aria-live="assertive"
            aria-atomic="true" data-bs-autohide="true" data-bs-delay="3000">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage"></div>
                <button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios@1.4.0/dist/axios.min.js"></script>
    <script src="{{ asset('assets/js/pages/public-form.js') }}"></script>



</body>

</html>
