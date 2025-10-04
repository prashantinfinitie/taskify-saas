<!-- tasks -->
@php
    $flag =
        Request::segment(2) == 'home' ||
        Request::segment(2) == 'users' ||
        Request::segment(2) == 'clients' ||
        (isset($viewAssigned) && $viewAssigned == 1) ||
        (Request::segment(2) == 'projects' && Request::segment(3) == 'information' && Request::segment(4) != null)
            ? 0
            : 1;

    $visibleColumns = getUserPreferences('tasks');
@endphp
@if ((isset($tasks) && $tasks > 0) || (isset($emptyState) && $emptyState == 0))
    <div class="<?= $flag == 1 ? 'card ' : '' ?>mt-2">
@endif
@if ($flag == 1 && ((isset($tasks) && $tasks > 0) || (isset($emptyState) && $emptyState == 0)))
    <div class="card-body">
@endif

{{ $slot }}
@if ((isset($tasks) && $tasks > 0) || (isset($emptyState) && $emptyState == 0))
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="input-group input-group-merge">
                <input type="text" id="task_start_date_between" name="task_start_date_between" class="form-control"
                    placeholder="<?= get_label('start_date_between', 'Start date between') ?>" autocomplete="off">
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="input-group input-group-merge">
                <input type="text" id="task_end_date_between" name="task_end_date_between" class="form-control"
                    placeholder="<?= get_label('end_date_between', 'End date between') ?>" autocomplete="off">
            </div>
        </div>
        @if (getAuthenticatedUser()->can('manage_projects'))
            @isset($projects)
                <div class="col-md-4 mb-3">
                    <select class="form-control " id="tasks_project_filter" multiple="multiple"
                        data-placeholder="<?= get_label('select_projects', 'Select Projects') ?>">

                    </select>
                </div>
            @endisset
        @endif
        @if (isAdminOrHasAllDataAccess() && !isset($viewAssigned))
            @if (explode('_', $id)[0] != 'client' && explode('_', $id)[0] != 'user')
                <div class="col-md-4 mb-3">
                    <select class="form-control " id="tasks_user_filter" name="user_ids[]"
                        multiple="multiple" data-placeholder="<?= get_label('select_users', 'Select Users') ?>">

                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <select class="form-control " id="tasks_client_filter" name="client_ids[]"
                        multiple="multiple" data-placeholder="<?= get_label('select_clients', 'Select Clients') ?>">>

                    </select>
                </div>
            @endif
        @endif

        <div class="col-md-4 mb-3">
            <select class="form-control" id="task_status_filter" name="status_ids[]" multiple="multiple"
                data-placeholder="<?= get_label('select_statuses', 'Select Statuses') ?>">

            </select>
        </div>

        <div class="col-md-4 mb-3">
            <select class="form-control" id="task_priority_filter" name="priority_ids[]" multiple="multiple"
                data-placeholder="<?= get_label('select_priorities', 'Select Priorities') ?>">

            </select>
        </div>
    </div>

    <input type="hidden" name="task_start_date_from" id="task_start_date_from">
    <input type="hidden" name="task_start_date_to" id="task_start_date_to">

    <input type="hidden" name="task_end_date_from" id="task_end_date_from">
    <input type="hidden" name="task_end_date_to" id="task_end_date_to">

    <div class="table-responsive text-nowrap">
        <input type="hidden" id="data_type" value="tasks">
        <input type="hidden" id="data_table" value="task_table">
        <input type="hidden" id="save_column_visibility">
        <table id="task_table" data-toggle="table" data-loading-template="loadingTemplate"
            data-url="{{ isset($viewAssigned) && $viewAssigned == 1 ? '' : (!empty($id) ? route('tasks.list', ['id' => $id]) : route('tasks.list')) }}"
            data-icons-prefix="bx" data-icons="icons" data-show-refresh="true" data-total-field="total"
            data-trim-on-search="false" data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]"
            data-search="true" data-side-pagination="server" data-show-columns="true" data-pagination="true"
            data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
            data-query-params="queryParamsTasks">
            <thead>
                <tr>
                    <th data-checkbox="true"></th>

                    <th data-field="id"
                        data-visible="{{ in_array('id', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                        data-sortable="true">{{ get_label('id', 'ID') }}</th>
                    <th data-field="title"
                        data-visible="{{ in_array('title', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                        data-sortable="true">{{ get_label('task', 'Task') }}</th>
                    <th data-field="project_id"
                        data-visible="{{ in_array('project_id', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                        data-sortable="true">{{ get_label('project', 'Project') }}</th>
                    <th data-field="users"
                        data-visible="{{ in_array('users', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                        {{ get_label('users', 'Users') }}</th>
                    <th data-field="clients"
                        data-visible="{{ in_array('clients', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                        {{ get_label('clients', 'Clients') }}</th>
                    <th data-field="status_id" class="status-column"
                        data-visible="{{ in_array('status_id', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                        data-sortable="true">{{ get_label('status', 'Status') }}</th>
                    <th data-field="priority_id" class="priority-column"
                        data-visible="{{ in_array('priority_id', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                        data-sortable="true">{{ get_label('priority', 'Priority') }}</th>
                    <th data-field="billing_type" data-visible="{{ in_array('billing_type', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">{{ get_label('billing_type', 'Billing type') }}</th>
                    <th data-field="start_date"
                        data-visible="{{ in_array('start_date', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                        data-sortable="true">{{ get_label('starts_at', 'Starts at') }}</th>
                    <th data-field="end_date"
                        data-visible="{{ in_array('end_date', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                        data-sortable="true">{{ get_label('ends_at', 'Ends at') }}</th>
                    <th data-field="created_at"
                        data-visible="{{ in_array('created_at', $visibleColumns) ? 'true' : 'false' }}"
                        data-sortable="true"><?= get_label('created_at', 'Created at') ?></th>
                    <th data-field="updated_at"
                        data-visible="{{ in_array('updated_at', $visibleColumns) ? 'true' : 'false' }}"
                        data-sortable="true"><?= get_label('updated_at', 'Updated at') ?></th>
                    
                    
                        @if(isset($customFields) && $customFields->isNotEmpty())

                                @foreach ($customFields as $customField)
                                    {{-- @dd($customField) --}}
                                    @if($customField->visibility !== null)
                                        <th data-field="custom_field_{{ $customField->id }}"
                                            data-visible="{{ (in_array('custom_field_' . $customField->id, $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}"
                                            data-sortable="true"
                                            data-formatter="{{ $customField->field_type === 'checkbox' ? 'customFieldFormatter' : '' }}"
                                            class="truncate-col" title="{{ $customField->field_label }}">
                                            {{ $customField->field_label }}
                                        </th>
                                    @endif
                                @endforeach
                            @endif
                        <th data-field="actions"
                        data-visible="{{ in_array('actions', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                        {{ get_label('actions', 'Actions') }}</th>
                </tr>
            </thead>
        </table>
    </div>

