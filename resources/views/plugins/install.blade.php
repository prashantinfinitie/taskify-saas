@extends('layout')
@section('title', get_label('install_plugin', 'Install Plugin'))
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ url('home') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item">
                            <?= get_label('settings', 'Settings') ?>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('plugins.index') }}">
                                <?= get_label('plugins', 'Plugins') ?>
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?= get_label('install_plugin', 'Install Plugin') ?>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <form class="form-horizontal" id="install-plugin" action="{{ route('plugin.install') }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <div class="dropzone dz-clickable" id="install-plugin-dropzone">
                                </div>
                                <div class="form-group mt-4 text-center">
                                    <button class="btn btn-primary"
                                        id="install_plugin_btn"><?= get_label('install_plugin', 'Install Plugin') ?></button>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <div class="form-group" id="error_box">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
