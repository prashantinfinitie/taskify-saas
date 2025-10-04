@extends('layout')

@section('title')
    <?= get_label('email_history', 'Email History') ?>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item"><a href="{{ route('home.index') }}">{{ get_label('home', 'Home') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ get_label('email', 'Email') }}</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('emails.send') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-original-title=" <?= get_label('send_email', 'Send Email') ?>"><i
                            class="bx bx-plus"></i></button></a>
                </a>
            </div>
        </div>
        {{-- @dd($emails); --}}
        @if ($emails->count() > 0)
        {{-- console commands for send shedule email --}}
        <div class="card">
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <input type="hidden" id="data_type" value="emails">
                    <input type="hidden" id="data_reload" value="1">
                    <table id="table" data-toggle="table" data-url="{{ route('emails.historyList') }}"
                        data-icons-prefix="bx" data-icons="icons" data-show-refresh="true" data-total-field="total"
                        data-loading-template="loadingTemplate" data-data-field="rows" data-page-list="[5, 10, 20, 50, 100]"
                        data-search="true" data-show-columns="true" data-side-pagination="server" data-pagination="true"
                        data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                        data-query-params="queryParamsEmailHistory">
                        <thead>
                            <tr>
                                <th data-checkbox="true"></th>
                                <th data-field="id" data-sortable="true">{{ get_label('id', 'ID') }}</th>
                                <th data-field="to_email" data-sortable="true">
                                    {{ get_label('recipient_email', 'Recipient Email') }}
                                </th>
                                <th data-field="subject">{{ get_label('subject', 'Subject') }}</th>
                                <th data-field="status">{{ get_label('status', 'Status') }}</th>
                                <th data-field="scheduled_at">{{ get_label('scheduled_at', 'Scheduled At') }}</th>
                                <th data-field="user_name">{{ get_label('created_by', 'Created By') }}</th>
                                <th data-field="" data-formatter="emailHistoryActionsFormatter">
                                    {{ get_label('view', 'View') }}
                                </th>
                                <th data-field="created_at" data-sortable="true">{{ get_label('created_at', 'Created At') }}
                                </th>
                                <th data-field="updated_at" data-sortable="true">
                                    {{ get_label('upadted_at', 'Updated At') }}
                                </th>
                                <th data-field="actions">{{ get_label('actions', 'Actions') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <script>
            var label_update = '<?= get_label('update', 'Update') ?>';
            var label_delete = '<?= get_label('delete', 'Delete') ?>';
            var label_duplicate = '<?= get_label('duplicate', 'Duplicate') ?>';
            const previewUrl = "";
        </script>
        @else
        <?php    $type = 'Emails'; ?>
        <x-empty-state-card :type="$type" />
        @endif

    </div>


    <script src="{{ asset('assets/js/pages/email-history.js') }}"></script>
@endsection