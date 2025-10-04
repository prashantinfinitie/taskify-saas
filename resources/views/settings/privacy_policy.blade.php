@extends('layout')

@section('title')
<?= get_label('privacy_policy', 'Privacy policy') ?>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Breadcrumb --}}
    <div class="d-flex justify-content-between mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-style1">
                <li class="breadcrumb-item">
                    <a href="{{ route('home.index') }}">
                        <?= get_label('home', 'Home') ?>
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <?= get_label('settings', 'Settings') ?>
                </li>
                <li class="breadcrumb-item active">
                    <?= get_label('privacy_policy', 'Privacy Policy') ?>
                </li>
            </ol>
        </nav>
    </div>

    {{-- Main Content --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('privacy_policy.store') }}" class="form-submit-event" method="POST"
                        enctype="multipart/form-data">

                        <input type="hidden" name="dnr">
                        <input type="hidden" name="redirect_url" value="{{ route('privacy_policy.index') }}">
                        @csrf
                        @method('PUT')



                        {{-- Form Content --}}
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label" for="privacy_policy">
                                        <?= get_label('privacy_policy', 'Privacy Policy') ?>
                                    </label> <span class="asterisk">*</span>
                                    <textarea class="form-control " name="privacy_policy" id="privacy_policy">
                                        @isset($privacy_policy['privacy_policy'])
                                            {!! trim($privacy_policy['privacy_policy']) !!}
                                        @endisset
                                    </textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" id="submit_btn" class="btn btn-primary">
                                    {{ get_label('save', 'Save') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
