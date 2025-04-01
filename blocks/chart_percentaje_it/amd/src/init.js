define([
    'core/toast',
    'core/templates',
    'block_chart_percentaje_it/shared_library',
], function(addToast,Templates,shared) {

    const url=M.cfg.wwwroot+'/webservice/rest/server.php';
    const token=document.querySelector('input[name="token"]').value;


    const init = () => {
        
        shared.areElementsLoaded('#id_chart_percentaje_it_boclick, #myPieChart').then((elements)=>{
            
            loadProjectOptions(url,token).then(()=>{
                loadGroupOptions(url,token)
            }).then(()=>reloadPieChart());
          

            const currentYear=new Date().getFullYear();
            let years=[];
            for (let index = -1; index < 6; index++) {
                const element = currentYear+index;
                years.push(element);
            }
            
            const selYear=document.querySelector('#id_chart_percentaje_it_year');
            selYear.innerHTML='';
            years.forEach((elem)=>{
                const op=document.createElement('option');
                op.textContent=elem;
                op.value=elem;
                if (currentYear===elem){
                    op.selected=true;
                }
                selYear.appendChild(op);
            })
            
            const bosubmit=elements[0];
            bosubmit.addEventListener('click',()=>{  
                reloadPieChart();
            })

            const project=document.querySelector('#id_chart_percentaje_it_project');
            project.addEventListener('change',(ev)=>{
                loadGroupOptions(url,token);
            })

        });

    };

    const loadGroupOptions=(url,token)=>{
        let xhr = new XMLHttpRequest();
    
        const projectid = document.querySelector('#id_chart_percentaje_it_project').value;

            //Se prepara el objeto a enviar
            const formData= new FormData();
            formData.append('wstoken',token);
            formData.append('wsfunction', 'block_chart_percentaje_it_load_vessels');
            formData.append('moodlewsrestformat', 'json');
            formData.append('projectid',parseInt(projectid));
            
            xhr.open('POST',url,true);
            xhr.send(formData);
    
            xhr.onload = (ev)=> {
                if (xhr.status === 200) {
                    reqHandlerGroupLoadEvent(xhr);
                    
                } else {
                    rejectAnswer(xhr);
                }
            }
    
            xhr.onerror = ()=> {
                rejectAnswer(xhr);
                
            }
    }

    const reloadPieChart = () => {
        // Show loading indicator
        shared.showLoader();
        
        let xhr=new XMLHttpRequest();
        // Get form values
        const project = document.querySelector('#id_chart_percentaje_it_project').value;
        const year = document.querySelector('#id_chart_percentaje_it_year').value;
        const month = document.querySelector('#id_chart_percentaje_it_month').value;
        const group = document.querySelector('#id_chart_percentaje_it_group')?.value || 0;
        const type = (parseInt(month) === 0) ? 'year' : 'month';
    
        // Prepare request
        const formData = new FormData();
        formData.append('wstoken', token);
        formData.append('wsfunction', 'block_chart_percentaje_it_get_states_percentage');
        formData.append('moodlewsrestformat', 'json');
        formData.append('projectid', parseInt(project));
        formData.append('year', parseInt(year));
        formData.append('month', parseInt(month));
        formData.append('type', type);
        formData.append('groupid', parseInt(group));
    
        xhr.open('POST',url,true);
        xhr.send(formData);

        xhr.onload = (ev)=> {
            // Hide loader regardless of success/failure
        shared.hideLoader();
            if (xhr.status === 200) {
                reqHandlerLoadPieChartEvent(xhr);
                
            } else {
                rejectAnswer(xhr);
               
            }
        }

        xhr.onerror = ()=> {
            // Hide loader regardless of success/failure
        shared.hideLoader();
            rejectAnswer(xhr);
            
        }
 
        // Optional: Handle timeout if needed
    xhr.ontimeout = () => {
        shared.hideLoader();
        rejectAnswer(xhr);
    };
        
    };
    

    const reqHandlerLoadPieChartEvent=(myXhr)=>{
        const response=JSON.parse(myXhr.responseText);
            reloadTemplatePieGraph(response);
    }

   /* Metodos responsables de la carga de proyectos */
    const loadProjectOptions=(url,token)=>{
        return new Promise((resolve,reject)=>{
            let xhr = new XMLHttpRequest();
    
            //Se prepara el objeto a enviar
            const formData= new FormData();
            formData.append('wstoken',token);
            formData.append('wsfunction', 'block_chart_percentaje_it_load_projects');
            formData.append('moodlewsrestformat', 'json');
            
            xhr.open('POST',url,true);
            xhr.send(formData);
    
            xhr.onload = (ev)=> {
                if (xhr.status === 200) {
                    reqHandlerCustomerLoadEvent(xhr);
                    resolve();
                } else {
                    rejectAnswer(xhr);
                    reject('Error en la promesa');
                }
            }
    
            xhr.onerror = ()=> {
                rejectAnswer(xhr);
                
            }
        });
        
    }
    const reqHandlerGroupLoadEvent=(xhr)=>{
        const response=JSON.parse(xhr.responseText);
        const selectProject=document.querySelector('#id_chart_percentaje_it_group');
        
        // Clear existing options
        selectProject.innerHTML = '';

        const option = document.createElement('option');
            option.value = '0';
            option.textContent = 'All Groups';
            selectProject.appendChild(option);

        if (response.length>0){
            // Add project options
            response.forEach(project => {
                const option = document.createElement('option');
                option.value = project.id;
                option.textContent = project.name;
                selectProject.appendChild(option);
            });
        } else {
            if (response.length===0 || !response){
                selectProject.innerHTML = '';

                // Add a default option
                const defaultOption = document.createElement('option');
                defaultOption.value = '0';
                defaultOption.textContent = 'No project registered';
                selectProject.appendChild(defaultOption);
            }
            
        }

    }

    const reqHandlerCustomerLoadEvent=(xhr)=>{
        const response=JSON.parse(xhr.responseText);
        const selectProject=document.querySelector('#id_chart_percentaje_it_project');
        
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

    const reloadTemplatePieGraph = (data) => {
        // Añadir el dato al contexto del template
        const templateData = {
            jsondata: JSON.stringify(data), // Convertir el dato a JSON
        };
    
        // Renderizar el template Mustache
        Templates.renderForPromise('block_chart_percentaje_it/piechart', templateData)
            .then(({ html, js }) => {
                const content = document.querySelector('#myPieChart');
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