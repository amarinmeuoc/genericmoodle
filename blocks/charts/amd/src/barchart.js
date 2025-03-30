// block_graphical_events/chart_module.js
define(['core/toast','core/chartjs'], function(addToast,Chart) {

  const createFirstBarChart=(rawData)=>{
    //Grafica que muestra el número de tickets (abiertos, cerrados, erroneos y asignados) por dotación
    const ctx = document.getElementById('myBarChartTickets');
              
    const labels = getCenteredMonthsCurrentYear(12);
  
    if (typeof rawData!=='undefined'){
       // 1. Extract unique GROUPS (for x-axis labels)
      const groups = [...new Set(rawData.map(item => item.label))]; 
      // Result: ['C1', 'C2', 'SRF']
  
      // 2. Extract unique CATEGORIES (for stacks)
      const categories = [...new Set(rawData.map(item => item.stack))]; 
      // Result: ['CATEGORIA1', 'CATEGORIA2']
  
      // 3. Build datasets (one per CATEGORY)
      const datasets = categories.map(category => {
        // For each category, get counts per group
        const data = groups.map(group => {
          const item = rawData.find(d => d.label === group && d.stack === category);
          return item ? item.dataSet : 0; // Default to 0 if no data
        });
  
        return {
          label: category, // Category name (for legend)
          data: data,      // Counts per group
          backgroundColor: getRandomColor(), // Assign colors
          stack: 'stack1'  // All bars share the same stack ID (for grouping)
        };
      });
  
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: groups, // ['C1', 'C2', 'SRF'] (x-axis)
          datasets: datasets // Processed datasets
        },
        options: {
          scales: {
            x: { stacked: true }, // Group bars by x-axis label
            y: { 
              stacked: true,      // Stack segments vertically
              beginAtZero: true
            }
          },
          plugins: {
            legend: { 
              position: 'top',
              labels: { usePointStyle: true }
            },
            title: {
              display: true,
              text: 'Tickets by Group and Category'
            }
          }
        }
      });
    }
  }

  // Helper function for colors (or use predefined colors)
  const getRandomColor = (opacity = 0.4) => {
    const r = Math.floor(Math.random() * 255);
    const g = Math.floor(Math.random() * 255);
    const b = Math.floor(Math.random() * 255);
    return `rgba(${r}, ${g}, ${b}, ${opacity})`;
  }

  const getCenteredMonthsCurrentYear = (length) => {
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

    return {
        init: function(data) {
            createFirstBarChart(data);
            
        }
    };
});




