const url=M.cfg.wwwroot+'/webservice/rest/server.php';

//Al completar la carga del formulario se eliminan las capas sobrantes
document.addEventListener('DOMContentLoaded',()=>{
    let boaddnew=document.querySelector('#id_bosubmit');
    boaddnew.classList.remove('btn-secondary');
    boaddnew.classList.add('btn-primary');

    //Se añade clase bootstrap a boton remove
    let boremove=document.querySelector('#id_boremove');
    boremove.classList.remove('btn-secondary');
    boremove.classList.add('btn-danger');

    let boupdate=document.querySelector('#id_boupdate');
    boupdate.disabled=true;
    boupdate.classList.remove('btn-secondary');
    boupdate.classList.add('btn-primary');

    //Se seleccionan capas y controles para que al cargar el formulario podamos eliminar las que dificulten la apariencia
    let teshortname=document.querySelector('#id_customercode');
    let tename=document.querySelector('#id_customername');
    let selectText=document.querySelector('#id_type');
    let form=document.querySelector('#customerformid');
    let selectContainer=document.querySelector('#fitem_id_type');
    
    let boaddNewContainer=document.querySelector('#fitem_id_bosubmit');
    let divAfterboAddNew=document.querySelector("#fitem_id_bosubmit>div:nth-child(1)");
    let divAfterboAddNew2=document.querySelector("#fitem_id_bosubmit>div:nth-child(2)");
    let boremoveContainer=document.querySelector('#fitem_id_boremove');
    let divAfterboRemove=document.querySelector("#fitem_id_boremove>div:nth-child(1)");
    let boupdateContainer=document.querySelector('#fitem_id_boupdate');
    let divAfterboupdate=document.querySelector('#fitem_id_boupdate>div:nth-child(1)');
    
    let token=document.querySelector('input[name="token"]').value;
        document.querySelector('#error-message').style.display="none";
    teshortname.classList.add('form-control');
    tename.classList.add('form-control');
    selectContainer.classList.add('mt-3');
    
    let boContainer=document.createElement('div');
    boContainer.classList.add('flex');
    boContainer.classList.add('row');
    boContainer.classList.add('m-3');
    boaddNewContainer.classList.remove('form-group');
    boaddNewContainer.classList.remove('row');
    boremoveContainer.classList.remove('form-group');
    boremoveContainer.classList.remove('row');
    boupdateContainer.classList.remove('row');
    boContainer.appendChild(boaddNewContainer);
    boContainer.appendChild(boupdateContainer);
    boContainer.appendChild(boremoveContainer);
    selectContainer.appendChild(boContainer);
    divAfterboAddNew.remove();
    divAfterboRemove.remove();
    divAfterboupdate.remove();
    
    //Se eliminan clases que entorpecen la estética
    divAfterboAddNew2.classList.remove('col-md-9');
    divAfterboAddNew2.classList.remove('form-inline');
    divAfterboAddNew2.classList.remove('align-items-start');
    divAfterboAddNew2.classList.remove('felement');
    divAfterboRemove.classList.remove('col-md-9');
    divAfterboRemove.classList.remove('form-inline');
    divAfterboRemove.classList.remove('align-items-start');
    divAfterboRemove.classList.remove('felement');
    

    boremove.addEventListener('click',(e)=>{
        removeProyect(selectText.value, token,url);
    });

    boaddnew.addEventListener('click',(e)=>{
        let hasErrors=false;
        if (teshortname.value.trim()===''){
            showMessage(document.querySelector('#error-message'),"The proyect code, cant be empty");
            teshortname.focus();
            hasErrors=true;
        }
        if (tename.value.trim()===''){
            showMessage(document.querySelector('#error-message'),"The proyect must have a name");
            tename.focus();
            hasErrors=true;
        }
          
        //Si no hay errores se añade el cliente
        if (!hasErrors)
            addproyect(teshortname.value,tename.value,token, url);
    })

    selectText.addEventListener('click',(e)=>{
        const shortname=e.target.value;
        const name=e.target.text;

        teshortname.value=shortname;
        tename.value=name;
        
        
        boupdate.disabled=false;
        
    })

    boupdate.addEventListener('click',()=>{
        window.console.log("update...");
        const originalshortname=selectText.value;
        const shortname=teshortname.value.trim();
        const project_name=tename.value.trim();
        updateProject(originalshortname,shortname, project_name, token,url);
    })
})


