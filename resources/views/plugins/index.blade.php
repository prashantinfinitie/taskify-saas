@extends('layout')

@section('title', get_label('plugins', 'Plugins'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-2 mt-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1">
                    <li class="breadcrumb-item">
                        <a href="{{ url('home') }}">{{ get_label('home', 'Home') }}</a>
                    </li>
                    <li class="breadcrumb-item">{{ get_label('settings', 'Settings') }}</li>
                    <li class="breadcrumb-item active">{{ get_label('plugins', 'Plugins') }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('plugin.upload') }}">
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" title="{{ get_label('install_plugin', 'Install Plugin') }}">
                    <i class="bx bx-plus"></i>
                </button>
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-responsive" id="plugins-table">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Version</th>
                        <th>Status</th>
                        <th>Description</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plugins as $plugin)
                        <tr>
                            <td>{{ $plugin['name'] ?? 'Unknown' }}</td>
                            <td>{{ $plugin['version'] ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $plugin['enabled'] ? 'success' : 'secondary' }}">
                                    {{ $plugin['enabled'] ? 'Enabled' : 'Disabled' }}
                                </span>
                            </td>
                            <td>{{ $plugin['description'] ?? 'N/A' }}</td>
                            <td>
                                @if($plugin['enabled'])
                                    <button class="btn btn-warning btn-sm disable-plugin" data-plugin="{{ $plugin['slug'] }}">
                                        {{ get_label('disable', 'Disable') }}
                                    </button>
                                @else
                                    <button class="btn btn-primary btn-sm enable-plugin" data-plugin="{{ $plugin['slug'] }}">
                                        {{ get_label('enable', 'Enable') }}
                                    </button>
                                @endif
                                <button class="btn btn-danger btn-sm uninstall-plugin" data-plugin="{{ $plugin['slug'] }}">
                                    {{ get_label('uninstall', 'Uninstall') }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No plugins installed.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Uninstall Confirmation Modal -->
<div class="modal fade" id="uninstallPluginModal" tabindex="-1" aria-labelledby="uninstallPluginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="uninstallPluginModalLabel">Confirm Uninstall</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to uninstall this plugin permanently?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm" id="confirm-uninstall-plugin">Uninstall</button>
            </div>
        </div>
    </div>
</div>
<script>
    var label_plugin_enabled = @json(get_label('plugin_enabled','Plugin Enabled Successfully'));
    var label_plugin_disabled = @json(get_label('plugin_disabled','Plugin Disabled Successfully'));
    var label_plugin_uninstalled = @json(get_label('plugin_uninstalled','Plugin Uninstalled Successfully'));
</script>
<script src="{{ asset('assets/js/pages/plugin-manage.js') }}"></script>
@endsection
