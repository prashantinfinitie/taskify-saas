

function ClientFormatter(value, row, index) {
    return '<ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">' + value + '</ul>';
}
function UserFormatter(value, row, index) {
    return '<ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">' + value + '</ul>';
}
$(function () {
    console.log('Loading projects report...');
    $('#projects_report_table').on('load-success.bs.table', function (e, data) {
        $('#total-projects').text(data.summary.total_projects);
        $('#on-time-projects').text(data.summary.on_time_projects);
        $('#projects-with-due-tasks').text(data.summary.projects_with_due_tasks);
        $('#average-days-remaining').text((data.summary.average_days_remaining || 0).toFixed(2));
        $('#average-task-progress').text((data.summary.average_task_progress || 0).toFixed(2) + '%');
        $('#average-overdue-days-per-project').text((data.summary.average_overdue_days_per_project || 0).toFixed(2));
        $('#total-tasks').text(data.summary.total_tasks);
        $('#average-task-duration').text((data.summary.average_task_duration || 0).toFixed(2) + ' days');
        $('#total-overdue-days').text(data.summary.total_overdue_days);
        $('#overdue-projects-percentage').text((data.summary.overdue_projects_percentage || 0).toFixed(2) + '%');
        $('#average-budget-utilization').text((data.summary.average_budget_utilization || 0).toFixed(2) + '%');
        $('#total-team-members').text(data.summary.total_team_members);
    });


});
$(document).ready(function () {
    $('#export_button').click(function () {
        // Prepare query parameters
        const queryParams = project_report_query_params({ offset: 0, limit: 1000, sort: 'id', order: 'desc', search: '' });
        // Construct the export URL
        const exportUrl = projects_report_export_url + '?' + $.param(queryParams);
        // Open the export URL in a new tab or window
        window.open(exportUrl, '_blank');
    });
    $('#filter_date_range').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        }
    });
    $('#filter_date_range').on('apply.daterangepicker', function (ev, picker) {
        // Set the value of the input field to the selected range
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format(
            'YYYY-MM-DD'));
        // Update the hidden input fields for start and end dates
        $('#filter_start_date').val(picker.startDate.format('YYYY-MM-DD'));
        $('#filter_end_date').val(picker.endDate.format('YYYY-MM-DD'));
        // Trigger a change event to refresh the table
        $('#filter_project, #filter_user, #filter_client, #filter_status, #filter_start_date, #filter_end_date')
            .change();
    });
    $('#filter_date_range').on('cancel.daterangepicker', function (ev, picker) {
        // Clear the input field and hidden fields
        $(this).val('');
        $('#filter_start_date').val('');
        $('#filter_end_date').val('');
        // Trigger a change event to refresh the table
        $('#filter_project, #filter_user, #filter_client, #filter_status, #filter_start_date, #filter_end_date')
            .change();
    });
});
function project_report_query_params(p) {
    // Extract start_date and end_date from the date range picker input
    const dateRange = $('#filter_date_range').val().split(' to ');
    const startDate = dateRange[0] ? dateRange[0] : ''; // Handle if no start date is set
    const endDate = dateRange[1] ? dateRange[1] : ''; // Handle if no end date is set
    return {
        project_id: $('#filter_project').val(),
        user_id: $('#filter_user').val(),
        client_id: $('#filter_client').val(),
        status_id: $('#filter_status').val(),
        start_date: startDate, // Use extracted start date
        end_date: endDate, // Use extracted end date
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}
$('#filter_project, #filter_user, #filter_client, #filter_status, #filter_start_date, #filter_end_date').change(
    function () {
        $('#projects_report_table').bootstrapTable('refresh');
    });

$(function () {
    initSelect2Ajax(
        '#filter_project',
        '/master-panel/tasks/search-projects',
        label_select_project,
        true,
        0,

    );

    initSelect2Ajax(
        '#filter_user',
        '/master-panel/users/search-users',
        label_select_user,
        true,
        0,);

    initSelect2Ajax(
        '#filter_client',
        '/master-panel/clients/search-clients',
        label_select_client,
        true,
        0,);

    initSelect2Ajax(
        '#filter_status',
        '/master-panel/status/search',
        label_filter_status,
        true,
        0,);
});
