@extends('layout')

@section('title')
    <?= get_label('create_language', 'Create language') ?>
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
                        <li class="breadcrumb-item">
                            <a href="{{ route('languages.index') }}"><?= get_label('languages', 'Languages') ?></a>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('create', 'Create') ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('languages.store') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="mb-3">
                            <label for="name">
                                Name
                                <span class="asterisk">*</span></label>
                            <input class="form-control" type="text" id="name" name="name"
                                placeholder="For Example: English" value="{{ old('name') }}">
                            @error('name')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror

                        </div>
                        <div class="mb-3">
                            <label for="code">
                                Code
                                <span class="asterisk">*</span></label>
                            <input class="form-control" type="text" id="code" name="code"
                                placeholder="For Example: en" value="{{ old('code') }}">
                            @error('code')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary me-2"
                            id="showToastPlacement"><?= get_label('create', 'Create') ?></button>
                        <button type="reset"
                            class="btn btn-outline-secondary"><?= get_label('cancel', 'Cancel') ?></button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection
