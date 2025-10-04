@extends('layout')
@section('title')
    {{ get_label('announcements', 'Announcements') }}
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
                            <?= get_label('announcements', 'Announcements') ?>
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#create_announcement_modal">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"data-bs-placement="left"
                        data-bs-original-title="{{ get_label('create_announcement', 'Create Announcement') }}">
                        <i class="bx bx-plus"></i>
                    </button>
                </a>
            </div>
        </div>
        @if ($announcements->count() > 0)
            <div class="card">
                <div class="card-body">
                    <div id="announcements_calendar"></div>
                </div>
            </div>
        @else
            <?php
            $type = 'Announcements'; ?>
            <x-empty-state-card :type="$type" />
        @endif
    </div>
    <script>
        var label_update = '<?= get_label('update', 'Update') ?>';
        var label_delete = '<?= get_label('delete', 'Delete') ?>';
        var label_duplicate = '<?= get_label('duplicate', 'Duplicate') ?>';
    </script>
    <script src="{{ asset('assets/js/pages/announcements.js') }}"></script>
@endsection
