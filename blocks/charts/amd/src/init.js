define([
    'core/templates',
    'block_charts/shared_library',
], function(Templates,shared) {

    const url=M.cfg.wwwroot+'/webservice/rest/server.php';
    const token=document.querySelector('input[name="token"]').value;


    const init = () => {
        
        shared.areElementsLoaded('#id_chart_boclick, #myBarChartTickets').then((elements)=>{
            console.log("TODO: START chart bar loaded");
            loadProjectOptions(url,token).then(()=>{
                reloadGraph();
            });

            const currentYear=new Date().getFullYear();
            let years=[];
            for (let index = -1; index < 6; index++) {
                const element = currentYear+index;
                years.push(element);
            }
            
            const selYear=document.querySelector('#id_chart_year');
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
                
                reloadGraph();
            })

        });

    };

    const reloadGraph=()=>{
        //Obtiene los datos y recarga el gráfico
        let xhr=new XMLHttpRequest();
        const project=document.querySelector('#id_chart_project').value;
        const year=document.querySelector('#id_chart_year').value;
        const month=document.querySelector('#id_chart_month').value;

         //Se prepara el objeto a enviar
         const formData= new FormData();
         formData.append('wstoken',token);
         //Se obtienen numero de tickets agrupados por categorias
         formData.append('wsfunction', 'block_charts_get_number_tickets_groupedby_category');
         formData.append('moodlewsrestformat', 'json');
         formData.append('params[0][project]',parseInt(project));
         formData.append('params[0][year]',parseInt(year));
         formData.append('params[0][month]',parseInt(month));

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

    const reqHandlerLoadGraphEvent=(myXhr)=>{
        const response=JSON.parse(myXhr.responseText);
            reloadTemplate(response);
    }

   /* Metodos responsables de la carga de proyectos */
    const loadProjectOptions=(url,token)=>{
        return new Promise((resolve,reject)=>{
            let xhr = new XMLHttpRequest();
    
            //Se prepara el objeto a enviar
            const formData= new FormData();
            formData.append('wstoken',token);
            formData.append('wsfunction', 'block_charts_load_projects');
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

    const reqHandlerCustomerLoadEvent=(xhr)=>{
        const response=JSON.parse(xhr.responseText);
        const selectProject=document.querySelector('#id_chart_project');
        
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
        Templates.renderForPromise('block_charts/barchart', templateData)
            .then(({ html, js }) => {
                const content = document.querySelector('#barchartpanel');
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
