// Edit Lead Sources
$(document).on("click", ".edit-lead-source", function () {
    var id = $(this).data("id");
    $("#edit_lead_source_modal").modal("show");
    $.ajax({
        url: "/master-panel/lead-sources/get/" + id,
        type: "get",
        headers: {
            "X-CSRF-TOKEN": $('input[name="_token"]').attr("value"),
        },
        dataType: "json",
        success: function (response) {

            $("#lead_source_id").val(response.lead_source.id);
            $("#lead_source_name").val(response.lead_source.name);
        },
    });
});