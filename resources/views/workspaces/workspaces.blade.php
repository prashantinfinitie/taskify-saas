@extends('layout')

@section('title')
    <?= get_label('workspaces', 'Workspaces') ?>
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
                            <?= get_label('workspaces', 'Workspaces') ?>
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                 <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#createWorkspaceModal"><button type="button" class="btn btn-sm btn-primary action_create_workspaces" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-original-title="<?= get_label('create_workspace', 'Create workspace') ?>"><i class='bx bx-plus'></i></button></a>
            </div>
        </div>
        <x-workspaces-card :workspaces="$workspaces" :users="$users" :clients="$clients" :admin="$admin" />
    </div>
    @php
        $routePrefix = Route::getCurrentRoute()->getPrefix();
    @endphp

    <script>
        var label_update = '<?= get_label('update', 'Update') ?>';
        var label_delete = '<?= get_label('delete', 'Delete') ?>';
        var label_not_assigned = '<?= get_label('not_assigned', 'Not assigned') ?>';
        var label_duplicate = '<?= get_label('duplicate', 'Duplicate') ?>';
        var routePrefix = '{{ $routePrefix }}';
    </script>
    <script src="{{ asset('assets/js/pages/workspaces.js') }}"></script>
@endsection