const updateProject=(originalshortname,shortname,project_name,token, url)=>{
    let xhr=new XMLHttpRequest();

    // Obtén los archivos de los filepickers
    
    
    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', 'block_itp_update_client');
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][original_shortname]',originalshortname);
    formData.append('params[0][shortname]',shortname);
    formData.append('params[0][proyectname]',project_name);

    

    xhr.open('POST',url,true);
    xhr.send(formData);

    xhr.onload = (ev)=> {
        processUpdateAnswer(xhr,shortname,project_name);
    }

    xhr.onerror = ()=> {
        rejectAnswer(xhr);
    }
}

const processUpdateAnswer=(xhr,shortname,name)=>{
    if (xhr.readyState=== 4 && xhr. status === 200){
        if (xhr.response){
            window.console.log(xhr.response);
            const response=JSON.parse(xhr.response);
            if (response===0 || !response){
                const errMsg=document.querySelector('#error-message');
                const msg="Operation has not been completed. Verify that there are not duplicates for the shortname.";
                const teshortname=document.querySelector('#id_customercode');
                teshortname.focus();
                teshortname.select();
                showMessage(errMsg,msg);
            } else { //suponiendo que todo haya ido bien
                const selectText = document.querySelector('#id_type');
               // Variables con los valores deseados
                const id = response.id; // Ejemplo de ID
                const shortname = response.shortname; // Ejemplo de shortname
                const projectname = response.name; // Ejemplo de projectname

                // Crear el texto para la opción
                const optionValue = shortname;
                const optionText = `${id} - ${shortname} - ${projectname}`;

                // Buscar si ya existe una opción con ese value
                let option = selectText.options[selectText.options.selectedIndex];
                option.text=optionText;
                option.value=optionValue;

                if (!option) {
                    // Si no existe, crear una nueva opción
                    option = document.createElement('option');
                    option.value = optionValue;
                    selectText.appendChild(option);
                }
            }
        }
    }
}


const removeProyect = (value,token, url)=>{
    let xhr = new XMLHttpRequest();
    
    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', 'block_itp_remove_client');
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][shortname]',value);

    xhr.open('POST',url,true);
    xhr.send(formData);

    xhr.onload = (ev)=> {
        reqHandlerDropElem(xhr);
    }

    xhr.onerror = ()=> {
        rejectAnswer(xhr);
    }
    
}

const reqHandlerDropElem=(xhr)=>{
    if (xhr.readyState=== 4 && xhr. status === 200){
        if (xhr.response){
            const response=JSON.parse(xhr.response);
            if (response){
                const selectText=document.querySelector('#id_type');
                selectText.remove(selectText.selectedIndex);
            }
        }
    }
}

const addproyect=(shortname,proyect_name,token, url)=>{
    let xhr=new XMLHttpRequest();

    // Obtén los archivos de los filepickers
    
    
    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', 'block_itp_add_client');
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][shortname]',shortname);
    formData.append('params[0][proyectname]',proyect_name);

    

    xhr.open('POST',url,true);
    xhr.send(formData);

    xhr.onload = (ev)=> {
        processAnswer(xhr,shortname,proyect_name);
    }

    xhr.onerror = ()=> {
        rejectAnswer(xhr);
    }
}

const processAnswer=(xhr,shortname,name)=>{
    if (xhr.readyState=== 4 && xhr. status === 200){
        if (xhr.response){
            window.console.log(xhr.response);
            const response=JSON.parse(xhr.response);
            if (response===0){
                const errMsg=document.querySelector('#error-message');
                const msg="Operation has not been completed. Verify that there are not duplicates for the shortname.";
                const teshortname=document.querySelector('#id_customercode');
                teshortname.focus();
                teshortname.select();
                showMessage(errMsg,msg);
            } else { //suponiendo que todo haya ido bien
                const selectText=document.querySelector('#id_type');
                const option=document.createElement('option');
                option.text=response+ ' - ' +shortname.toUpperCase() + ' - ' + name;
                option.value=shortname;
                option.dataset.id=response;
                selectText.add(option);
            }
        }
    }
}

const rejectAnswer=(xhr)=>{
    const errMsg=document.querySelector('#error-message');
    const msg="Operation not completed. Please, check the connection.";
    showMessage(errMsg,msg);
}

const showMessage=(elem,msg)=>{
    elem.textContent=msg;
    elem.style.display='block';

    setTimeout(function() {
        elem.style.display = 'none';
    }, 3000); // 3000 milisegundos = 3 segundos
}

function obtenerUltimaCadena(url) {
    const ultimoSlash = url.lastIndexOf('/');
    if (ultimoSlash === -1) {
      return url; // No hay barras, devuelve la URL completa
    }
    const ultimaCadena= url.substring(ultimoSlash + 1);
    return decodeURIComponent(ultimaCadena);
  }
  