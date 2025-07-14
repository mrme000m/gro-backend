$(document).ready(function() {
    // Safe dashboard initialization - no Chart.js errors
    console.log('Safe dashboard loaded');
    
    // Initialize updatingChart as null since this dashboard uses ApexCharts
    var updatingChart = null;

    // Safe chart toggle handler that doesn't break if chart is null
    $('[data-toggle="chart-bar"]').click(function (e) {
        console.log('Chart toggle clicked, but Chart.js is disabled for compatibility');
        return false;
    });

    // Safe chart data labels handler
    $('.js-chart-datalabels').each(function () {
        console.log('Chart data labels element found, but Chart.js is disabled');
    });
});
