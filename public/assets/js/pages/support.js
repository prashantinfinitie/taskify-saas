'use strict';
function queryParams(p) {
    return {
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
$(document).on('click', '.delete-ticket', function (e) {
    e.preventDefault();

    var id = $(this).data('id');
    var url = $(this).data('url');
    $('#deleteModalTicket').modal("show");

    $('#deleteConfirm').on('click', function () {
        $('#deleteConfirm').html(label_please_wait).attr('disabled', true);
        $.ajax({
            url: url,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()  // Simplified CSRF token retrieval
            },
            success: function (response) {
                $('#deleteConfirm').html('Delete').attr('disabled', false);
                $('#deleteModalTicket').modal('hide');
                if (!response.error) {
                    toastr.success(response.message);
                    $('#table').bootstrapTable('refresh');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function () {
                $('#deleteConfirm').html('Delete').attr('disabled', false);
                $('#deleteModalTicket').modal('hide');
                toastr.error(label_something_went_wrong);
            }
        });
    });
});
