@extends('layout')

@section('title')
    <?= get_label('meetings', 'Meetings') ?>
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
                        <li class="breadcrumb-item active">
                            <?= get_label('meetings', 'Meetings') ?>
                        </li>

                    </ol>
                </nav>
            </div>
            <div>
            @php
                    $meetingsDefaultView = getUserPreferences('meetings', 'default_view');
                    // dd($meetingsDefaultView);
                @endphp
                @if ($meetingsDefaultView === 'list')
                    <span class="badge bg-primary"><?= get_label('default_view', 'Default View') ?></span>
                @else
                    <a href="javascript:void(0);"><span class="badge bg-secondary" id="set-default-view" data-type="meetings"
                            data-view="list"><?= get_label('set_as_default_view', 'Set as Default View') ?></span></a>
                @endif
            </div>    
            <div>
            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#createMeetingModal"><button type="button" class="btn btn-sm btn-primary action_create_meetings" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-original-title="<?= get_label('create_meeting', 'Create meeting') ?>"><i class='bx bx-plus'></i></button></a>
            <a href="{{ route('meetings.calendar_view') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('calendar_view', 'Calendar view') ?>"><i
                            class='bx bx-calendar'></i></button></a>
        </div>
        </div>
        <x-meetings-card :meetings="$meetings" :users="$users" :clients="$clients" />
    </div>
    <script>
        var label_update = '<?= get_label('update', 'Update') ?>';
        var label_delete = '<?= get_label('delete', 'Delete') ?>';
        var label_duplicate = '<?= get_label('duplicate', 'Duplicate') ?>';
        var label_not_assigned = '<?= get_label('not_assigned', 'Not assigned') ?>';
    </script>
    <script src="{{ asset('assets/js/pages/meetings.js') }}"></script>
@endsection
