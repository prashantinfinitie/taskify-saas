@extends('layout')

@section('title')
    <?= get_label('global_asset_analytics', 'Global Asset Analytics') ?>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ url('home') }}">{{ get_label('home', 'Home') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('assets.index') }}">{{ get_label('assets', 'Assets') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ get_label('analytics', 'Analytics') }}
                        </li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary mt-4">
                <i class="bx bx-arrow-back"></i> <?= get_label('back', 'Back') ?>
            </a>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm" style="height: 400px;">
                    <div class="card-header">
                        <i class="bx bx-pie-chart"></i> <?= get_label('asset_status_charts', 'Asset Status Chart') ?>
                    </div>
                    <div class="card-body d-flex justify-content-center align-items-center" style="height: 350px;">
                        <div id="statusChart" style="max-height: 350px; width: 100%;"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm" style="height: 400px;">
                    <div class="card-header">
                        <i class="bx bx-list-ul"></i> <?= get_label('asset_status_summary', 'Asset Status Summary') ?>
                    </div>
                    <div class="card-body" style="height: 350px; overflow-y: auto;">
                        <ul class="list-unstyled m-0">
                            @foreach ($statusData as $status => $count)
                                <li class="d-flex justify-content-between align-items-center border-bottom p-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="badge rounded-circle"
                                            style="width: 15px; height: 15px; background-color: {{ \Plugins\AssetManagement\Models\Asset::getStatusColor($status) }};">
                                        </span>
                                        <span class="fw-semibold">{{ get_label('assets_status', ucfirst(str_replace('-', ' ', $status))) }}</span>
                                    </div>
                                    <span class="badge bg-label-primary">{{ $count }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <i class="bx bx-user"></i> <?= get_label('users_and_assigned_assets', 'Users and Assigned Assets') ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive text-nowrap">
                    <table class="table-hover table">
                        <thead>
                            <tr>
                                <th>{{ get_label('users', 'User') }}</th>
                                <th>{{ get_label('asset_name', 'Asset Name') }}</th>
                                <th>{{ get_label('asset_tag', 'Asset Tag') }}</th>
                                <th>{{ get_label('category', 'Category') }}</th>
                                <th>{{ get_label('status', 'Status') }}</th>
                                <th>{{ get_label('purchase_date', 'Purchase Date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($users as $user)
                                @forelse($user->assets as $asset)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                {!! formatUserHtml($user) !!}
                                            </div>
                                        </td>
                                        <td>{{ $asset->name }}</td>
                                        <td>{{ $asset->asset_tag }}</td>
                                        <td>{{ $asset->category->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge {{ $asset->getStatusBadgeClass() }}">
                                                {{ $asset->status }}
                                            </span>
                                        </td>
                                        <td>{{ $asset->purchase_date ?  format_date($asset->purchase_date, false) : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">{{ get_label('no_assets', $user->first_name . ' has no assets.') }}</td>
                                    </tr>
                                @endforelse
                            @empty
                                <tr>
                                    <td colspan="6">{{ get_label('no_users_with_assigned_assets', 'No users with assigned assets.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.assetAnalyticsData = {
            statusData: {!! json_encode($statusData) !!},
            statusLabels: {!! json_encode(array_map(fn($s) => ucfirst(str_replace('-', ' ', $s)), array_keys($statusData))) !!},
            statusValues: {!! json_encode(array_values($statusData)) !!}
        };
    </script>
     <script src="{{ asset('assets/js/apexcharts.js') }}"></script>
    <script src="{{ asset('assets/js/asset-plugin/assets.js') }}"></script>
@endsection
