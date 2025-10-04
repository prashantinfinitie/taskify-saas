@extends('layout')

@section('title')
    <?= get_label('create_workspace', 'Create workspace') ?>
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
                            <a href="{{ route('workspaces.index') }}"><?= get_label('workspaces', 'Workspaces') ?></a>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('create', 'Create') ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card ">
            <div class="card-body">
                <form action="{{ route('workspaces.store') }}" class="form-submit-event" method="POST">
                    <input type="hidden" name="redirect_url" value="{{ route('workspaces.index') }}">
                    @csrf
                    <div class="row">
                        <div class="mb-3">
                            <label for="title" class="form-label"><?= get_label('title', 'Title') ?> <span
                                    class="asterisk">*</span></label>
                            <input class="form-control" type="text" id="title" name="title"
                                placeholder="{{ (get_label('enter_title', 'Enter Title')) }}" value="{{ old('title') }}">
                            @error('title')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 ">
                            <label class="form-label"
                                for="user_id"><?= get_label('select_users', 'Select users') ?></label>
                            <div class="input-group">
                                <select id="" class="form-control js-example-basic-multiple" name="user_ids[]"
                                    multiple="multiple"
                                    data-placeholder="<?= get_label('type_to_search', 'Type to search') ?>">
                                    @foreach ($admin->teamMembers as $teamMember)
                                        <option value="{{ $teamMember->user->id }}"
                                            {{ $teamMember->user->id == auth()->id() ? 'selected' : '' }}>
                                            {{ $teamMember->user->first_name }} {{ $teamMember->user->last_name }}
                                        </option>
                                    @endforeach
                                    <option value="{{ $admin->user->id }}"
                                        {{ $admin->user->id == auth()->id() ? 'selected' : '' }}>
                                        {{ $admin->user->first_name }} {{ $admin->user->last_name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="mb-3">
                            <label class="form-label"
                                for="client_id"><?= get_label('select_clients', 'Select clients') ?></label>
                            <div class="input-group">

                                <select id="" class="form-control js-example-basic-multiple" name="client_ids[]"
                                    multiple="multiple"
                                    data-placeholder="<?= get_label('type_to_search', 'Type to search') ?>">
                                    @foreach ($clients as $client)
                                        <?php $selected = $client->id == getAuthenticatedUser()->id && $auth_user->hasRole('client') ? 'selected' : ''; ?>
                                        <option value="{{ $client->id }}"
                                            {{ collect(old('client_ids'))->contains($client->id) ? 'selected' : '' }}
                                            <?= $selected ?>>{{ $client->first_name }} {{ $client->last_name }}</option>
                                    @endforeach
                                </select>

                            </div>
                        </div>
                    </div>
                    @if (isAdminOrHasAllDataAccess())
                        <div class="row">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <label class="form-check-label" for="primaryWorkspace">
                                        <input class="form-check-input" type="checkbox" name="primaryWorkspace"
                                            id="primaryWorkspace">
                                        <?= get_label('primary_workspace', 'Primary Workspace') ?>?
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="alert alert-primary alert-dismissible" role="alert">
                        <?= get_label('you_will_be_workspace_participant_automatically', 'You will be workspace participant automatically.') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>

                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary me-2"
                            id="submit_btn"><?= get_label('create', 'Create') ?></button>
                        <button type="reset"
                            class="btn btn-outline-secondary"><?= get_label('cancel', 'Cancel') ?></button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection
