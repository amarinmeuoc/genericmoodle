// block_graphical_events/chart_module.js
define(['core/toast','chart'], function(addToast,Chart) {
    return {
        init: function(data) {
            const ctx = document.getElementById('myChart');

            if (typeof data !== 'undefined'){
                if (data.errorcode=='Group not found'){
                    addToast.add(`No group has been defined yet.`);
                }else {
                    // Extract data from the web service response
                    const ticketData = data[0]; // Assuming the response is an array with one object
                    const labels = ['Open', 'Cancelled', 'Closed', 'Assigned'];
                    const datasetData = [
                        ticketData.totaltickets_open,
                        ticketData.totaltickets_cancelled,
                        ticketData.totaltickets_closed,
                        ticketData.totaltickets_assigned,
                    ];

                    // Create the chart
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: '# of Tickets',
                                data: datasetData,
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.2)',
                                    'rgba(54, 162, 235, 0.2)',
                                    'rgba(75, 192, 192, 0.2)',
                                    'rgba(153, 102, 255, 0.2)',
                                ],
                                borderColor: [
                                    'rgba(255, 99, 132, 1)',
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(75, 192, 192, 1)',
                                    'rgba(153, 102, 255, 1)',
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
            }

        }
    };
});