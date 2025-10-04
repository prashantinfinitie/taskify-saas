
'use strict';

function queryParams(p) {
    return {
        "user_id": $('#user_filter').val(),
        "invoice_id": $('#invoice_filter').val(),
        "pm_id": $('#payment_method_filter').val(),
        "date_from": $('#payment_date_from').val(),
        "date_to": $('#payment_date_to').val(),
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

function actionsFormatter(value, row, index) {
    return [
        '<a href="javascript:void(0);" class="edit-payment" data-bs-toggle="modal" data-id=' + row.id + ' title=' + label_update + ' class="card-link"><i class="bx bx-edit mx-1"></i></a>' +
        '<button title=' + label_delete + ' type="button" class="btn delete" data-id=' + row.id + ' data-type="payments">' +
        '<i class="bx bx-trash text-danger mx-1"></i>' +
        '</button>'
    ]
}

$('#payment_date_between').on('apply.daterangepicker', function (ev, picker) {
    var fromDate = picker.startDate.format('YYYY-MM-DD');
    var toDate = picker.endDate.format('YYYY-MM-DD');

    $('#payment_date_from').val(fromDate);
    $('#payment_date_to').val(toDate);

    $('#table').bootstrapTable('refresh');
});

$('#payment_date_between').on('cancel.daterangepicker', function (ev, picker) {
    $('#payment_date_from').val('');
    $('#payment_date_to').val('');
    $('#table').bootstrapTable('refresh');
    $('#payment_date_between').val('');
});

$('#user_filter,#invoice_filter,#payment_method_filter').on('change', function (e) {
    e.preventDefault();
    $('#table').bootstrapTable('refresh');
});

$(function () {
    initSelect2Ajax(
        '#invoice_filter',
        '/master-panel/payments/search-invoices',
        label_select_invoice,
        true,
        0,
        true
    );
    initSelect2Ajax(
        '#select_invoice',
        '/master-panel/payments/search-invoices',
        label_select_invoice,
        true,
        0,
        true
    );

    initSelect2Ajax(
        '#user_filter',
        '/master-panel/users/search-users',
        label_select_user,
        true,
        0,
        true
    );

    initSelect2Ajax(
        '#select_user',
        '/master-panel/users/search-users',
        label_select_user,
        true,
        0,
        true
    );

    initSelect2Ajax(
        '#payment_user_id',
        '/master-panel/users/search-users',
        label_select_user,
        true,
        0,
        true
    );


    $('#payment_method_filter , #select_payment_method,#payment_pm_id').select2();
});
