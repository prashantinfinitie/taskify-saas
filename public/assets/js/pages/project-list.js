'use strict';

function queryParamsProjects(p) {
    return {
        "status": $('#status_filter').val(),
        "user_id": $('#projects_user_filter').val(),
        "client_id": $('#projects_client_filter').val(),
        "project_start_date_from": $('#project_start_date_from').val(),
        "project_start_date_to": $('#project_start_date_to').val(),
        "project_end_date_from": $('#project_end_date_from').val(),
        "project_end_date_to": $('#project_end_date_to').val(),
        "is_favorites": $('#is_favorites').val(),
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

window.icons = {
    refresh: 'bx-refresh',
    toggleOn: 'bx-toggle-right',
    toggleOff: 'bx-toggle-left'
}

function loadingTemplate(message) {
    return '<i class="bx bx-loader-alt bx-spin bx-flip-vertical" ></i>'
}

$('#status_filter,#projects_user_filter,#projects_client_filter').on('change', function (e) {
    e.preventDefault();
    $('#projects_table').bootstrapTable('refresh');
});

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

$(document).ready(function () {
    initSelect2Ajax(
        '#status_filter',
        '/master-panel/status/search',
        label_filter_status,
        true,                                  // Allow clear
        0,                                     // Minimum input length
        true                                   // Allow Initials Options
    );

    initSelect2Ajax(
        '#projects_user_filter',
        '/master-panel/users/search-users',
        label_select_user,
        true,                                  // Allow clear
        0,                                     // Minimum input length
        true                                   // Allow Initials Options
    );

    initSelect2Ajax(
        '#projects_client_filter',
        '/master-panel/clients/search-clients',
        label_select_client,
        true,                                  // Allow clear
        0,                                     // Minimum input length
        true                                   // Allow Initials Options
    );
});