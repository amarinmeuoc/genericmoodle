define(['core/toast', 'gantt'], function(addToast, Gantt) {
    console.log('Frappe Gantt library loaded:', Gantt); // Should log the Gantt class
    return {
        init: function(data) {
            // Verify that the container exists
            const container = document.getElementById('mgantt-container');
            if (!container || container.offsetWidth === 0) {
                console.error('Container not found or has zero width.');
                return;
            }

            // Convert dates to ISO strings (if not already)
            const tasks = data.map(task => ({
                ...task,
                start: new Date(task.start).toISOString().split('T')[0], // YYYY-MM-DD
                end: new Date(task.end).toISOString().split('T')[0]
            }));

            // Debug: Log tasks after date conversion
            console.log('Tasks with ISO dates:', tasks);

            // Render the Gantt chart
            const gantt = new Gantt(container, data, {
                on_click: function(task) {
                    if (!task) {
                        console.error('Task DOM element not found:', task);
                        return;
                    }
                    console.log('Task clicked:', task);
                },
                on_date_change: function(task, start, end) {
                    console.log('Date changed:', task, start, end);
                }
            });


            console.log('Diagrama de Gantt renderizado:', gantt);
        }
    };
});