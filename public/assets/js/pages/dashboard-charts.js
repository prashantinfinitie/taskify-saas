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



$(document).ready(function () {
    $.ajax({
        url: customerMonthlyCountUrl,
        type: 'GET',
        dataType: 'json',
        success: function (response) {


            var options = {
                chart: {
                    type: 'bar',
                    height: 300,
                    width: '100%',
                    foreColor: '#0d1321',
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350
                        }
                    }
                },
                series: [{
                    name: 'Customers',
                    data: response.customerCounts
                }],
                xaxis: {
                    categories: response.months
                },
                yaxis: {
                    title: {
                        text: 'Number of Customers'
                    }
                },
                colors: ['#1B1A55'],
                dataLabels: {
                    enabled: false
                },
                grid: {
                    borderColor: '#f1f1f1',
                },
                tooltip: {
                    theme: 'light'
                }
            };

            var chart = new ApexCharts(document.querySelector("#customerChart"), options);
            chart.render();
        },
        error: function (xhr, status, error) {
            console.error(xhr.responseText);
        }
    });
});

$(document).ready(function () {
    // Make an AJAX request to fetch revenue data
    $.ajax({
        url: revenueDataUrl, // Replace this with the actual endpoint URL
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            console.log(response);

            // Map the response data to the format expected by ApexCharts
            var revenueData = response.revenueData.map(function (item) {
                return [item.timestamp, parseFloat(item.amount)];
            });

            // Calculate total revenue
            var totalRevenue = response.revenueData.reduce(function (acc, item) {
                return acc + parseFloat(item.amount);
            }, 0);

            // Update the total revenue display
            $('#totalRevenue').text(totalRevenue.toFixed(response.decimal_point));

            // Update the chart options with the fetched revenue data
            var options = {
                series: [{
                    data: revenueData // Revenue data received from the backend
                }],
                chart: {
                    id: 'revenueChart',
                    type: 'area',
                    height: 330,
                    zoom: {
                        autoScaleYaxis: true
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                markers: {
                    size: 0,
                    style: 'hollow',
                },
                xaxis: {
                    type: 'datetime',
                    tickAmount: 6,
                },
                tooltip: {
                    x: {
                        format: 'dd MMM yyyy'
                    }
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.9,
                        stops: [0, 100]
                    }
                },
                colors: ['#1B1A55']
            };

            // Render the chart with updated options
            var chart = new ApexCharts(document.querySelector("#revenueChart"), options);
            chart.render();
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
});




$(document).ready(function () {
    $.ajax({
        url: subscriptionRateUrl, // Replace with your actual route name
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            const planNames = Object.keys(response.chartData);

            // Create the chart options
            const options = {
                series: planNames.map(planName => ({
                    name: planName,
                    data: response.chartData[planName]
                })),
                chart: {
                    type: 'line',
                    height: 300,
                    zoom: {
                        enabled: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth'
                },
                xaxis: {
                    type: 'category'
                },
                yaxis: {
                    title: {
                        text: 'Charging Price'
                    }
                },
                // colors: ['#070F2B', '#FBA834', "#535C91", "#50C4ED", "#9290C3", "#333A73"],
            };

            var chart = new ApexCharts(document.getElementById('subscriptionRateChart'), options);
            chart.render();
        },
        error: function (error) {
            console.error('Error fetching chart data:', error);
        }
    });
});



$(document).ready(function () {
    $.ajax({
        url: getActiveSubscriptionPerPlanUrl,
        type: 'GET',
        dataType: 'json',
        success: function (subscriptionCountPerPlan) {
            // Create the chart options
            const options = {
                series: Object.values(subscriptionCountPerPlan),
                chart: {
                    type: 'donut',
                    height: 300
                },
                labels: Object.keys(subscriptionCountPerPlan),
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                        legend: {
                            position: 'bottom'
                        },
                        plotOptions: {
                            donut: {
                                customScale: 1,

                            },
                        },
                    }
                }],
            };

            // Create the chart instance
            const chart = new ApexCharts(document.querySelector("#planSalesChart"), options);
            chart.render();
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
});
