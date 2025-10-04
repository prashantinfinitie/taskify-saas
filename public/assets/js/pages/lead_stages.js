
'use strict';
function queryParamsLeadStage(p) {
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

$(document).on('click', '.edit-lead-stage', function () {
    var id = $(this).data('id');
    $('#edit_lead_stage_modal').modal('show');
    var classes = $("#edit_lead_stages_color").attr("class").split(" ");
    var currentColorClass = classes.filter(function (className) {
        return className.startsWith("select-");
    })[0];
    $.ajax({
        url: '/master-panel/lead-stages/get/' + id,
        type: 'get',
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').attr('value') // Replace with your method of getting the CSRF token
        },
        dataType: 'json',
        success: function (response) {
            $("#edit_lead_stage_id").val(response.lead_stage.id);
            $("#edit_lead_stage_name").val(response.lead_stage.name);
            $("#edit_lead_stages_color")
                .val(response.lead_stage.color)
                .removeClass(currentColorClass)
                .addClass("select-bg-label-" + response.lead_stage.color);
        },

    });
});
function enableSortable() {

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Target tbody inside the bootstrap table
    const tbody = $('#table tbody');

    tbody.sortable({
        helper: function (e, tr) {
            let $originals = tr.children();
            let $helper = tr.clone();
            $helper.children().each(function (index) {
                $(this).width($originals.eq(index).width());
            });
            return $helper;
        },
        cursor: "move",
        items: "tr",
        update: function (event, ui) {
            let order = [];
            tbody.find('tr').each(function (index) {
                let id = $(this).find('td:eq(1)').text(); // assuming 2nd column is ID
                if (id) {
                    order.push({
                        id: parseInt(id),
                        position: index + 1
                    });
                }
            });

            $.ajax({
                url: "/master-panel/lead-stages/reorder",
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
                contentType: 'application/json',
                data: JSON.stringify({
                    order: order
                }),
                success: function (response) {
                    if (!response.error) {
                        toastr.success(response.message);
                        $('#table').bootstrapTable('refresh');
                    }
                },
                error: function (xhr) {
                    console.error(xhr.responseJSON);
                }
            });

        }
    }).disableSelection();
}

// Enable sortable after table data loads
$('#table').on('post-body.bs.table', function () {
    enableSortable();
});
