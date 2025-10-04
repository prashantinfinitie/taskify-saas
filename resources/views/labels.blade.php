<script>
    var label_please_wait = '{{ get_label('please_wait', 'Please wait...') }}';
    var label_please_select_records_to_delete =
        '{{ get_label('please_select_records_to_delete', 'Please select records to delete.') }}';
    var label_something_went_wrong = '{{ get_label('something_went_wrong', 'Something went wrong.') }}';
    var label_please_correct_errors = '{{ get_label('please_correct_errors', 'Please correct errors.') }}';
    var label_project_removed_from_favorite_successfully =
        '{{ get_label('project_removed_from_favorite_successfully', 'Project removed from favorite successfully.') }}';
    var label_project_marked_as_favorite_successfully =
        '{{ get_label('project_marked_as_favorite_successfully', 'Project marked as favorite successfully.') }}';
    var label_yes = '{{ get_label('yes', 'Yes') }}';
    var label_upload = '{{ get_label('upload', 'Upload') }}';
    var decimal_points = {{ intval($general_settings['decimal_points_in_currency'] ?? '2') }};
    var label_update = '{{ get_label('update', 'Update') }}';
    var label_delete = '{{ get_label('delete', 'Delete') }}';
    var label_view = '{{ get_label('view', 'View') }}';
    var label_not_assigned = '{{ get_label('not_assigned', 'Not assigned') }}';
    var label_delete_selected = '{{ get_label('delete_selected', 'Delete selected') }}';
    var label_search = '{{ get_label('search', 'Search') }}';
    var label_create = '{{ get_label('create', 'Create') }}';
    var label_min_0 = '{{ get_label('value_must_be_greater_then_0', 'Value must be greater than 0') }}';
    var label_max_100 = '{{ get_label('not_greater_then_100', 'Not greater than 100') }}';
    var label_set_as_default_view = '<?= get_label('set_as_default_view', 'Set as Default View') ?>';
    var label_users_associated_with_project =
        '<?= get_label('users_associated_with_project', 'Users associated with project') ?>';
    var label_update_task = '<?= get_label('update_task', 'Update Task') ?>';
    var label_quick_view = '<?= get_label('quick_view', 'Quick View') ?>';
    var label_project = '<?= get_label('project', 'Project') ?>';
    var label_task = '<?= get_label('task', 'Task') ?>';
    var label_projects = '<?= get_label('projects', 'Projects') ?>';
    var label_tasks = '<?= get_label('tasks', 'Tasks') ?>';
    var label_clear_filters = '<?= get_label('clear_filters', 'Clear Filters') ?>';
    var label_set_as_default_view = '<?= get_label('set_as_default_view', 'Set as Default View') ?>';
    var label_default_view = '<?= get_label('default_view', 'Default View') ?>';
    var label_save_column_visibility = '<?= get_label('save_column_visibility', 'Save Column Visibility') ?>';
    var preview_not_available_label = '{{ get_label('preview_not_available', 'Preview not available') }}';
    var label_filter_status = "<?= get_label('filter_by_status', 'Filter by status') ?>";
    var label_select_user = "<?= get_label('select_user', 'Select user') ?>";
    var label_select_client = "<?= get_label('select_client', 'Select client') ?>";
    var label_select_project = "<?= get_label('select_project', 'Select project') ?>";
    var label_select_date_range = "<?= get_label('select_date_range', 'Select date range') ?>";
    var label_select_user = "<?= get_label('select_user', 'Select user') ?>";
    var label_select_client = "<?= get_label('select_client', 'Select client') ?>";
    var label_select_priority = "<?= get_label('select_priority', 'Select priority') ?>";
    var label_create_plan = "{{ get_label('create_plan_button', 'Create Plan') }}";
    var label_update_plan = "{{ get_label('update_plan_button', 'Update Plan') }}";
    var label_status_not_changed_warning =
        "{{ get_label('project_status_unchanged_no_update_performed', 'Project status unchanged. No Update performed.') }}";
    var label_income = "{{ get_label('income', 'Income') }}";
    var label_expenses = "{{ get_label('expenses', 'Expenses') }}";
    var label_income_vs_expenses = "{{ get_label('income_vs_expenses', 'Income vs Expenses') }}";
    var labelTotalIncome = "{{ get_label('total_income', 'Total Income') }}";
    var labelTotalExpenses = "{{ get_label('total_expenses', 'Total Expenses') }}";
    var labelNetResult = "{{ get_label('net_result', 'Net Result') }}";
    var label_total = "{{ get_label('total', 'Total') }}";
    var label_all_time = "{{ get_label('all_time', 'All Time') }}";
    var label_no_task_list = "{{ get_label('no_task_list', 'No Task List') }}";
    var label_searching = "{{ get_label('searching', 'Searching...') }}";
    var label_delete_selected = "{{ get_label('delete_selected', 'Delete selected') }}";
    var label_select_source = "{{ get_label('select_lead_source', 'Select Lead Source') }}";
    var label_select_stage = "{{ get_label('select_lead_stage', 'Select Lead Stage') }}";
    var label_select_candidates = "{{ get_label('select_candidates', 'Select Candidates') }}";
    var label_select_candidates_statuses = "{{ get_label('select_candidates_statuses', 'Select Candidates Statuses') }}";
    var label_select_search_candidates = "{{ get_label('search_candidate', 'Search Candidates') }}";
    var label_select_search_interviewer = "{{ get_label('search_interviewer', 'Search Interviewer') }}";
    var label_select_task_list = "<?= get_label('select_task_list', 'Select Task List') ?>";

    // plugin
    var label_only_one_file_can_be_uploaded_at_a_time = "{{ get_label('only_one_file_can_be_uploaded_at_a_time', 'Only 1 file can be uploaded at a time') }}";
    var label_select = "{{ get_label('select', 'Select') }}";
    var label_or = "{{ get_label('or', 'or') }}";
    var label_update_the_system =  "{{ get_label('update_the_system', 'Update The System') }}";

</script>
