define([
    'core/templates',
    'block_graphical_events/shared_library',
], function(Templates,shared) {

    const url=M.cfg.wwwroot+'/webservice/rest/server.php';
    const token=document.querySelector('input[name="token"]').value;

    const init = (userIdFromPHP) => {
        
        shared.areElementsLoaded('#layer').then((elements)=>{
            console.log("TODO: START");
            const itemPanel=document.querySelectorAll('#graphpanel li');
            

            itemPanel.forEach((item)=>{
                item.addEventListener('click',(e)=>{
                    //Limpiamos todas las clases activas
                    const linkItemPanel=document.querySelectorAll('#graphpanel li a');
                    linkItemPanel.forEach((itemlink)=>{
                        itemlink.classList.remove('active','active_tree_node');
                    })
                    e.target.classList.add('active','active_tree_node');
                    let userId=0;
                    
                    if (e.target.dataset.user==='yes')
                        userId = userIdFromPHP; // Fallback to 0 if userId is not set

                    else
                        userId=0;

                    //Se recarga la plantilla con los nuevos datos
                    reloadGraph(url,token,userId);
                })
            });

            const bosubmit=elements[0].querySelector('#idboclick');
            bosubmit.addEventListener('click',()=>{
                const tabs=document.querySelector('#graphpanel');
                const selectedTab=tabs.querySelector('.nav-link.active');
                let userId=0;
                    if (selectedTab.dataset.user==='yes')
                        userId = userIdFromPHP; // Fallback to 0 if userId is not set

                    else
                        userId=0;
                reloadGraph(url,token,userId);
            })

        });

        const reloadGraph=(url,token,userId)=>{
            const selectedCustomer=parseInt(document.querySelector('#id_graph_project').value);
            const selectedGroup=parseInt(document.querySelector('#id_graph_vessel').value);
            
            let xhr = new XMLHttpRequest();
        
            //Se prepara el objeto a enviar
            const formData= new FormData();
            formData.append('wstoken',token);
            formData.append('wsfunction', 'block_graphical_events_load_data');
            formData.append('moodlewsrestformat', 'json');
            formData.append('params[0][projectid]', selectedCustomer);
            formData.append('params[0][groupid]', selectedGroup);
            formData.append('params[0][userid]', userId);
            
            xhr.open('POST',url,true);
            
            xhr.send(formData);

            xhr.onload = (ev)=> {
                if (xhr.status === 200) {
                    reqHandlerLoadGraphEvent(xhr);
                    
                } else {
                    rejectAnswer(xhr);
                    
                }
            }

            xhr.onerror = ()=> {
                rejectAnswer(xhr);
                
            }
            
        }

        const reqHandlerLoadGraphEvent=(xhr)=>{
            const response=JSON.parse(xhr.responseText);
            reloadTemplate(response);
        }

        shared.areElementsLoaded('#id_graph_project, #id_graph_vessel').then((elements)=>{
            
            const selectProject=elements[0];

            const loadProjectOptionsPromise=new Promise((resolve,reject)=>{
                loadProjectOptions(url,token,resolve,reject);
            })
            
            loadProjectOptionsPromise.then(()=>{
                //Cuando se hayan cargado los proyectos, cargamos los grupos
                const loadGroups=new Promise((resolve,reject)=>{
                    loadVesselOptions(url,token,resolve,reject);
                })

                loadGroups.then(()=>{
                    reloadGraph(url,token,0);
                })
            })
            

            
            
            selectProject.addEventListener('change',(e)=>{
                loadVesselOptions(url,token,null,null);
            })
        });
    };

    const loadVesselOptions=(url,token,resolve,reject)=>{
        const projectid=document.querySelector('#id_graph_project').value;
        let xhr = new XMLHttpRequest();
        
        //Se prepara el objeto a enviar
        const formData= new FormData();
        formData.append('wstoken',token);
        formData.append('wsfunction', 'block_graphical_events_load_vessels');
        formData.append('moodlewsrestformat', 'json');
        formData.append('projectid', projectid);
        
        xhr.open('POST',url,true);
        xhr.send(formData);

        xhr.onload = (ev)=> {
            if (xhr.status === 200) {
                reqHandlerVesselLoadEvent(xhr);
                if (typeof resolve === "function") {
                    // Llamar a la función opcional si existe
                    resolve();
                } 
            } else {
                rejectAnswer(xhr);
                if (typeof reject === "function") {
                    // Llamar a la función opcional si existe
                    reject();
                } 
            }
        }

        xhr.onerror = ()=> {
            rejectAnswer(xhr);
            
        }
    }

    const reqHandlerVesselLoadEvent=(xhr)=>{
        const response=JSON.parse(xhr.responseText);
        const selectVessel=document.querySelector('#id_graph_vessel');

        // Clear existing options
        selectVessel.innerHTML = '';

        if (response && response.length>0){
            // Add vessel options
            response.forEach(vessel => {
                const option = document.createElement('option');
                option.value = vessel.id;
                option.textContent = vessel.name;
                selectVessel.appendChild(option);
            });
        } else {
            // Add a default option
            const defaultOption = document.createElement('option');
            defaultOption.value = '0';
            defaultOption.textContent = 'No group registered';
            selectVessel.appendChild(defaultOption);
        }

        
    }

    const loadProjectOptions=(url,token,resolve,reject)=>{
        let xhr = new XMLHttpRequest();
    
        //Se prepara el objeto a enviar
        const formData= new FormData();
        formData.append('wstoken',token);
        formData.append('wsfunction', 'block_graphical_events_load_projects');
        formData.append('moodlewsrestformat', 'json');
        
        xhr.open('POST',url,true);
        xhr.send(formData);

        xhr.onload = (ev)=> {
            if (xhr.status === 200) {
                reqHandlerCustomerLoadEvent(xhr);
                resolve();
            } else {
                rejectAnswer(xhr);
                reject('Error al cargar la información');
            }
        }

        xhr.onerror = ()=> {
            rejectAnswer(xhr);
            reject('Error de conexión');
        }
    }

    const reqHandlerCustomerLoadEvent=(xhr)=>{
        const response=JSON.parse(xhr.responseText);
        const selectProject=document.querySelector('#id_graph_project');
        
        // Clear existing options
        selectProject.innerHTML = '';

        if (response){
            // Add project options
            response.forEach(project => {
                const option = document.createElement('option');
                option.value = project.id;
                option.textContent = project.shortname;
                selectProject.appendChild(option);
            });
        } else {
            // Add a default option
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'No project registered';
            selectProject.appendChild(defaultOption);
        }

        
    }

    const rejectAnswer=(xhr)=>{
        window.console.log('error de conexion');
    }

    const reloadTemplate = (data) => {
        // Añadir el dato al contexto del template
        const templateData = {
            jsondata: JSON.stringify(data), // Convertir el dato a JSON
        };
    
        // Renderizar el template Mustache
        Templates.renderForPromise('block_graphical_events/canvas', templateData)
            .then(({ html, js }) => {
                const content = document.querySelector('#graphicalpanel');
                content.innerHTML = '';
                Templates.appendNodeContents(content, html, js);
            })
            .catch((error) => {
                console.error('Error rendering template:', error);
            });
    };
         
    return {
        init: init
    };
});
