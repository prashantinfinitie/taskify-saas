@extends('layout')
@section('title')
    <?= get_label('support', 'Support') ?>
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
                            <?= get_label('support', 'Support') ?>
                        </li>

                    </ol>
                </nav>
            </div>
            @if (auth()->user()->hasRole('admin'))
                <div>
                    <a href="{{ route('support.create') }}"><button type="button" class="btn btn-sm btn-primary"
                            data-bs-toggle="tooltip" data-bs-placement="right"
                            data-bs-original-title=" <?= get_label('create_ticket', 'Create Ticket') ?>"><i
                                class="bx bx-plus"></i></button></a>
                </div>
            @endif
        </div>
        <!-- meetings -->

        <div class="card">
            <div class="card-body">
                <div class="table-responsive text-nowrap">


                        <table id="table" data-toggle="table" data-loading-template="loadingTemplate" data-url="{{ route('support.list') }}"
                            data-icons-prefix="bx" data-icons="icons" data-show-refresh="true" data-total-field="total"
                            data-trim-on-search="false" data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]"
                            data-search="true" data-side-pagination="server" data-show-columns="true" data-pagination="true"
                            data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                            data-query-params="queryParams" data-route-prefix="{{ Route::getCurrentRoute()->getPrefix() }}">

                            <thead>
                                <tr>

                                    <th data-sortable="true" data-field="id"><?= get_label('id', 'ID') ?></th>
                                    <th data-sortable="true" data-field="title"><?= get_label('title', 'Title') ?></th>
                                    <th data-sortable="true" data-visible="false" data-field="description"><?= get_label('description', 'Description') ?>
                                    </th>
                                    <th data-sortable="true" data-field="status"><?= get_label('status', 'Status') ?></th>
                                    <th data-sortable="true" data-field="priority"><?= get_label('priority', 'Priority') ?></th>
                                    <th data-sortable="false" data-field="created_by">
                                        <?= get_label('created_by', 'Created By') ?></th>
                                    <th data-sortable="true" data-field="created_at" data-visible="false">
                                        <?= get_label('created_at', 'Created at') ?></th>
                                    <th data-sortable="true" data-field="updated_at" data-visible="false">
                                        <?= get_label('updated_at', 'Updated at') ?></th>
                                    <th data-field="actions"><?= get_label('actions', 'Actions') ?></th>
                                </tr>
                            </thead>
                        </table>



                </div>
            </div>
        </div>


    </div>

    <div class="modal fade" id="deleteModalTicket" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel"><?= get_label('delete', 'Delete') ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><?= get_label('confirm_delete', 'Are you sure you want to delete this?') ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= get_label('cancel', 'Cancel') ?></button>
                    <button type="button" class="btn btn-danger" id="deleteConfirm"><?= get_label('delete', 'Delete') ?></button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/pages/support.js') }}"></script>
@endsection
