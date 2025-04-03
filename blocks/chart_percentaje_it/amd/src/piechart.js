define(['core/toast', 'core/chartjs'], function(addToast, Chart) {
    
        const createChartResponsetime = (response) => {
            const ctx = document.getElementById('myPieChart');
            
            // Destroy previous chart if it exists
            if (ctx.chart) {
                ctx.chart.destroy();
            }
            
            // Prepare data for chart
            const labels = Object.keys(response);
            const data = Object.values(response);
            const backgroundColors = [
                'rgba(255, 99, 132, 0.7)',
                'rgba(54, 162, 235, 0.7)',
                'rgba(75, 192, 192, 0.7)',
                'rgba(255, 206, 86, 0.7)'
            ];
            
            const chartData = {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: backgroundColors,
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 206, 86, 1)'
                    ],
                    borderWidth: 1
                }]
            };
            
            const options = {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${percentage}% (${value})`;

                            }
                        }
                    }
                }
            };
            
            // Create new chart
            ctx.chart = new Chart(ctx, {
                type: 'pie',
                data: chartData,
                options: options
            });
        };
    

    
    return {
        init: function (data){
            if (typeof data!=='undefined'){
                createChartResponsetime(data)
            } else
                return;
        }
    };
});


