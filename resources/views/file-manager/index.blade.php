@extends('layout')

@section('title')
    <?= get_label('general_file_manager', 'General File Manager') ?>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('general_file_manager', 'General File Manager') ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <strong>Info:</strong> Maximum file upload size is {{ ini_get('upload_max_filesize') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <div id="fm" class="file-manager-height"></div>
    </div>

    <link href="{{ asset('vendor/file-manager/css/file-manager.css') }}" rel="stylesheet">
    <script src="{{ asset('vendor/file-manager/js/file-manager.js') }}"></script>
    {{--
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new FileManager({
                selector: '#fm',
                // Optional: add custom options like language or disk
            });
        });
    </script> --}}
@endsection