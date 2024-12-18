const url=M.cfg.wwwroot+'/blocks/itp/admin/updateitp.php';
const urlService=M.cfg.wwwroot+'/webservice/rest/server.php';
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
    document.querySelector('#error-message').style.display='none';

    //Token
    let token=document.querySelector('input[name="token"]').value;

    boreset.addEventListener('click',(e)=>{
        requestResetTrainingPlan(urlService,token,'reset');
    });

    //Lista de clientes
    const customerSel=document.querySelector('#id_tecustomer');

    const firstClient = (typeof customerSel[0]!=='undefined' && !isNaN(customerSel.options[0].value))?parseInt(customerSel.options[0].value):0;

    //loadGroupOfSelectedCustomer(firstClient,token,url);

    customerSel.addEventListener('change',(e)=>{
        const selectedValue= e.target.value;
        loadGroupOfSelectedCustomer(selectedValue,token,url);
    });
});

const requestResetTrainingPlan=(url,token,action)=>{
    let xhr = new XMLHttpRequest();
    
        //Se prepara el objeto a enviar
        const formData= new FormData();
        formData.append('wstoken',token);
        formData.append('wsfunction', 'block_itp_reset_itp');
        formData.append('moodlewsrestformat', 'json');
        formData.append('params[0][op]',action);
        
        xhr.open('POST',url,true);
        xhr.send(formData);
    
        xhr.onload = (ev)=> {
            reqHandlerResetTrainingPlan(xhr);
        }
    
        xhr.onerror = ()=> {
            rejectAnswer(xhr);
        }
}

const reqHandlerResetTrainingPlan=(xhr)=>{
    if (xhr.readyState=== 4 && xhr. status === 200){
        if (xhr.response){
            const response=JSON.parse(xhr.response);
            if (!response || /error/i.test(response.message)){
                const errMsg=document.querySelector('#error-message');
                const msg="Something went wrong. The table itp hasn't been reset yet.";
                showMessage(errMsg,msg);
            } else {
                const errMsg=document.querySelector('#error-message');
                errMsg.classList.remove('alert-danger');
                errMsg.classList.add('alert-info');
                const msg="The table has been reset. Operation completed.";
                showMessage(errMsg,msg);
            }
        }
    }
}

const loadGroupOfSelectedCustomer=(value,token,url)=>{
    let xhr = new XMLHttpRequest();
    
    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('customerid',value);
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

const showMessage=(elem,msg)=>{
    elem.textContent=msg;
    elem.style.display='block';

    setTimeout(function() {
        elem.style.display = 'none';
    }, 3000); // 3000 milisegundos = 3 segundos
    
}
