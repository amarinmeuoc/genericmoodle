// block_graphical_events/chart_module.js
define(['core/toast','core/chartjs'], function(addToast,Chart) {


  const createChartResponsetime=(response)=>{
    //Grafica que muestra el número de tickets (abiertos, cerrados, erroneos y asignados) por dotación
    const ctx = document.getElementById('myLineChart');
              
    const type=(document.querySelector('#id_chart_responsetime_month').value===0)?'year':'month';
    let months=[];
    let days=[];
    if (type==='year')
      months = getCenteredMonthsCurrentYearLine(12);
    else {
      const year=document.querySelector('#id_chart_responsetime_year').value;
      const month=document.querySelector('#id_chart_responsetime_month').value;
      days=getDaysInMonth(year,month);
    }
      
    
    if (typeof response!=='undefined'){
       
      new Chart(ctx, {
        type: 'line',
        data: {
            labels: response.labels,
            datasets: response.datasets
        },
        options: {
            responsive: true,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Average Action Time (hours)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: type === 'month' ? 'Day of Month' : 'Month'
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + 
                                  (context.parsed.y !== null ? 
                                   context.parsed.y.toFixed(2) + ' hours' : 'No data');
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 20
                    }
                }
            }
        }
    });

      
    }
    
    
    }
    
    
    // Helper function for colors (or use predefined colors)
    function getRandomColor(opacity = 0.4) {
    const r = Math.floor(Math.random() * 255);
    const g = Math.floor(Math.random() * 255);
    const b = Math.floor(Math.random() * 255);
    return `rgba(${r}, ${g}, ${b}, ${opacity})`;
    }
    
    const getCenteredMonthsCurrentYearLine = (length) => {
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                   'July', 'August', 'September', 'October', 'November', 'December'];
    
    if (length < 1 || length > 12) {
      throw new Error('Length must be between 1 and 12');
    }
    
    const currentMonthIndex = new Date().getMonth(); // 0-11
    const halfLength = Math.floor(length / 2);
    
    // Calculate start index (clamped to avoid going out of bounds)
    let startIndex = currentMonthIndex - halfLength;
    
    // Adjust for even lengths (center slightly left-biased)
    if (length % 2 === 0) {
      startIndex += 1;
    }
    
    // Clamp to valid range (no negative or exceeding 12)
    startIndex = Math.max(0, startIndex);
    startIndex = Math.min(startIndex, 12 - length);
    
    // Return the sliced months
    return months.slice(startIndex, startIndex + length);
    }

    const getDaysInMonth=(year, month) =>{
      const date = new Date(year, month - 1, 1);
      const days = [];
      
      while (date.getMonth() === month - 1) {
          days.push(new Date(date)); // Push a new Date object
          date.setDate(date.getDate() + 1);
      }
      
      return days;
  }

  return {
      init: function(data) {
          createChartResponsetime(data);
          
      }
  };
});

