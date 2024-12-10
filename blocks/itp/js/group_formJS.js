const url=M.cfg.wwwroot+'/webservice/rest/server.php';

document.addEventListener('DOMContentLoaded',()=>{
    let boaddnew=document.querySelector('#id_bosubmit');
    boaddnew.classList.remove('btn-secondary');
    boaddnew.classList.add('btn-primary');

    //Se añade clase bootstrap a boton remove
    let boremove=document.querySelector('#id_boremove');
    boremove.classList.remove('btn-secondary');
    boremove.classList.add('btn-danger');

    //Reubicación de botones
    let buttonContainer=document.querySelector('#button_container');
    let boaddnewdiv=document.querySelector("#fitem_id_bosubmit>div:nth-child(2)");
    boaddnewdiv.classList.remove('col-md-9');
    boaddnewdiv.classList.add('mr-1');
    let boremovediv=document.querySelector("#fitem_id_boremove>div:nth-child(2)");
    boremovediv.classList.remove('col-md-9');
    boremovediv.classList.add('ml-1');
    let boContainer=document.createElement('div');
    boContainer.appendChild(boaddnewdiv);
    boContainer.appendChild(boremovediv);
    buttonContainer.appendChild(boContainer);
    boContainer.classList.add('flex','row');
    let old_addButton_layer=document.querySelector('#fitem_id_bosubmit');
    let old_removeButton_layer=document.querySelector('#fitem_id_boremove');
    old_addButton_layer.remove();
    old_removeButton_layer.remove();

    //Reubicando error message
    let errorlayer=document.querySelector('#error-message');
    buttonContainer.parentNode.insertBefore(errorlayer, buttonContainer.nextSibling);
    errorlayer.style.display='none';

    const groupListSel=document.querySelector('#id_tegrouplist');
    groupListSel.classList.add('pr-2');

    let checkbox=document.getElementById('id_hiddengroup'); 
    let ifhidden=0;

    checkbox.addEventListener('change', function() {
        if (checkbox.checked) {
            ifhidden=checkbox.value;
        } else {
            ifhidden=0;
        }
    });

    
    //Token
    let token=document.querySelector('input[name="token"]').value;
    
    //Lista de clientes
    const customerSel=document.querySelector('#id_tecustomer');

    const firstClient = (typeof customerSel[0]!=='undefined' && !isNaN(customerSel.options[0].value))?parseInt(customerSel.options[0].value):0;

    loadGroupOfSelectedCustomer(firstClient,token,url);

    customerSel.addEventListener('change',(e)=>{
        const selectedValue= e.target.value;
        loadGroupOfSelectedCustomer(selectedValue,token,url);
    });

    //Se define comportamiento ADD NEW
    boaddnew.addEventListener('click',(e)=>{
        createGroup(ifhidden,token);        
    })

    //Se define el comportamiento de Remove
    boremove.addEventListener('click',(e)=>{
        removeGroup(token);
    })
});

const removeGroup=(token)=>{
    const groupListSel=document.querySelector('#id_tegrouplist');
    const selectedGroup=groupListSel.value;
    
    if (selectedGroup)
        removeGroupFromSelectedCustomer(selectedGroup, token, url);
    else{
        const errMsg=document.querySelector('#error-message');
        const msg="No Group Selected. Please, select a group from the list";
        showMessage(errMsg,msg);
    }
}

const createGroup=(ifhidden,token)=>{
    const grouptrainee=document.querySelector('#id_tegroup'); 
    const groupValue=grouptrainee.value.trim();
    const selectedCustomer= document.querySelector('#id_tecustomer').value;
    const errMsg=document.querySelector('#error-message');
    //Si el grupo es administrativo o no.
    
    

    
    if (selectedCustomer && groupValue)
        createGroupInSelectedCustomer(ifhidden,selectedCustomer,groupValue,token,url);
    else if (selectedCustomer==='') { 
        const msg="Error: No proyect selected.";
        showMessage(errMsg,msg);
    } else if (groupValue===''){
        const msg="Error: Vessel can't be empty.";
        showMessage(errMsg,msg);
        grouptrainee.focus();

    }
}

