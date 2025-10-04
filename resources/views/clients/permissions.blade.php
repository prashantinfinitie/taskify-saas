@extends('layout')

@section('title')
    <?= get_label('permissions', 'Permissions') ?>
@endsection
<?php

use Spatie\Permission\Models\Permission; ?>
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item ">
                            <a href="{{ route('clients.index') }}"><?= get_label('clients', 'Clients') ?></a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ get_label('permissions', 'Permissions') }}
                        </li>
                    </ol>
                </nav>
            </div>
            <div>

            </div>
        </div>

        <div class="card">
            <div class="card-header">
                {{ get_label('client_permissions', 'Client Permissions') }} - {{ $client->first_name }}
                {{ $client->last_name }}
            </div>
            <div class="card-body">

                @if ($role->name == 'admin')
                    <div class="alert alert-primary alert-dismissible">
                        <span class="text-primary">
                            {{ get_label('admin_has_all_permissions', 'Admin has all the permissions') }}
                        </span>
                    </div>
                @else
                    <div class="alert alert-primary alert-dismissible">
                        <span class="text-primary">
                            {{ get_label('permissions_alert', 'Default Checked Permissions Are Those Assigned to the User\'s Role') }}
                        </span>
                    </div>

                    <div class="table-responsive text-nowrap">
                        <form id = "ClientPermissions" method = "POST"
                            action= "{{ route('clients.update_permissions', ['client' => $client->id]) }}">
                            @method('put')
                            @csrf
                            <table class="table my-2">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="form-check">
                                                <input type="checkbox" id="selectAllColumnPermissions"
                                                    class="form-check-input">
                                                <label class="form-check-label" for="selectAllColumnPermissions">
                                                    <?= get_label('select_all', 'Select all') ?>
                                                </label>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (config('taskify.permissions') as $module => $permissions)
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <input type="checkbox" id="selectRow{{ $module }}"
                                                        class="form-check-input row-permission-checkbox"
                                                        data-module="{{ $module }}">
                                                    <label class="form-check-label"
                                                        for="selectRow{{ $module }}">{{ $module }}</label>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex flex-wrap justify-content-between">
                                                    @foreach ($permissions as $permission)
                                                        <div class="form-check mx-4">
                                                            @if ($role->guard_name == 'client')
                                                                <?php
                                                                $permissionModel = Permission::where('name', $permission)->where('guard_name', 'client')->first();
                                                                ?>
                                                                <input type="checkbox" name="permissions[]"
                                                                    value="{{ $permissionModel ? $permissionModel->id : '' }}"
                                                                    class="form-check-input permission-checkbox"
                                                                    data-module="{{ $module }}"
                                                                    {{ $mergedPermissions->contains('name', $permission) ? 'checked' : '' }}>
                                                                <label
                                                                    class="form-check-label text-capitalize">{{ $permissionModel ? substr($permissionModel->name, 0, strpos($permissionModel->name, '_')) : '' }}</label>
                                                            @else
                                                                <input type="checkbox" name="permissions[]"
                                                                    value="<?php print_r(Permission::findByName($permission)->id); ?>"
                                                                    class="form-check-input permission-checkbox"
                                                                    data-module="{{ $module }}"
                                                                    {{ $mergedPermissions->contains('name', $permission) ? 'checked' : '' }}>
                                                                <label
                                                                    class="form-check-label text-capitalize"><?php print_r(substr($permission, 0, strpos($permission, '_'))); ?></label>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-start">
                                <button type="submit" class="btn btn-primary"><?= get_label('save', 'Save') ?></button>
                            </div>
                        </form>

                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
