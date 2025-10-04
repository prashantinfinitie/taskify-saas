@extends('layout')

@section('title')
    <?= get_label('permission_settings', 'Permission settings') ?>
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
                            <?= get_label('permissions', 'Permissions') ?>
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('roles.create') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('create_role', 'Create role') ?>"><i
                            class='bx bx-plus'></i></button></a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?= get_label('role', 'Role') ?></th>
                                <th><?= get_label('permissions', 'Permissions') ?></th>
                                <th><?= get_label('actions', 'Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                                @if ($role->name == 'superadmin')
                                    <tr>
                                        <td>
                                            <h4 class="text-capitalize fw-bold mb-0">{{ $role->name }}</h4>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-success"><?= get_label('superadmin_has_all_permissions', 'Super Admin has all the permissions') ?></span>
                                        </td>
                                        <td><!-- Hide action buttons for superadmin --></td>
                                    </tr>
                                @endif
                                @if ($role->name == 'manager')
                                    <tr>
                                        <td>
                                            <h4 class="text-capitalize fw-bold mb-0">{{ $role->name }}</h4>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-success"><?= get_label('manager_alert', 'As a Manager, user can access and manage Plans, Subscriptions, Transactions, and Customers And Support') ?></span>
                                        </td>
                                        <td><!-- Hide action buttons for superadmin --></td>
                                    </tr>
                                @endif
                            @endforeach
                            @foreach ($roles as $role)
                                @if ($role->name != 'superadmin' && $role->name != 'manager')
                                    <tr>
                                        <td>
                                            <h4 class="text-capitalize fw-bold mb-0">{{ $role->name }}</h4>
                                        </td>
                                        <?php $permissions = $role->permissions; ?>

                                        @if ($role->name == 'admin')
                                            <td>
                                                <span
                                                    class="badge bg-success"><?= get_label('admin_has_all_permissions', 'Admin has all the permissions') ?></span>
                                            </td>
                                        @elseif (count($permissions) != 0)
                                            <td class="display-flex-wrap">
                                                @foreach ($permissions as $permission)
                                                    <span class="badge rounded p-2 m-1 px-3 bg-primary">
                                                        {{ $role->hasPermissionTo($permission) ? str_replace('_', ' ', $permission->name) : '' }}
                                                    </span>
                                                @endforeach
                                            </td>
                                        @else
                                            <td class="align-items-center">
                                                <span>
                                                    <?= get_label('no_permissions_assigned', 'No Permissions Assigned!') ?>
                                                </span>
                                            </td>
                                        @endif
                                        <td class="align-items-center">
                                            @if (in_array($role->name, ['superadmin', 'admin']))
                                                <!-- Hide action buttons for superadmin and admin -->
                                            @elseif (in_array($role->name, ['client', 'member']))
                                                <!-- Hide delete button for client and team member -->
                                                <div class="d-flex">
                                                    <a href="{{ route('roles.edit', ['id' => $role->id]) }}"
                                                        class="card-link"><i class='bx bx-edit mx-1'></i></a>
                                                </div>
                                            @else
                                                <!-- Display action buttons for other roles -->
                                                <div class="d-flex">
                                                    <a href="{{ route('roles.edit', ['id' => $role->id]) }}"
                                                        class="card-link"><i class='bx bx-edit mx-1'></i></a>
                                                    <a href="javascript:void(0);" type="button"
                                                        data-id="{{ $role->id }}" data-type="roles"
                                                        class="card-link mx-4 delete"><i
                                                            class='bx bx-trash text-danger mx-1'></i></a>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
