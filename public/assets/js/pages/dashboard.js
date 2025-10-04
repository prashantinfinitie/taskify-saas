/**
 * Dashboard Analytics
 */
'use strict';

(function () {
    let cardColor, headingColor, axisColor, shadeColor, borderColor;

    cardColor = config.colors.white;
    headingColor = config.colors.headingColor;
    axisColor = config.colors.axisColor;
    borderColor = config.colors.borderColor;
    // Custom pastel colors for the charts
    const pastelColors = ["#64C7CC", "#A0D995", "#FFB677", "#D4A5FF"]; // Light cool colors
    const pastelSuccess = "#63ED7A"; // Light green for success
    const pastelDanger = "#FC544B"; // Light red for danger
    // Projects Statistics Chart
    // Function to calculate percentage
    function calculatePercentage(data) {
        const total = data.reduce((a, b) => a + b, 0);
        return data.map((value) => ((value / total) * 100).toFixed(2) + "%");
    }

    var projectOptions = {
        series: project_data, // Dynamic project data
        colors: bg_colors, // Dynamic colors
        labels: labels, // Dynamic labels
        chart: {
            type: "donut",
            height: 200, // Compact height
        },
        plotOptions: {
            pie: {
                donut: {
                    size: "85%", // Smaller donut thickness
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: "Total",
                            fontSize: "16px",
                            fontWeight: 500,
                            formatter: function () {
                                return project_data.reduce((a, b) => a + b, 0); // Dynamic total sum
                            },
                        },
                    },
                },
            },
        },
        dataLabels: {
            enabled: false, // Disable external labels for cleaner design
        },
        responsive: [
            {
                breakpoint: 480,
                options: {
                    chart: {
                        width: 180,
                    },
                    legend: {
                        position: "bottom",
                        fontSize: "12px",
                    },
                },
            },
        ],
        legend: {
            position: "right",
            fontSize: "14px",
            markers: {
                radius: 12,
            },
        },
        tooltip: {
            y: {
                formatter: function (val, { seriesIndex }) {
                    const percentage =
                        calculatePercentage(project_data)[seriesIndex];
                    return `${val} (${percentage})`; // Show value and percentage in tooltip
                },
            },
        },
    };

    var projectChart = new ApexCharts(
        document.querySelector("#projectStatisticsChart"),
        projectOptions
    );
    projectChart.render();

    // Tasks Statistics Chart
    var taskOptions = {
        series: task_data, // Dynamic task data
        colors: bg_colors, // Dynamic colors
        labels: labels, // Dynamic labels
        chart: {
            type: "donut",
            height: 200,
        },
        plotOptions: {
            pie: {
                donut: {
                    size: "85%", // Smaller donut thickness
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: "Total",
                            fontSize: "16px",
                            fontWeight: 500,
                            formatter: function () {
                                return task_data.reduce((a, b) => a + b, 0); // Dynamic total sum
                            },
                        },
                    },
                },
            },
        },
        dataLabels: {
            enabled: false,
        },
        responsive: [
            {
                breakpoint: 480,
                options: {
                    chart: {
                        width: 180,
                    },
                },
            },
        ],
        tooltip: {
            y: {
                formatter: function (val, { seriesIndex }) {
                    const percentage =
                        calculatePercentage(task_data)[seriesIndex];
                    return `${val} (${percentage})`; // Show value and percentage in tooltip
                },
            },
        },
    };

    var taskChart = new ApexCharts(
        document.querySelector("#taskStatisticsChart"),
        taskOptions
    );
    taskChart.render();

    // Todos Statistics Chart
    var todoOptions = {
        series: todo_data, // Dynamic todo data
        colors: [pastelSuccess, pastelDanger], // Light success and danger colors
        labels: [done, pending], // Dynamic labels for done/pending
        chart: {
            type: "donut",
            height: 200,
        },
        plotOptions: {
            pie: {
                donut: {
                    size: "85%", // Smaller donut thickness
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: "Total",
                            fontSize: "16px",
                            fontWeight: 500,
                            formatter: function () {
                                return todo_data.reduce((a, b) => a + b, 0); // Dynamic total sum
                            },
                        },
                    },
                },
            },
        },
        dataLabels: {
            enabled: false,
        },
        responsive: [
            {
                breakpoint: 480,
                options: {
                    chart: {
                        width: 180,
                    },
                },
            },
        ],
        tooltip: {
            y: {
                formatter: function (val, { seriesIndex }) {
                    const percentage =
                        calculatePercentage(todo_data)[seriesIndex];
                    return `${val} (${percentage})`; // Show value and percentage in tooltip
                },
            },
        },
    };

    var todoChart = new ApexCharts(
        document.querySelector("#todoStatisticsChart"),
        todoOptions
    );
    todoChart.render();
})();

