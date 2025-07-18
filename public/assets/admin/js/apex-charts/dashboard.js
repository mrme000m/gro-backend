$(document).ready(function() {
    // Safe Chart.js initialization - prevent errors
    console.log('ApexCharts dashboard loaded');

    // Initialize Chart.js charts only if elements exist and HSCore is available
    if (typeof $.HSCore !== 'undefined' && $.HSCore.components && $.HSCore.components.HSChartJS) {
        $('.js-chart').each(function () {
            if ($(this).length > 0 && $(this).is('canvas')) {
                try {
                    $.HSCore.components.HSChartJS.init($(this));
                } catch (e) {
                    console.warn('Failed to initialize chart:', e);
                }
            }
        });

        // Check if updatingData element exists and has canvas before initializing
        var updatingChart = null;
        if ($('#updatingData').length > 0 && $('#updatingData canvas').length > 0) {
            try {
                updatingChart = $.HSCore.components.HSChartJS.init($('#updatingData'));
            } catch (e) {
                console.warn('Failed to initialize updating chart:', e);
            }
        }
    } else {
        console.warn('HSCore Chart.js components not available - using ApexCharts only');
        var updatingChart = null;
    }

    $('[data-toggle="chart-bar"]').click(function (e) {
        // Only proceed if updatingChart was successfully initialized
        if (!updatingChart) {
            console.warn('Chart not initialized, skipping update');
            return;
        }

        let keyDataset = $(e.currentTarget).attr('data-datasets')

        if (keyDataset === 'lastWeek') {
            updatingChart.data.labels = ["Apr 22", "Apr 23", "Apr 24", "Apr 25", "Apr 26", "Apr 27", "Apr 28", "Apr 29", "Apr 30", "Apr 31"];
            updatingChart.data.datasets = [
                {
                    "data": [120, 250, 300, 200, 300, 290, 350, 100, 125, 320],
                    "backgroundColor": "#377dff",
                    "hoverBackgroundColor": "#377dff",
                    "borderColor": "#377dff"
                },
                {
                    "data": [250, 130, 322, 144, 129, 300, 260, 120, 260, 245, 110],
                    "backgroundColor": "#e7eaf3",
                    "borderColor": "#e7eaf3"
                }
            ];
            updatingChart.update();
        } else {
            updatingChart.data.labels = ["May 1", "May 2", "May 3", "May 4", "May 5", "May 6", "May 7", "May 8", "May 9", "May 10"];
            updatingChart.data.datasets = [
                {
                    "data": [200, 300, 290, 350, 150, 350, 300, 100, 125, 220],
                    "backgroundColor": "#377dff",
                    "hoverBackgroundColor": "#377dff",
                    "borderColor": "#377dff"
                },
                {
                    "data": [150, 230, 382, 204, 169, 290, 300, 100, 300, 225, 120],
                    "backgroundColor": "#e7eaf3",
                    "borderColor": "#e7eaf3"
                }
            ]
            updatingChart.update();
        }
    })

    $('.js-chart-datalabels').each(function () {
        // Safe initialization for chart data labels
        if (typeof $.HSCore !== 'undefined' && $.HSCore.components && $.HSCore.components.HSChartJS && $(this).is('canvas')) {
            try {
                $.HSCore.components.HSChartJS.init($(this), {
            plugins: [ChartDataLabels],
            options: {
                plugins: {
                    datalabels: {
                        anchor: function (context) {
                            var value = context.dataset.data[context.dataIndex];
                            return value.r < 20 ? 'end' : 'center';
                        },
                        align: function (context) {
                            var value = context.dataset.data[context.dataIndex];
                            return value.r < 20 ? 'end' : 'center';
                        },
                        color: function (context) {
                            var value = context.dataset.data[context.dataIndex];
                            return value.r < 20 ? context.dataset.backgroundColor : context.dataset.color;
                        },
                        font: function (context) {
                            var value = context.dataset.data[context.dataIndex],
                                fontSize = 25;

                            if (value.r > 50) {
                                fontSize = 35;
                            }

                            if (value.r > 70) {
                                fontSize = 55;
                            }

                            return {
                                weight: 'lighter',
                                size: fontSize
                            };
                        },
                        offset: 2,
                        padding: 0
                    }
                }
            },
        });
            } catch (e) {
                console.warn('Failed to initialize chart with data labels:', e);
            }
        } else {
            console.warn('Chart data labels element found but Chart.js not available or element is not canvas');
        }
    });
});
