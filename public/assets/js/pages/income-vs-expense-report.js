document.addEventListener('DOMContentLoaded', function () {
    // Define default start and end dates (example: last 30 days)
    var start = moment().subtract(29, 'days');
    var end = moment();

    // Initialize the date range picker with default dates
    $('#income_expense_filter_date_range').daterangepicker({
        alwaysShowCalendars: true,
        showCustomRangeLabel: true,
        singleDatePicker: false,
        showDropdowns: true,
        autoUpdateInput: true, // Important: this updates the input automatically
        startDate: start,
        endDate: end,
        locale: {
            cancelLabel: "Clear",
            format: js_date_format, // Your custom format
        },
    });

    // Set the input value on initialization
    $('#income_expense_filter_date_range').val(start.format(js_date_format) + ' - ' + end.format(js_date_format));

    // Function to get current filters
    function getFilters() {
        return {
            start_date: $('#income_expense_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD'),
            end_date: $('#income_expense_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD'),
        };
    }

    // Function to fetch and update the report data
    function updateReport() {
        $.ajax({
            url: '/master-panel/reports/income-vs-expense-report-data',
            method: 'GET',
            data: getFilters(),
            success: function (data) {
                $('#total_income').text(data.total_income || '0');
                $('#total_expenses').text(data.total_expenses || '0');
                $('#profit_or_loss').text(data.profit_or_loss || '0');

                // Update invoice details
                var invoicesHtml = '';
                if (data.invoices.length > 0) {
                    data.invoices.forEach(function (invoice) {
                        invoicesHtml += `
                        <tr>
                            <td><a href="${invoice.view_route}">${invoice.id}</a></td>
                            <td>${invoice.from_date} - ${invoice.to_date}</td>
                            <td>${invoice.amount}</td>
                        </tr>
                    `;
                    });
                } else {
                    invoicesHtml = `
                    <tr>
                        <td colspan="3" class="text-center">No data available</td>
                    </tr>
                `;
                }
                $('#invoices_table tbody').html(invoicesHtml);

                // Update expense details
                var expensesHtml = '';
                if (data.expenses.length > 0) {
                    data.expenses.forEach(function (expense) {
                        expensesHtml += `
                        <tr>
                            <td>${expense.id}</td>
                            <td>${expense.title}</td>
                            <td>${expense.amount}</td>
                            <td>${expense.expense_date}</td>
                        </tr>
                    `;
                    });
                } else {
                    expensesHtml = `
                    <tr>
                        <td colspan="4" class="text-center">No data available</td>
                    </tr>
                `;
                }
                $('#expenses_table tbody').html(expensesHtml);
            },
            error: function () {
                alert('Error fetching report data.');
            }
        });
    }

    // Update report when date range is applied
    $('#income_expense_filter_date_range').on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format(js_date_format) + ' - ' + picker.endDate.format(js_date_format));
        updateReport();
    });

    // Initialize report with default filters
    updateReport();
});

// Export button click
$('#export_button').on('click', function () {
    var startDate = $('#income_expense_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
    var endDate = $('#income_expense_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');

    var exportUrl = `${export_income_vs_expense_url}?start_date=${startDate}&end_date=${endDate}`;
    window.open(exportUrl, '_blank');
});
