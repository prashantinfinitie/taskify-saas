$(function () {
    console.log("Loading time sheet report...");

    // Modify the load-success event handler
    $("#time_sheet_report_table").on(
        "load-success.bs.table",
        function (e, data) {
            // Check if summary exists in the response

            if (data.summary) {
                $("#total-hours").text(data.summary.total_hours || "0");
                $("#project-total-hours").text(
                    data.summary.project_total_hours
                );
                $("#total-tasks").text(data.summary.total_tasks || "0");
                $("#total-projects").text(data.summary.total_projects || "0");
                $("#total-users").text(data.summary.total_users || "0");
            } else {
                // Fallback if no summary
                $(
                    "#total-hours, #total-tasks, $project-total-hours #total-projects, #total-users"
                ).text("0");
            }
        }
    );
});

function time_sheet_report_query_params(p) {
    // Extract start_date and end_date from the date range picker input
    const dateRange = $("#filter_timesheet_date_range").val().split(" to ");
    const startDate = dateRange[0] ? dateRange[0] : "";
    const endDate = dateRange[1] ? dateRange[1] : "";

    return {
        project_id: $("#filter_project").val(),
        user_id: $("#filter_user").val(),
        start_date_from: startDate,
        start_date_to: endDate,
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search,
    };
}

$(document).ready(function () {
    $("#export_button").click(function () {
        // Prepare query parameters
        const queryParams = time_sheet_report_query_params({
            offset: 0,
            limit: 1000,
            sort: "id",
            order: "desc",
            search: "",
        });
        // Construct the export URL
        const exportUrl =
            time_sheet_report_export_url + "?" + $.param(queryParams);
        // Open the export URL in a new tab or window
        window.open(exportUrl, "_blank");
    });
    $("#filter_timesheet_date_range").daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: "Clear",
        },
    });
    $("#filter_timesheet_date_range").on(
        "apply.daterangepicker",
        function (ev, picker) {
            // Set the value of the input field to the selected range
            $(this).val(
                picker.startDate.format("YYYY-MM-DD") +
                    " to " +
                    picker.endDate.format("YYYY-MM-DD")
            );
            // Update the hidden input fields for start and end dates
            $("#filter_start_date").val(picker.startDate.format("YYYY-MM-DD"));
            $("#filter_end_date").val(picker.endDate.format("YYYY-MM-DD"));
            // Trigger a change event to refresh the table

            $("#time_sheet_report_table").bootstrapTable("refresh");
        }
    );
    $("#filter_timesheet_date_range").on(
        "cancel.daterangepicker",
        function (ev, picker) {
            // Clear the input field and hidden fields
            $(this).val("");
            $("#filter_start_date").val("");
            $("#filter_end_date").val("");
            // Trigger a change event to refresh the table
            $("#time_sheet_report_table").bootstrapTable("refresh");
        }
    );
});
$(
    "#filter_project, #filter_user, #project-total-hours, #filter_timesheet_date_range"
).change(function () {
    $("#time_sheet_report_table").bootstrapTable("refresh");
});
$(function () {
    initSelect2Ajax(
        "#filter_project",
        "/master-panel/tasks/search-projects",
        label_select_project,
        true,
        0
    );

    initSelect2Ajax(
        "#filter_user",
        "/master-panel/users/search-users",
        label_select_user,
        true,
        0
    );
});
