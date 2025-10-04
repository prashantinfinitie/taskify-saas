@extends('layout')
@section('title')
{{ get_label('settings', 'Settings') }}
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
                            <?= get_label('admin_settings', 'Admin Settings') ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin_settings.update') }}" class="form-submit-event" method="POST"
                    enctype="multipart/form-data">
                    <input type="hidden" name="redirect_url" value="{{ route('admin_settings.index') }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="mb-3 col-md-12">
                            <label for="company_title" class="form-label"><?= get_label('company_title', 'Company title') ?>
                                <span class="asterisk">*</span></label>
                            <input class="form-control" type="text" id="company_title" name="company_title"
                                placeholder="Enter company title" value="{{ $general_settings['company_title'] }}">

                            @error('company_title')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror

                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="full_logo" class="form-label"><?= get_label('full_logo', 'Full logo') ?> <a
                                    data-bs-toggle="tooltip" data-bs-placement="right"
                                    data-bs-original-title="<?= get_label('view_current_full_logo', 'View current full logo') ?>"
                                    href="{{ asset($general_settings['full_logo'])}}" data-lightbox="full logo"
                                    data-title="<?= get_label('current_full_logo', 'Current full logo') ?>"> <i
                                        class='bx bx-show-alt'></i></a></label>
                            <input type="file" accept="image/*" class="form-control" id="inputGroupFile02" name="full_logo">
                            @error('full_logo')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="half_logo" class="form-label"><?= get_label('half_logo', 'Half logo') ?> <a
                                    data-bs-toggle="tooltip" data-bs-placement="right"
                                    data-bs-original-title="<?= get_label('view_current_half_logo', 'View current half logo') ?>"
                                    href="{{ asset($general_settings['half_logo']) }}" data-lightbox="half_logo"
                                    data-title="<?= get_label('current_half_logo', 'Current half logo') ?>"> <i
                                        class='bx bx-show-alt'></i></a></label>
                            <input type="file" accept="image/*" class="form-control" id="inputGroupFile02" name="half_logo">
                            @error('half_logo')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>


                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary me-2"
                                id="submit_btn"><?= get_label('update', 'Update') ?></button>
                            <button type="reset"
                                class="btn btn-outline-secondary"><?= get_label('cancel', 'Cancel') ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
