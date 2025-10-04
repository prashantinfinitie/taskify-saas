
window.icons = {
    refresh: 'bx-refresh'
}

function loadingTemplate(message) {
    return '<i class="bx bx-loader-alt bx-spin bx-flip-vertical" ></i>'
}

function actionFormatterUsers(value, row, index) {
    return [
        '<a href="/master-panel/users/edit/' + row.id + '" title=' + label_update + '>' +
        '<i class="bx bx-edit mx-1">' +
        '</i>' +
        '</a>' +
        '<button title=' + label_delete + ' type="button" class="btn delete" data-id=' + row.id + ' data-type="users">' +
        '<i class="bx bx-trash text-danger mx-1"></i>' +
        '</button>'
    ]
}

function actionFormatterClients(value, row, index) {
    return [
        '<a href="/master-panel/clients/edit/' + row.id + '" title=' + label_update + '>' +
        '<i class="bx bx-edit mx-1">' +
        '</i>' +
        '</a>' +
        '<button title=' + label_delete + ' type="button" class="btn delete" data-id=' + row.id + ' data-type="clients">' +
        '<i class="bx bx-trash text-danger mx-1"></i>' +
        '</button>'
    ]
}

// New custom field formatter for checkbox values
function customFieldFormatter(value, row) {
    if (value && typeof value === 'string' && value.startsWith('[') && value.endsWith(']')) {
        try {
            // Parse JSON string to array
            const options = JSON.parse(value);
            // Join array elements with comma and space
            return options.join(', ');
        } catch (e) {
            // Return original value if parsing fails
            return value;
        }
    }
    return value;
}

function queryParamsTasks(p) {
    return {
        "status_ids": $('#task_status_filter').val(),
        "priority_ids": $('#task_priority_filter').val(),
        "user_ids": $('#tasks_user_filter').val(),
        "client_ids": $('#tasks_client_filter').val(),
        "project_ids": $('#tasks_project_filter').val(),
        "task_start_date_from": $('#task_start_date_from').val(),
        "task_start_date_to": $('#task_start_date_to').val(),
        "task_end_date_from": $('#task_end_date_from').val(),
        "task_end_date_to": $('#task_end_date_to').val(),
        "task_parent_id": typeof task_parent_id !== "undefined" ? task_parent_id : null,
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}
$('#task_status_filter, #task_priority_filter, #tasks_user_filter, #tasks_client_filter, #tasks_project_filter').on('change', function (e, refreshTable) {
    e.preventDefault();
    if (typeof refreshTable === 'undefined' || refreshTable) {
        $('#task_table').bootstrapTable('refresh');
    }
});

function userFormatter(value, row, index) {
    return '<div class="d-flex">' +
        row.profile +
        '</div>';

}

function clientFormatter(value, row, index) {
    return '<div class="d-flex">' +
        row.profile +
        '</div>';
}

function assignedFormatter(value, row, index) {
    return '<div class="d-flex justify-content-start align-items-center"><div class="text-center mx-4"><span class="badge rounded-pill bg-primary" >' + row.projects + '</span><div>' + label_projects + '</div></div>' +
        '<div class="text-center"><span class="badge rounded-pill bg-primary" >' + row.tasks + '</span><div>' + label_tasks + '</div></div></div>'
}

function queryParamsUsersClients(p) {
    return {
        type: $('#type').val(),
        typeId: $('#typeId').val(),
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

$(document).on('click', '.clear-filters', function (e) {
    e.preventDefault();
    $('#task_start_date_between').val('');
    $('#task_end_date_between').val('');
    $('#task_start_date_from').val('');
    $('#task_start_date_to').val('');
    $('#task_end_date_from').val('');
    $('#task_end_date_to').val('');
    $('#tasks_project_filter').val('').trigger('change', [0]);
    $('#tasks_user_filter').val('').trigger('change', [0]);
    $('#tasks_client_filter').val('').trigger('change', [0]);
    $('#task_status_filter').val('').trigger('change', [0]);
    $('#task_priority_filter').val('').trigger('change', [0]);
    $('#task_table').bootstrapTable('refresh');
})

$(document).ready(function () {
    initSelect2Ajax(
        '#tasks_project_filter',
        '/master-panel/tasks/search-projects',
        label_filter_projects,
        true,                                  // Allow clear
        0,                                     // Minimum input length
        true                                   // Allow Initials Options
    );
    initSelect2Ajax(
        '#tasks_user_filter',
        '/master-panel/users/search-users',
        label_filter_users,
        true,                                  // Allow clear
        0,                                     // Minimum input length
        true                                   // Allow Initials Options
    );

    initSelect2Ajax(
        '#tasks_client_filter',
        '/master-panel/clients/search-clients',
        label_filter_clients,
        true,                                  // Allow clear
        0,                                     // Minimum input length
        true                                   // Allow Initials Options
    );

    initSelect2Ajax(
        '#task_status_filter',
        '/master-panel/status/search',
        label_filter_status,
        true,                                  // Allow clear
        0,                                     // Minimum input length
        true                                   // Allow Initials Options
    );

    initSelect2Ajax(
        '#task_priority_filter',
        '/master-panel/priority/search',
        label_filter_priority,
        true,                                  // Allow clear
        0,                                     // Minimum input length
        true                                   // Allow Initials Options
    );
});

// Add this code at the end of your click event handler for edit-task

// When edit task modal is hidden, reset all custom fields
$("#edit_task_modal").on("hidden.bs.modal", function () {
    // Reset all form inputs in the edit modal
    $("#edit_task_modal form")[0].reset();
    
    // Reset all Select2 dropdowns
    $("#edit_task_modal .js-example-basic-multiple").val(null).trigger("change");
    
    // Reset all custom fields based on their type
    $("#edit_task_modal .custom-field").each(function() {
        var fieldId = $(this).attr('id');
        var fieldType = $(this).attr('type') || $(this).prop('tagName').toLowerCase();
        
        switch(fieldType.toLowerCase()) {
            case 'checkbox':
            case 'radio':
                $(this).prop('checked', false);
                break;
            case 'select':
            case 'textarea':
            case 'text':
            case 'password':
            case 'number':
                $(this).val('');
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).val(null).trigger('change');
                }
                break;
            case 'date':
                $(this).val('');
                break;
        }
    });
    
    // Reset the specific settings sections
    $('#edit-reminder-settings').addClass('d-none');
    $('#edit-recurring-task-settings').addClass('d-none');
    $('#edit-reminder-switch').prop('checked', false);
    $('#edit-recurring-task-switch').prop('checked', false);
    
    // Hide conditional fields
    $('#edit-day-of-week-group').addClass('d-none');
    $('#edit-day-of-month-group').addClass('d-none');
    $('#edit-recurrence-day-of-week-group').addClass('d-none');
    $('#edit-recurrence-day-of-month-group').addClass('d-none');
    $('#edit-recurrence-month-of-year-group').addClass('d-none');
});
