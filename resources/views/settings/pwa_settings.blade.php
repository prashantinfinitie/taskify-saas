@extends('layout')
@section('title')
    {{ get_label('pwa_settings', 'PWA Settings') }}
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item">
                            <?= get_label('settings', 'Settings') ?>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('pwa_settings', 'PWA Settings') ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card">
            <div class="card-header">PWA Settings</div>
            <div class="card-body">
                <form class="form-submit-event" action="{{ route('pwa_settings.store') }}" method="POST"
                    enctype="multipart/form-data">
                    <input type="hidden" name="redirect_url" value="">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="name">NAME *</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ $pwa_settings['pwa_name'] ?? 'Taskify' }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="short_name">SHORT NAME *</label>
                                <input type="text" class="form-control" id="short_name" name="short_name"
                                    value="{{ $pwa_settings['pwa_short_name'] ?? 'Taskify' }}" maxlength="12" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="theme_color">THEME COLOR *</label>
                                <div class="d-flex align-items-center">
                                    <input type="color" class="form-control form-control-color me-2" id="theme_color"
                                        name="theme_color" value="{{ $pwa_settings['pwa_theme_color'] }}" required>
                                    <input type="text" class="form-control" id="theme_color_text"
                                        value="{{ $pwa_settings['pwa_theme_color'] }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="background_color">BACKGROUND COLOR *</label>
                                <div class="d-flex align-items-center">
                                    <input type="color" class="form-control form-control-color me-2" id="background_color"
                                        name="background_color" value="{{ $pwa_settings['pwa_background_color']  }}"
                                        required>
                                    <input type="text" class="form-control" id="background_color_text"
                                        value="{{ $pwa_settings['pwa_background_color']  }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="description">DESCRIPTION *</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                            required>{{ $pwa_settings['pwa_description'] ?? 'A task management app to boost productivity' }}</textarea>
                    </div>

                    <div class="form-group mb-3 col-md-6">
                        <label for="logo" class="form-label">
                            {{ get_label('pwa_logo', 'LOGO') }} *
                            @if(isset($pwa_settings['pwa_logo']) && !empty($pwa_settings['pwa_logo']))
                                <a data-bs-toggle="tooltip" data-bs-placement="right"
                                    data-bs-original-title="{{ get_label('view_current_image', 'View current image') }}"
                                    href="{{ asset($pwa_settings['pwa_logo']) }}" data-lightbox="pwa_logo"
                                    data-title="{{ get_label('current_image', 'Current Image') }}">
                                    <i class='bx bx-show-alt'></i>
                                </a>
                            @endif
                        </label>

                        <p class="text-danger small">
                            {{ get_label('pwa_logo_note', 'Please upload maximum 512x512 size PNG logo or else it will not work.') }}
                        </p>

                        <input type="file" class="form-control" id="logo" name="logo" accept="image/png">
                        <small class="text-muted">{{ get_label('recommended_size', 'Recommended Size: 512 x 512') }}</small>

                        @error('logo')
                            <p class="text-danger text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Single Screenshot Section -->
                    <div class="form-group mb-3">
                        <label class="form-label">
                            {{ get_label('pwa_screenshot', 'PWA SCREENSHOT') }}
                            <span class="text-muted">(Optional)</span>
                        </label>
                        <p class="text-info small">
                            {{ get_label('pwa_screenshot_note', 'Recommended size: 1280x720 or 2560x1440 pixels.') }}
                        </p>

                        <div class="screenshot-container border rounded p-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label">Screenshot File</label>
                                    <input type="file" class="form-control" name="screenshot_file" accept="image/*">
                                    <small class="text-muted">PNG, JPG, JPEG (Max: 2MB)</small>
                                </div>

                                <div class="col-md-2 d-flex align-items-end">
                                    @if(!empty($pwa_settings['pwa_screenshot']['src']))
                                        <a href="{{ asset($pwa_settings['pwa_screenshot']['src']) }}" data-lightbox="screenshot"
                                            data-title="PWA Screenshot" class="btn btn-sm btn-outline-primary me-2">
                                            <i class='bx bx-show-alt'></i>
                                        </a>
                                        <button type="button" id="remove-screenshot" class="btn btn-sm btn-outline-danger">
                                            <i class='bx bx-trash'></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @if(!empty($pwa_settings['pwa_screenshot']['src']))
                                <div class="mt-2">
                                    <small class="text-success">
                                        <i class='bx bx-check-circle'></i> Current screenshot:
                                        {{ basename($pwa_settings['pwa_screenshot']['src']) }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex justify-content-start">
                        <button type="submit" class="btn btn-primary me-2" id="submit_btn">
                            {{ get_label('update', 'Update') }}
                        </button>
                        <button type="reset" class="btn btn-outline-secondary">
                            {{ get_label('cancel', 'Cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- PWA Installation Status -->
        {{-- <div class="card mt-4">
            <div class="card-header">PWA Installation Status</div>
            <div class="card-body">
                <div id="pwa-status">
                    <p class="mb-2">Current Installation Status: <span id="install-status"
                            class="badge bg-secondary">Checking...</span></p>
                    <button id="install-btn" class="btn btn-success" style="display: none;">Install App</button>

                </div>
            </div>
        </div> --}}

    </div>

    <script src="{{ asset('assets/js/pages/pwa-settings.js') }}"></script>

@endsection