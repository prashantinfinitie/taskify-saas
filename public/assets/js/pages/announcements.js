$(document).ready(function () {
    $("#all_workspace_users").on("change", function () {
        if ($(this).is(":checked")) {
            $("#select_users_section").addClass("d-none"); // Hide select users
        } else {
            $("#select_users_section").removeClass("d-none"); // Show select users
        }
    });
});
$(document).on('click', '.edit-announcement', function () {
    let id = $(this).data('id');
    let $modal = $('#editAnnouncementModal');
    let $announcementDetailModal = $('#announcementModal');

    // Hide any open modal and show the edit modal
    $announcementDetailModal.modal('hide');
    $modal.modal('show');

    // Fetch the data via AJAX
    $.ajax({
        type: 'GET',
        url: `/master-panel/announcements/${id}/edit`,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        dataType: "json",
        success: function (response) {
            if (response.error === false) {
                const announcement = response.announcement;

                // Format dates
                const formattedStartDate = moment(announcement.start_date).format(js_date_format);
                const formattedEndDate = moment(announcement.end_date).format(js_date_format);

                // Populate the modal fields
                $modal.find('#announcement_id').val(announcement.id);
                $modal.find('#edit_announcement_title').val(announcement.title);
                $modal.find('#edit_announcement_content').val(announcement.content);
                $modal.find('#edit_announcement_start_date').val(formattedStartDate);
                $modal.find('#edit_announcement_end_date').val(formattedEndDate);
                $modal.find('#edit_announcement_priority').val(announcement.priority).trigger('change');

                if (announcement.all_workspace_users) {
                    $('#edit_announcement_all_workspace_users').prop('checked', true);
                    $('#edit_announcement_select_users_section').addClass('d-none');
                } else {
                    $('#edit_announcement_all_workspace_users').prop('checked', false);
                    $('#edit_announcement_select_users_section').removeClass('d-none');

                    // Preselect users
                    const selectedUsers = announcement.selected_users || [];
                    $('#edit_announcement_select_users').val(selectedUsers).trigger('change');
                }
            } else {
                console.error(response.message);
            }
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
});

$(document).ready(function () {
    // Toggle user selection visibility
    $("#edit_announcement_all_workspace_users").on("change", function () {
        if ($(this).is(":checked")) {
            $("#edit_announcement_select_users_section").addClass("d-none");
        } else {
            $("#edit_announcement_select_users_section").removeClass("d-none");
        }
    });

});



