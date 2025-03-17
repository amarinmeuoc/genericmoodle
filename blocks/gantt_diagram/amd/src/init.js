define([
    'core/templates',
    'block_gantt_diagram/shared_library',
], function(Templates, shared) {

    const url = M.cfg.wwwroot + '/webservice/rest/server.php';

    const reloadTemplate = (data) => {
        const templateData = {
            jsondata: JSON.stringify(data), // Serialize data to JSON
        };
    
        console.log('templateData:', templateData); // Debugging
    
        Templates.renderForPromise('block_gantt_diagram/canvas', templateData)
            .then(({ html, js }) => {
                const content = document.querySelector('#gantt_layer');
                content.innerHTML = '';
                Templates.appendNodeContents(content, html, js);
    
                // After the template is rendered, initialize the Gantt chart
                initializeGantt(data);
            })
            .catch((error) => {
                console.error('Error rendering template:', error);
            });
    };

    const validateTasks = (tasks) => {
        return tasks.every(task => {
            // Check if start and end dates are present and valid
            const startDate = new Date(task.start);
            const endDate = new Date(task.end);
            return task.start && task.end && !isNaN(startDate) && !isNaN(endDate);
        });
    };
    
    
    const initializeGantt = (data) => {
        if (!validateTasks(data)) {
            console.error('Invalid task data: Missing or invalid start/end dates.');
            return;
        }
    
        require.config({
            paths: {
                gantt: '/blocks/gantt_diagram/js/frappe-gantt',
            },
            shim: {
                gantt: { exports: 'gantt' },
            }
        });
    
        requestAnimationFrame(()=>{
            require(['block_gantt_diagram/frappe_gant_module', 'chart'], function(module, chart) {
                module.init(data); // Pass the data to the module's init function
            });
        })
        
    };
    

    const init = () => {
        shared.areElementsLoaded('#mgantt-container').then((elements) => {
            console.log("TODO: GANTT START");

            // Example data for the Gantt chart
            const ganttData = [
                {
                    id: 'Task_1',
                    name: 'Task 1',
                    start: '2023-10-01',
                    end: '2023-10-05',
                    progress: 0.5 // 50% complete
                },
                {
                    id: 'Task_2',
                    name: 'Task 2',
                    start: '2023-10-06',
                    end: '2023-10-10',
                    progress: 0.2, // 20% complete
                    dependencies: ['Task_1'] // Task 2 depends on Task 1
                },
                {
                    id: 'Task_3',
                    name: 'Task 3',
                    start: '2023-10-11',
                    end: '2023-10-15',
                    progress: 0, // 0% complete
                    dependencies: ['Task_2'] // Task 3 depends on Task 2
                }
            ];
            reloadTemplate(ganttData);
        });
    };

    return {
        init: init
    };
});