$(document).ready(function() {
    // Handle sort change
    $("#sort").on("change", function() {
        $("#table").bootstrapTable("refresh");
    });

    // Handle status selection change
    $("#interview_status").on("change", function() {
        $("#table").bootstrapTable("refresh");
    });

    // Handle date range filter apply
    $("#interview_filter_date_range").on("apply.daterangepicker", function(ev, picker) {
        var startDate = picker.startDate.format("YYYY-MM-DD");
        var endDate = picker.endDate.format("YYYY-MM-DD");
        
        // Set the values in hidden fields
        $("#interview_start_date").val(startDate);
        $("#interview_end_date").val(endDate);
        
        // Display the selected date range in the visible input
        $(this).val(startDate + ' - ' + endDate);
        
        // Refresh the table with new filters
        $("#table").bootstrapTable("refresh");
    });

    // Handle date range filter cancel/clear
    $("#interview_filter_date_range").on("cancel.daterangepicker", function(ev, picker) {
        // Clear all values
        $("#interview_start_date").val('');
        $("#interview_end_date").val('');
        $(this).val('');
        
        // Reset picker
        picker.setStartDate(moment());
        picker.setEndDate(moment());
        picker.updateElement();
        
        // Refresh the table
        $("#table").bootstrapTable("refresh");
    });

    // Handle interview edit button click
    $(document).on("click", ".edit-interview-btn", function() {
        const interview = $(this).data("interview");

        if (!interview || !interview.id) {
            console.error("Invalid interview data:", interview);
            toastr.error(label_something_went_wrong);
            return;
        }

        // Construct the form action URL dynamically
        const actionUrl = `/master-panel/interviews/update/${interview.id}`;
        $("#editInterviewForm").attr("action", actionUrl);

        // Set candidate value - check which select element exists
        if ($("#edit_search_candidates").length) {
            // For interviews.index route
            $("#edit_search_candidates").val(interview.candidate_id).trigger("change");
        } else if ($("#candidate_id").length) {
            // For other routes
            $("#candidate_id").val(interview.candidate_id).trigger("change");
        }

        // Set interviewer value - also check for the correct ID
        if ($("#edit_search_interviewer").length) {
            $("#edit_search_interviewer").val(interview.interviewer_id).trigger("change");
        } else if ($("#interviewer_id").length) {
            $("#interviewer_id").val(interview.interviewer_id).trigger("change");
        }

        // Set other form values
        $("#round").val(interview.round || "");
        $("#scheduled_at").val(interview.scheduled_at || "");
        $("#mode").val(interview.mode || "");
        $("#location").val(interview.location || "");
        $("#status").val(interview.status || "");

        // Open the modal
        $("#editInterviewModal").modal("show");
    });
});

// Query parameters function for BootstrapTable
function queryParams(params) {
    return {
        page: params.offset / params.limit + 1,
        limit: params.limit,
        sort: params.sort,
        order: params.order,
        offset: params.offset,
        search: params.search,
        sort: $("#sort").val(),
        status: $("#interview_status").val(),
        start_date: $("#interview_start_date").val(),
        end_date: $("#interview_end_date").val()
    };
}