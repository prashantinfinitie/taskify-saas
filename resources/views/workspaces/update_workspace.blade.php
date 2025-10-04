@extends('layout')

@section('title')
    <?= get_label('update_workspace', 'Update workspace') ?>
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
                        <li class="breadcrumb-item">
                            <?= $workspace->title ?>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('update', 'Update') ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('workspaces.update', ['id' => $workspace->id]) }}" class="form-submit-event"
                    method="POST">
                    <input type="hidden" name="redirect_url" value="{{ route('workspaces.index') }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="mb-3">
                            <label for="title" class="form-label"><?= get_label('title', 'Title') ?> <span
                                    class="asterisk">*</span></label>
                            <input class="form-control" type="text" id="title" name="title"
                                placeholder="Enter Title" value="{{ $workspace->title }}">
                            @error('title')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="mb-3">
                            <label class="form-label"
                                for="user_id"><?= get_label('select_users', 'Select users') ?></label>
                            <div class="input-group">
                                <select id="" class="form-control js-example-basic-multiple" name="user_ids[]"
                                    multiple="multiple"
                                    data-placeholder="<?= get_label('type_to_search', 'Type to search') ?>">
                                    <?php
                                    $workspace_users = $workspace->users;
                                    ?>
                                    @foreach ($admin->teamMembers as $teamMember)
                                    <option value="{{ $teamMember->user->id }}" <?php if ($workspace_users->contains($teamMember->user)) {


                                            echo 'selected';
                                        } ?>>{{ $teamMember->user->first_name }}

                                        {{ $teamMember->user->last_name }}</option>


                                    @endforeach
                                    <option value="{{ $admin->user->id }}" {{ $workspace_users->contains($admin->user) ? 'selected' : '' }}>

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
                                    <?php
                                    $workspace_clients = $workspace->clients;
                                    ?>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}" <?php if ($workspace_clients->contains($client)) {
                                            echo 'selected';
                                        } ?>>{{ $client->first_name }}
                                            {{ $client->last_name }}</option>
                                    @endforeach
                                </select>

                            </div>
                        </div>
                    </div>

                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary me-2"
                            id="submit_btn"><?= get_label('update', 'Update') ?></button>
                        <button type="reset"
                            class="btn btn-outline-secondary"><?= get_label('cancel', 'Cancel') ?></button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection
