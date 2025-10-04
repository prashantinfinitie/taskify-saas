'use strict';

function queryParams(p) {
    return {
        "status": $('#client_status_filter').val(),
        "internal_purpose": $('#client_internal_purpose_filter').val(),
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}



window.icons = {
    refresh: 'bx-refresh'
}

function loadingTemplate(message) {
    return '<i class="bx bx-loader-alt bx-spin bx-flip-vertical" ></i>'
}

function nameFormatter(value, row, index) {
    return [row.first_name, row.last_name].join(' ')
}


$('#client_status_filter, #client_internal_purpose_filter').on('change', function (e) {
    e.preventDefault();
    $('#table').bootstrapTable('refresh');
});
$(document).ready(function () {
    $('#client_status_filter').select2();
    $('#client_internal_purpose_filter').select2();
});

$(document).ready(function () {
    if ($('#phone-input').length) {
        const phoneInput = initPhoneInput('phone-input');
        $('form').on('submit', function (e) {
            $('#country_code').val(phoneInput.getCountryCode());
            $('#phone_number').val(phoneInput.getNumber().replace(/\s+/g, ''));
            $('#country_iso_code').val(phoneInput.getISOCode());

        });
    }
});
$(document).ready(function () {
    if ($('#phone-input-edit').length) {

        const phoneInput = initPhoneInput('phone-input-edit', $('#country_code').val(), $('#country_iso_code').val());
        $('form').on('submit', function (e) {
            $('#country_code').val(phoneInput.getCountryCode());
            $('#phone_number').val(phoneInput.getNumber().replace(/\s+/g, ''));
            $('#country_iso_code').val(phoneInput.getISOCode());

        });
    }
});
