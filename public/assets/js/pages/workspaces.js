
function queryParams(p) {
    return {
        "user_id": $('#workspace_user_filter').val(),
        "client_id": $('#workspace_client_filter').val(),
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
$('#workspace_user_filter,#workspace_client_filter').on('change', function (e) {
    e.preventDefault();
    $('#table').bootstrapTable('refresh');
});
$(document).ready(function () {
    initSelect2Ajax(
        '#workspace_user_filter',
        '/master-panel/users/search-users',
        label_select_user,
        true,                                  // Allow clear
        0,                                     // Minimum input length
        true                                   // Allow Initials Options
    );

    initSelect2Ajax(
        '#workspace_client_filter',
        '/master-panel/clients/search-clients',
        label_select_client,
        true,                                  // Allow clear
        0,                                     // Minimum input length
        true                                   // Allow Initials Options
    );
});
