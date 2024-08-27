const url=window.location.protocol+'//'+window.location.hostname+'/webservice/rest/server.php';

//Al completar la carga del formulario se eliminan las capas sobrantes
document.addEventListener('DOMContentLoaded',()=>{
    //Correcciones visuales de los botones
    const boremove_layer=document.querySelector('#fitem_id_boremove');
    boremove_layer.classList.remove('row');
    boremove_layer.childNodes[1].classList.remove('col-md-3');
    boremove_layer.childNodes[3].classList.remove('col-md-9');
    
    const boreset=document.querySelector('#id_boremove');
    boreset.classList.remove('btn-secondary');
    boreset.classList.add('btn-danger');

    const boremove_container=document.querySelector('#button_container');
    boremove_container.classList.remove('flex');
    boremove_container.classList.add('flex-row-reverse');

    //Token
    let token=document.querySelector('input[name="token"]').value;

    boreset.addEventListener('click',(e)=>{
        requestResetTrainingPlan(url,token,'reset');
    });

    //Lista de clientes
    const customerSel=document.querySelector('#id_tecustomer');

    const firstClient = (typeof customerSel[0]!=='undefined' && !isNaN(customerSel.options[0].value))?parseInt(customerSel.options[0].value):0;

    loadGroupOfSelectedCustomer(firstClient,token,url);

    customerSel.addEventListener('change',(e)=>{
        const selectedValue= e.target.value;
        loadGroupOfSelectedCustomer(selectedValue,token,url);
    });

    


});

const loadGroupOfSelectedCustomer=(value,token,url)=>{
    let xhr = new XMLHttpRequest();
    
    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', 'block_itp_load_groups');
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][customerid]',value);
    
    xhr.open('POST',url,true);
    xhr.send(formData);

    xhr.onload = (ev)=> {
        reqHandlerChangeListOfGroups(xhr);
    }

    xhr.onerror = ()=> {
        rejectAnswer(xhr);
    }
}

const reqHandlerChangeListOfGroups=(xhr)=>{
    if (xhr.readyState=== 4 && xhr. status === 200){
        if (xhr.response){
            const response=JSON.parse(xhr.response);
            
            // Obtener el elemento select
            const grouptraineeSel = document.getElementById('id_tegroup');

            // Limpiar las opciones existentes (opcional)
            grouptraineeSel.innerHTML = '';

            if (response.length!==0) {
                let option = createOption(0,'All Groups');
                grouptraineeSel.appendChild(option);
                // Iterar sobre los datos y crear las opciones
                response.forEach(item => {
                    let option = createOption(item.id,item.name);
                    grouptraineeSel.appendChild(option);
                });
            } else {
                let option=createOption(0,'No Groups');
                grouptraineeSel.appendChild(option);
            }
        }
    }
}

const createOption= (value,text)=>{
    const option = document.createElement('option');
    option.value = value;
    option.text = text;
    return option;
}