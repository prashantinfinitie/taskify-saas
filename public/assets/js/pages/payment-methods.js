
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

function actionsFormatter(value, row, index) {
    return [
        '<a href="javascript:void(0);" class="edit-pm" data-id=' + row.id + ' title=' + label_update + ' class="card-link"><i class="bx bx-edit mx-1"></i></a>' +
        '<button title=' + label_delete + ' type="button" class="btn delete" data-id=' + row.id + ' data-type="payment-methods">' +
        '<i class="bx bx-trash text-danger mx-1"></i>' +
        '</button>'
    ]
}

$(document).on('click', '.edit-pm', function () {
    var id = $(this).data('id');
    var routePrefix = $('#table').data('routePrefix');
    $('#edit_pm_modal').modal('show');
    $.ajax({
        url: routePrefix + '/payment-methods/get/' + id,
        type: 'get',
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').attr('value') // Replace with your method of getting the CSRF token
        },
        dataType: 'json',
        success: function (response) {
            $('#pm_id').val(response.pm.id)
            $('#pm_title').val(response.pm.title)
        },

    });
});


document.addEventListener('DOMContentLoaded', function () {
    // Parse the fragment from the URL
    var fragment = window.location.hash.substring(1);

    // If fragment exists and matches a tab, activate it
    if (fragment) {
        var tab = document.querySelector('.nav-link[data-bs-target="#' + fragment + '"]');
        if (tab) {
            // Remove 'active' class from all tabs
            var allTabs = document.querySelectorAll('.nav-link');
            allTabs.forEach(function (tab) {
                tab.classList.remove('active');
            });

            // Add 'active' class to the selected tab
            tab.classList.add('active');

            // Show the corresponding tab pane
            var tabPane = document.querySelector(tab.getAttribute('data-bs-target'));
            if (tabPane) {
                // Remove 'show' and 'active' classes from all tab panes
                var allTabPanes = document.querySelectorAll('.tab-pane');
                allTabPanes.forEach(function (pane) {
                    pane.classList.remove('show', 'active');
                });

                // Add 'show' and 'active' classes to the selected tab pane
                tabPane.classList.add('show', 'active');
            }
        }
    }
});