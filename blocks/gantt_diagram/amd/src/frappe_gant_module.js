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
            let tasks=[];
            // Convert dates to ISO strings (if not already)
            tasks = data.map(task => ({
                ...task,
                start: new Date(task.start).toISOString().split('T')[0], // YYYY-MM-DD
                end: new Date(task.end).toISOString().split('T')[0]
            }));

            // Debug: Log tasks after date conversion
            console.log('Tasks with ISO dates:', tasks);

            if (typeof tasks[0]==='undefined'){
                let today = new Date();
                const dd = String(today.getDate()).padStart(2, '0');
                const mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
                const yyyy = today.getFullYear();

                today = `${yyyy}-${mm}-${dd}`;

                // Render the Gantt chart
                tasks = [
                    {
                        id: 'Task_1',
                        name: 'No activity defined yet',
                        start: '2021-10-01',
                        end: today,
                        progress: 0 // 50% complete
                    },];
               
            }
            

            // Render the Gantt chart
            const gantt = new Gantt(container, tasks, {
                view_mode: 'Month',
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

            //console.log('Diagrama de Gantt renderizado:', gantt);

            const getTaskElement = (taskId) => {
                return document.querySelector(`.bar-wrapper[data-id="${taskId}"]`);
            }
            

            // Function to center the view on the first task
            const centerOnFirstTask = (taskId) => {
                const taskElement = getTaskElement(taskId);
                if (!taskElement) {
                    console.warn(`Task with ID "${taskId}" not found.`);
                    return;
                }
                
                const ganttContainer = document.querySelector('.gantt-container');
                ganttContainer.scrollTo({ left: 0, behavior: 'auto' }); // Reset scroll to the far left
                const taskRect = taskElement.getBoundingClientRect();
                const containerRect = ganttContainer.getBoundingClientRect();

                

                // Calculate the scroll position to center the task
                const scrollPosition = taskRect.left - containerRect.left - (containerRect.width / 2) + (taskRect.width / 2);

                window.console.log(`taskRect.left: ${taskRect.left}, containerRect.left: ${containerRect.left}, containerRect.width: ${containerRect.width}, taskRect.left: ${taskRect.left}, scrollPosition: ${scrollPosition}`);

                // Scroll the Gantt container
                ganttContainer.scrollTo({
                    left: scrollPosition,
                    behavior: 'smooth'
                });

            };

            

            // Center on the first task initially
            centerOnFirstTask(tasks[0].id);

            // Add event listeners for view mode buttons
            document.getElementById('view-mode-day').addEventListener('click', function() {
                gantt.change_view_mode('Day');
                centerOnFirstTask(tasks[0].id); // Center on the first task after changing view mode
            });

            document.getElementById('view-mode-month').addEventListener('click', function() {
                gantt.change_view_mode('Month');
                centerOnFirstTask(tasks[0].id); // Center on the first task after changing view mode
            });

            document.getElementById('view-mode-year').addEventListener('click', function() {
                gantt.change_view_mode('Year');
                centerOnFirstTask(tasks[0].id); // Center on the first task after changing view mode
            });
        }
    };
});