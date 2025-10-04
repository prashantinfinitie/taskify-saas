@extends('layout')

@section('title')
    <?= get_label('terms_and_conditions', 'Terms and conditions') ?>
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
                            <?= get_label('terms_and_conditions', 'Terms and Conditons') ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="col-12">
            <div class="card">

                <div class="card-body">
                    <form action="{{ route('terms_and_conditions.store') }}" class="form-submit-event" method="POST"
                        enctype="multipart/form-data">
                       <input type="hidden" name="dnr">
                        @csrf
                        @method('PUT')

                        {{-- Form Content --}}
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label" for="terms_and_conditions">
                                        <?= get_label('terms_and_conditions', 'Terms and Conditions') ?>
                                    </label> <span class="asterisk">*</span>
                                    <textarea class="form-control " name="terms_and_conditions" id="terms_and_conditions">
                                        @isset($terms_and_conditions['terms_and_conditions'])
                                            {!! trim($terms_and_conditions['terms_and_conditions']) !!}
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
@endsection
