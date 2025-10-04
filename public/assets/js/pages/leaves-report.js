$(function () {

    $('#leaves_report_table').on('load-success.bs.table', function (e, data) {
        $('#rejected-leaves').text(data.summary.formatted_rejected_leaves);
        $('#pending-leaves').text(data.summary.formatted_pending_leaves);
        $('#approved-leaves').text(data.summary.formatted_approved_leaves);
        $('#total-leaves').text((data.summary.formatted_total_leaves));
        $('#full-leaves').text((data.summary.total_full_leaves));
        $('#partial-leaves').text((data.summary.formatted_partial_leaves));
    });
});
$(document).ready(function () {
    $('#export_button').click(function () {
        var $exportButton = $(this);
        $exportButton.attr('disabled', true);
        // Prepare query parameters
        const queryParams = leaves_report_query_params({ offset: 0, limit: 1000, sort: 'id', order: 'desc', search: '' });
        // Construct the export URL
        const exportUrl = leaves_report_export_url + '?' + $.param(queryParams);
        // Open the export URL in a new tab or window
        $exportButton.attr('disabled', false);
        window.open(exportUrl, '_blank');
    });
    $('#filter_date_range').on('apply.daterangepicker', function (ev, picker) {
        $('#filter_date_range_from').val(picker.startDate.format('YYYY-MM-DD'));
        $('#filter_date_range_to').val(picker.endDate.format('YYYY-MM-DD'));
        $('#leaves_report_table').bootstrapTable('refresh');
    });
    $('#filter_date_range').on('cancel.daterangepicker', function (ev, picker) {
        // Clear the input field and hidden fields
        $(this).val('');
        // Clear the hidden inputs
        $('#filter_date_range_from').val('');
        $('#filter_date_range_to').val('');
        picker.setStartDate(moment());
        picker.setEndDate(moment());
        picker.updateElement();
        $('#leaves_report_table').bootstrapTable('refresh');
    });


    $('#report_start_date_between').on('apply.daterangepicker', function (ev, picker) {
        var startDate = picker.startDate.format('YYYY-MM-DD');
        var endDate = picker.endDate.format('YYYY-MM-DD');

        $('#filter_start_date_from').val(startDate);
        $('#filter_start_date_to').val(endDate);

        $('#leaves_report_table').bootstrapTable('refresh');
    });

    $('#report_start_date_between').on('cancel.daterangepicker', function (ev, picker) {
        $('#filter_start_date_from').val('');
        $('#filter_start_date_to').val('');
        $('#report_start_date_between').val('');
        picker.setStartDate(moment());
        picker.setEndDate(moment());
        picker.updateElement();
        $('#leaves_report_table').bootstrapTable('refresh');
    });

    $('#report_end_date_between').on('apply.daterangepicker', function (ev, picker) {
        var startDate = picker.startDate.format('YYYY-MM-DD');
        var endDate = picker.endDate.format('YYYY-MM-DD');

        $('#filter_end_date_from').val(startDate);
        $('#filter_end_date_to').val(endDate);

        $('#leaves_report_table').bootstrapTable('refresh');
    });
    $('#report_end_date_between').on('cancel.daterangepicker', function (ev, picker) {
        $('#filter_end_date_from').val('');
        $('#filter_end_date_to').val('');
        $('#report_end_date_between').val('');
        picker.setStartDate(moment());
        picker.setEndDate(moment());
        picker.updateElement();
        $('#leaves_report_table').bootstrapTable('refresh');
    });
});
function leaves_report_query_params(p) {
    return {
        user_ids: $('#user_filter').val(),
        statuses: $('#status_filter').val(),
        date_between_from: $('#filter_date_range_from').val(),
        date_between_to: $('#filter_date_range_to').val(),
        start_date_from: $('#filter_start_date_from').val(),
        start_date_to: $('#filter_start_date_to').val(),
        end_date_from: $('#filter_end_date_from').val(),
        end_date_to: $('#filter_end_date_to').val(),
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}
addDebouncedEventListener('#user_filter, #status_filter', 'change', function (e, refreshTable) {
    e.preventDefault();
    if (typeof refreshTable === 'undefined' || refreshTable) {
        $('#leaves_report_table').bootstrapTable('refresh');
    }
});


$(document).on('click', '.clear-report-filters', function (e) {
    e.preventDefault();
    $('#filter_date_range').val('');
    $('#filter_date_range_from').val('');
    $('#filter_date_range_to').val('');
    $('#report_start_date_between').val('');
    $('#filter_start_date_from').val('');
    $('#filter_start_date_to').val('');
    $('#report_end_date_between').val('');
    $('#filter_end_date_from').val('');
    $('#filter_end_date_to').val('');
    $('#user_filter').val('').trigger('change', [0]);
    $('#status_filter').val('').trigger('change', [0]);
    $('#leaves_report_table').bootstrapTable('refresh');
})
// Function to format the Total Leaves column
function formatTotalLeaves(value, row, index) {
    return formatLeaveDuration(row.total_leaves, row.total_days, row.total_hours);
}

// Function to format the Partial Leaves column
function formatPartialLeaves(value, row, index) {
    return formatLeaveDuration(row.partial_leaves, '', row.total_hours);
}

// Function to format the Approved Leaves column
function formatApprovedLeaves(value, row, index) {
    return formatLeaveDuration(row.approved_leaves, row.approved_days, row.approved_hours);
}

// Function to format the Pending Leaves column
function formatPendingLeaves(value, row, index) {
    return formatLeaveDuration(row.pending_leaves, row.pending_days, row.pending_hours);
}

// Function to format the Rejected Leaves column
function formatRejectedLeaves(value, row, index) {
    return formatLeaveDuration(row.rejected_leaves, row.rejected_days, row.rejected_hours);
}

// General function to format leave duration in a "X Days and Y Hours" format
function formatLeaveDuration(totalLeaves, days, hours) {
    const dayLabel = 'Day';
    const daysLabel = 'Days';
    const hourLabel = 'Hour';
    const hoursLabel = 'Hours';

    // If there are no days or hours, return just the total leaves
    if (days === 0 && hours === 0) {
        return `${totalLeaves}`;
    }

    // Initialize the formatted string
    let formatted = `${totalLeaves}`;

    // Array to hold the duration strings
    let leaveDuration = [];

    // If there are days, format and add them
    if (days > 0) {
        leaveDuration.push(`${days} ${days > 1 ? daysLabel : dayLabel}`);
    }

    // If there are hours, format and add them
    if (hours > 0) {
        leaveDuration.push(`${hours} ${hours > 1 ? hoursLabel : hourLabel}`);
    }

    // If we have any leave duration to display, append it inside parentheses
    if (leaveDuration.length > 0) {
        formatted += ` (${leaveDuration.join(' and ')})`;
    }

    return formatted;
}


