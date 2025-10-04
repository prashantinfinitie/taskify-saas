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
    toggleOff: 'bx-toggle-left',
    toggleOn: 'bx-toggle-right'
}
function loadingTemplate(message) {
    return '<i class="bx bx-loader-alt bx-spin bx-flip-vertical" ></i>'
}

function actionFormatter(value, row, index) {

    return [
        '<a href="/superadmin/managers/edit/' + row.id + '" title=' + label_update + '>' +
        '<i class="bx bx-edit mx-1">' +
        '</i>' +
        '</a>' +
        '<button title=' + label_delete + ' type="button" class="btn delete" data-id=' + row.id + ' data-type="managers">' +
        '<i class="bx bx-trash text-danger mx-1"></i>' +
        '</button>'
    ]
}
$('.delete-manager').on('click', function () {

    var id = $(this).data('id');
    var type = $(this).data('type');
    var routePrefix = $('#table').data('routePrefix');
})

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