const removeGroupFromSelectedCustomer= (groupid,token,url)=>{
    let xhr = new XMLHttpRequest();
    
    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', 'block_itp_remove_group');
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][groupid]',parseInt(groupid));
    
    xhr.open('POST',url,true);
    xhr.send(formData);

    xhr.onload = (ev)=> {
        reqHandlerRemoveGroup(xhr);
    }

    xhr.onerror = ()=> {
        rejectAnswer(xhr);
    }
}

const createGroupInSelectedCustomer = (ifhidden,selectedCustomerid,groupValue,token,url)=>{
    let xhr = new XMLHttpRequest();
    
    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', 'block_itp_add_group');
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][customerid]',selectedCustomerid);
    formData.append('params[0][groupname]',groupValue);
    formData.append('params[0][ifhidden]',ifhidden);
    
    xhr.open('POST',url,true);
    xhr.send(formData);

    xhr.onload = (ev)=> {
        reqHandlerCreateGroup(xhr);
    }

    xhr.onerror = ()=> {
        rejectAnswer(xhr);
    }
}

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


const reqHandlerRemoveGroup = (xhr)=> {
    if (xhr.readyState=== 4 && xhr. status === 200){
        if (xhr.response){
            const response=JSON.parse(xhr.response);
            if (!response){
                const errMsg=document.querySelector('#error-message');
                const msg="No group selected. Please, select a group";
                showMessage(errMsg,msg);
            } else {
                const groupListSel=document.querySelector('#id_tegrouplist');
                groupListSel.remove(groupListSel.selectedIndex);
            }
        }
    }
}
const reqHandlerCreateGroup=(xhr)=>{
    if (xhr.readyState=== 4 && xhr. status === 200){
        if (xhr.response){
            const response=JSON.parse(xhr.response);
            const grouptrainee=document.querySelector('#id_tegroup');
            window.console.log(response);
            if (response.result===0){
                const errMsg=document.querySelector('#error-message');
                const msg="A Vessel must be written. Please, write a vessel";
                grouptrainee.focus();
                grouptrainee.select();
                showMessage(errMsg,msg);
            } else { //suponiendo que todo haya ido bien
                const groupListSel=document.querySelector('#id_tegrouplist');
                if (document.querySelector('#id_tegrouplist option')!==null)
                    if (document.querySelector('#id_tegrouplist option').value==='0')
                        groupListSel.innerHTML='';
                const value=response.result;
                const text=grouptrainee.value.toUpperCase();
                const ifhidden=response.ifhidden;
                const option=createOption(value,text,ifhidden);
                
                groupListSel.add(option);
            }
        }
    }
}

const reqHandlerChangeListOfGroups=(xhr)=>{
    if (xhr.readyState=== 4 && xhr. status === 200){
        if (xhr.response){
            const response=JSON.parse(xhr.response);
            
            // Obtener el elemento select
            const grouptraineeSel = document.getElementById('id_tegrouplist');

            // Limpiar las opciones existentes (opcional)
            grouptraineeSel.innerHTML = '';

            if (response.length!==0) {
                // Iterar sobre los datos y crear las opciones
                response.forEach(item => {
                    let option = createOption(item.id,item.name,item.hidden);
                    grouptraineeSel.appendChild(option);
                });
            } else {
                let option=createOption(0,'No Groups');
                grouptraineeSel.appendChild(option);
            }
        }
    }
}

const createOption= (value,text,ifhidden)=>{
    const option = document.createElement('option');
    option.value = value;
    option.text = text;
    option.dataset.hidden=ifhidden;
    if (ifhidden==1){
        option.classList.add('bg-warning');
    }
    return option;
}

const rejectAnswer = (xhr)=>{
    //Manejar el error
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