@else
    @if (!isset($emptyState) || $emptyState != 0)
        <?php
        $type = 'Tasks';
        ?>
        <x-empty-state-card :type="$type" />
    @endif
@endif

@if ((isset($tasks) && $tasks > 0) || (isset($emptyState) && $emptyState == 0))
    @if($flag == 1)
    </div>
    </div>
    @endif

@endif

<script>
    var label_update = '<?= get_label('update', 'Update') ?>';
    var label_delete = '<?= get_label('delete', 'Delete') ?>';
    var label_duplicate = '<?= get_label('duplicate', 'Duplicate') ?>';
    var label_not_assigned = '<?= get_label('not_assigned', 'Not assigned') ?>';
    var add_favorite = '<?= get_label('add_favorite', 'Click to mark as favorite') ?>';
    var remove_favorite = '<?= get_label('remove_favorite', 'Click to remove from favorite') ?>';
    var label_filter_projects = "<?= get_label('select_projects', 'Select projects') ?>";
    var label_filter_users = "<?= get_label('select_users', 'Select users') ?>";
    var label_filter_clients = "<?= get_label('select_clients', 'Select clients') ?>";
    var label_filter_priority= "<?= get_label('select_priority', 'Select Priority') ?>";
    var id = '<?= $id ?? '' ?>';
</script>
<script src="{{ asset('assets/js/pages/tasks.js') }}"></script>

