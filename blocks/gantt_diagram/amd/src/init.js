define([
    'core/templates',
    'block_gantt_diagram/shared_library',
], function(Templates, shared) {


    const reloadgroups=(customer,token,url)=>{
        return new Promise((resolve, reject) => {
            let xhr = new XMLHttpRequest();
        
            //Se prepara el objeto a enviar
            const formData= new FormData();
            formData.append('wstoken',token);
            formData.append('wsfunction', 'block_itp_load_groups');
            formData.append('moodlewsrestformat', 'json');
            formData.append('params[0][customerid]',customer);
            
            xhr.open('POST',url,true);
            xhr.send(formData);
    
            xhr.onload = (ev)=> {
                if (xhr.status === 200) {
                    reqHandlerCustomerChangeEvent(xhr);
                    resolve(); // Resuelve la promesa cuando la solicitud ha terminado correctamente
                } else {
                    rejectAnswer(xhr);
                    reject('Error al cargar la información'); // Rechaza la promesa en caso de error
                }
            }
    
            xhr.onerror = ()=> {
                rejectAnswer(xhr);
                reject('Error de red al cargar la información'); // Rechaza la promesa en caso de error de red
            }
        });
    }
    
    const reloadGantt=(xhr)=>{
        if(xhr.status == 200){
            let response = JSON.parse(xhr.responseText);
            window.console.log(response);
        }
    }
    
    const reqHandlerCustomerChangeEvent=(xhr)=>{
        if(xhr.status == 200){
            let response = JSON.parse(xhr.responseText);
            /*
            Retorna la lista de usuarios asociados al grupo.
            Ejemplo de retorno:
            response=[
                {id: 1, name: 'C1'},
                {id: 2, name: 'C2'}
            ]
            */
            const selGroup=document.querySelector('#id_group');
            selGroup.innerHTML = '';
    
            if (response && response.length>0){
                //Se añade la opción de mostrar el gantt para todos los grupos
                const option = document.createElement('option');
                option.value = 0;
                option.textContent = "All groups";
                selGroup.appendChild(option);
    
                //Actualiza selGroup con los valores devueltos por response
                response.forEach((group) => {
                    const option = document.createElement('option');
                    option.value = group.id;
                    option.textContent = group.name;
                    selGroup.appendChild(option);
                });
            } else {
                if (selGroup.selectedIndex===-1){
                    const option=document.createElement('option');
                    option.text='No group registered yet';
                    selGroup.add(option);
                } 
            }

        }
    }

    const getGanttData= (url,token)=>{
        return new Promise((resolve, reject) => {
            let xhr = new XMLHttpRequest();
            const customerid=document.querySelector('#id_project').value;
            const groupid=document.querySelector('#id_group').value;
            //Se prepara el objeto a enviar
            const formData= new FormData();
            formData.append('wstoken',token);
            formData.append('wsfunction', 'block_gantt_diagram_get_gantt');
            formData.append('moodlewsrestformat', 'json');
            formData.append('params[0][customerid]',customerid);
            formData.append('params[0][groupid]',groupid);
            
            xhr.open('POST',url,true);
            xhr.send(formData);
    
            xhr.onload = (ev)=> {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    resolve(response); // Resolve with the parsed response data
                } else {
                    rejectAnswer(xhr);
                    reject('Error al cargar la información'); // Rechaza la promesa en caso de error
                }
            }
    
            xhr.onerror = ()=> {
                rejectAnswer(xhr);
                reject('Error de red al cargar la información'); // Rechaza la promesa en caso de error de red
            }
        });
    }

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


    return {
        init: function (role, token) {
            shared.areElementsLoaded('#mgantt-container, #gantt_customer_select').then((elements) => {
                const selproject = document.querySelector('#id_project');
                selproject.addEventListener('change', (ev) => {
                    reloadgroups(ev.target.value, token, url);
                });
    
                // Fetch Gantt data and reload the template
                getGanttData(url, token)
                    .then((ganttData) => {
                        console.log('Gantt data fetched:', ganttData); // Debugging
                        reloadTemplate(ganttData);
                    })
                    .catch((error) => {
                        console.error('Error fetching Gantt data:', error);
                    });
                const boreload=document.querySelector('#id_reload');
                boreload.addEventListener('click',(ev)=>{
                    getGanttData(url, token)
                    .then((ganttData) => {
                        console.log('Gantt data fetched:', ganttData); // Debugging
                        reloadTemplate(ganttData);
                    })
                    .catch((error) => {
                        console.error('Error fetching Gantt data:', error);
                    });
                })
            });
        }
    };
    
});




