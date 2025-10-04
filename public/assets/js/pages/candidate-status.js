$(document).on('click', '#createStatusModal', function () {
    $('#candidateStatusModal').modal('show');
    $('#candidateStatusModalLabel').text('Add Candidate Status');
    // $('#createStatusForm')[0].reset(); // use correct ID here
    $('#candidate_status_id').val('');
});



function queryParams(params) {
    return {
        limit: params.limit,
        offset: params.offset,
        search: params.search,
        sort: params.sort,
        order: params.order
    };
}

$(document).on('click', '.edit-candidate-status-btn', function () {
    const candidateStatus = $(this).data('candidate-status');
    const actionUrl = `/master-panel/candidate-status/update/${candidateStatus.id}`;
    const color = candidateStatus.color; // e.g., "success", "danger", etc.
    $('#status_color')
        .removeClass('select-bg-label-primary select-bg-label-secondary select-bg-label-success select-bg-label-danger select-bg-label-warning select-bg-label-info select-bg-label-dark')
        .addClass(`select-bg-label-${color}`);

    $('#editStatusForm').attr('action', actionUrl)
    $('#editStatusId').val(candidateStatus.id);

    $('#editStatusName').val(candidateStatus.name);

    $('#editStatusModal').modal('show');

});

// ajax for changing order in status


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
                url: "/master-panel/candidate-status/reorder",
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