window.icons = {
    refresh: 'bx-refresh',
    toggleOn: 'bx-toggle-right',
    toggleOff: 'bx-toggle-left'
}

function loadingTemplate(message) {
    return '<i class="bx bx-loader-alt bx-spin bx-flip-vertical" ></i>'
}


function queryParamsUpcomingBirthdays(p) {
    return {
        "upcoming_days": $('#upcoming_days_bd').val(),
        "user_id": $('#birthday_user_filter').val(),
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

$('#upcoming_days_birthday_filter').on('click', function (e) {
    e.preventDefault();
    $('#birthdays_table').bootstrapTable('refresh');


})

$('#birthday_user_filter').on('change', function (e) {
    e.preventDefault();
    $('#birthdays_table').bootstrapTable('refresh');


})

function queryParamsUpcomingWa(p) {
    return {
        "upcoming_days": $('#upcoming_days_wa').val(),
        "user_id": $('#wa_user_filter').val(),
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

$('#upcoming_days_wa_filter').on('click', function (e) {
    e.preventDefault();
    $('#wa_table').bootstrapTable('refresh');


})

$('#wa_user_filter').on('change', function (e) {
    e.preventDefault();
    $('#wa_table').bootstrapTable('refresh');

})

function queryParamsMol(p) {
    return {
        "upcoming_days": $('#upcoming_days_mol').val(),
        "user_id": $('#mol_user_filter').val(),
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

$('#upcoming_days_mol_filter').on('click', function (e) {
    e.preventDefault();
    $('#mol_table').bootstrapTable('refresh');

})

$('#mol_user_filter').on('change', function (e) {
    e.preventDefault();
    $('#mol_table').bootstrapTable('refresh');

})

document.addEventListener("DOMContentLoaded", function () {
    let incomeExpenseChart = null; // Initialize the chart variable

    function getFilters() {
        // Get the values from hidden inputs
        var startDate = $("#filter_date_range_from").val();
        var endDate = $("#filter_date_range_to").val();

        // Check if the input values are not empty
        if (startDate && endDate) {
            return {
                start_date: startDate,
                end_date: endDate,
            };
        }

        // If dates are not set or input is empty, return null
        return {
            start_date: null,
            end_date: null,
        };
    }

    function parseCurrencyValue(currencyString) {
        // Remove currency symbol and commas, then convert to float
        return parseFloat(currencyString.replace(/[^0-9.-]+/g, ""));
    }

    function groupByDate(data, type) {
        const grouped = {};

        data.forEach((item) => {
            const date =
                type === "invoice" ? item.from_date : item.expense_date;
            const amount = parseCurrencyValue(item.amount);

            if (!grouped[date]) {
                grouped[date] = 0;
            }
            grouped[date] += amount;
        });

        return grouped;
    }

    function transformData(response) {
        // Group invoices and expenses by date
        const invoicesByDate = groupByDate(response.invoices, "invoice");
        const expensesByDate = groupByDate(response.expenses, "expense");

        // Get all unique dates
        const allDates = [
            ...new Set([
                ...Object.keys(invoicesByDate),
                ...Object.keys(expensesByDate),
            ]),
        ].sort();

        // Prepare series data
        const categories = [];
        const incomeData = [];
        const expenseData = [];

        allDates.forEach((date) => {
            categories.push(date);
            incomeData.push(invoicesByDate[date] || 0);
            expenseData.push((expensesByDate[date] || 0)); // Make expenses negative
        });

        return {
            categories,
            incomeData,
            expenseData,
        };
    }

    function updateIEChart() {
        $.ajax({
            type: "GET",
            url: "/master-panel/reports/income-vs-expense-report-data",
            dataType: "JSON",
            data: getFilters(),
            success: function (response) {
                const chartData = transformData(response);

                const options = {
                    series: [
                        {
                            name: "Income",
                            data: chartData.incomeData,
                        },
                        {
                            name: "Expenses",
                            data: chartData.expenseData,
                        },
                    ],
                    chart: {
                        height: 380,
                        type: "area",
                        stacked: false,
                        toolbar: {
                            show: false,
                        },
                    },
                    dataLabels: {
                        enabled: false,
                    },
                    stroke: {
                        curve: "smooth",
                        width: 2,
                    },
                    fill: {
                        type: "gradient",
                        gradient: {
                            opacityFrom: 0.6,
                            opacityTo: 0.1,
                        },
                    },
                    colors: ["#22c55e", "#ef4444"], // Green for income, Red for expenses
                    xaxis: {
                        categories: chartData.categories,
                        labels: {
                            rotate: -45,
                            style: {
                                colors: "#64748b",
                                fontSize: "12px",
                            },
                        },
                        axisBorder: {
                            show: false,
                        },
                        axisTicks: {
                            show: false,
                        },
                    },
                    yaxis: {
                        labels: {
                            formatter: function (val) {
                                return "$ " + Math.abs(val).toLocaleString();
                            },
                            style: {
                                colors: "#64748b",
                                fontSize: "12px",
                            },
                        },
                    },
                    grid: {
                        borderColor: "#e2e8f0",
                        strokeDashArray: 4,
                        xaxis: {
                            lines: {
                                show: true,
                            },
                        },
                        yaxis: {
                            lines: {
                                show: true,
                            },
                        },
                    },
                    tooltip: {
                        shared: true,
                        intersect: false,
                        y: {
                            formatter: function (value) {
                                return "$ " + Math.abs(value).toLocaleString();
                            },
                        },
                    },
                    legend: {
                        position: "top",
                        horizontalAlign: "right",
                        fontSize: "14px",
                        markers: {
                            radius: 12,
                        },
                    },
                };

                if (incomeExpenseChart) {
                    incomeExpenseChart.updateOptions(options);
                } else {
                    incomeExpenseChart = new ApexCharts(
                        document.querySelector("#income-expense-chart"),
                        options
                    );
                    incomeExpenseChart.render();
                }
            },
            error: function (xhr, status, error) {
                console.error("Chart Error:", error);
            },
        });
    }

    $("#filter_date_range_income_expense").on("apply.daterangepicker", function (ev, picker) {
        // Set the values in hidden inputs
        $("#filter_date_range_from").val(picker.startDate.format("YYYY-MM-DD"));
        $("#filter_date_range_to").val(picker.endDate.format("YYYY-MM-DD"));
        updateIEChart(); // Update report when dates are applied
    });

    $("#filter_date_range_income_expense").on("cancel.daterangepicker", function (ev, picker) {
        $(this).val("");
        // Clear the hidden inputs
        $("#filter_date_range_from").val("");
        $("#filter_date_range_to").val("");
        picker.setStartDate(moment());
        picker.setEndDate(moment());
        picker.updateElement();
        updateIEChart(); // Update report when dates are cleared
    });

    // Initial chart update
    updateIEChart();
});

$(document).ready(function () {
    if (typeof moment === 'undefined') {
        console.error("Moment.js is NOT loaded!");
        return;
    }

    $('#filter_date_range_income_expense').daterangepicker({
        width: '100%',
        "alwaysShowCalendars": true,
        startDate: moment().startOf('month'),
        endDate: moment().endOf('month'),
        locale: {
            format: 'YYYY-MM-DD',
            cancelLabel: 'Clear'
        },
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });
});

