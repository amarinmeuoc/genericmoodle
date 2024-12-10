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

    //Se añade clase bootstrap a boton edit
    let boedit=document.querySelector('#id_boedit');
    boremove.classList.remove('btn-secondary');
    boremove.classList.add('btn-info');
    boedit.disabled=true;

    //Se seleccionan capas y controles para que al cargar el formulario podamos eliminar las que dificulten la apariencia
    let categoryname=document.querySelector('#id_categoryname');

    let checkbox=document.getElementById('id_hiddencategory'); 
    let ifhidden=0;

    checkbox.addEventListener('change', function() {
        if (checkbox.checked) {
            ifhidden=checkbox.value;
        } else {
            ifhidden=0;
        }
    });
    
    let form=document.querySelector('#categoryformid');
    let selectContainer=document.querySelector('#fitem_id_categorySelect');
    let boaddNewContainer=document.querySelector('#fitem_id_bosubmit');
    let divAfterboAddNew=document.querySelector("#fitem_id_bosubmit>div:nth-child(1)");
    let divAfterboAddNew2=document.querySelector("#fitem_id_bosubmit>div:nth-child(2)");
    let boremoveContainer=document.querySelector('#fitem_id_boremove');
    let divAfterboRemove=document.querySelector("#fitem_id_boremove>div:nth-child(1)");
    let divAfterboRemove2=document.querySelector("#fitem_id_boremove>div:nth-child(2)");
    let boeditContainer=document.querySelector('#fitem_id_boedit');
    let divAfterboEdit=document.querySelector("#fitem_id_boedit>div:nth-child(1)");
    let divAfterboEdit2=document.querySelector("#fitem_id_boedit>div:nth-child(2)");
    let token=document.querySelector('input[name="token"]').value;
    document.querySelector('#error-message').style.display='none';

    categoryname.classList.add('form-control');
    
    let boContainer=document.createElement('div');
    boContainer.classList.add('flex');
    boContainer.classList.add('row');
    boContainer.classList.add('m-3');
    boaddNewContainer.classList.remove('form-group');
    boaddNewContainer.classList.remove('row');
    boremoveContainer.classList.remove('form-group');
    boremoveContainer.classList.remove('row');
    boeditContainer.classList.remove('form-group');
    boeditContainer.classList.remove('row');
    boContainer.appendChild(boaddNewContainer);
    boContainer.appendChild(boeditContainer);
    boContainer.appendChild(boremoveContainer);
    
    selectContainer.appendChild(boContainer);
    divAfterboAddNew.remove();
    divAfterboRemove.remove();
    divAfterboEdit.remove();
    
    //Se eliminan clases que entorpecen la estética
    divAfterboAddNew2.classList.remove('col-md-9');
    divAfterboAddNew2.classList.remove('form-inline');
    divAfterboAddNew2.classList.remove('align-items-start');
    divAfterboAddNew2.classList.remove('felement');
    divAfterboRemove.classList.remove('col-md-9');
    divAfterboRemove.classList.remove('form-inline');
    divAfterboRemove.classList.remove('align-items-start');
    divAfterboRemove.classList.remove('felement');
    
    divAfterboEdit.classList.remove('col-md-9');
    divAfterboEdit.classList.remove('form-inline');
    divAfterboEdit.classList.remove('align-items-start');
    divAfterboEdit.classList.remove('felement');

    const categoryList=document.querySelector('#id_categorySelect');

    const options=document.querySelectorAll('#id_categorySelect option').forEach((option)=>{
        const hiddenValue=option.dataset.hidden;
        if (hiddenValue==1){
            option.classList.add('bg-warning');
        }
    })

    // Listener para clic en el select
    categoryList.addEventListener('click', (e) => {
        const index = categoryList.selectedIndex;
        handleCategoryChange(index);
    });

    // Listener para interacción con teclado
    categoryList.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
            // Asegurarnos de que el cambio se refleje con las teclas
            setTimeout(() => {
                const index = categoryList.selectedIndex;
                handleCategoryChange(index);
            }, 0); // Espera breve para permitir que el navegador actualice el índice seleccionado
        }
    });

    // Listener para cambio explícito con teclado (confirmación)
    categoryList.addEventListener('change', (e) => {
        const index = categoryList.selectedIndex;
        handleCategoryChange(index);
    });

    

    boremove.addEventListener('click',(e)=>{
        //removeProyect(selectText.value, token,url);
        window.console.log("El borrado no ha sido implementado aún.");
    });

    boaddnew.addEventListener('click',(e)=>{
        let hasErrors=false;
        if (categoryname.value.trim()===''){
            showMessage(document.querySelector('#error-message'),"The category name cant be empty");
            categoryname.focus();
            hasErrors=true;
        }
        
        
        //Si no hay errores se añade el cliente
        if (!hasErrors){
            
            addticketcategory(ifhidden,categoryname.value,token, url);
        }
            
    })

    boedit.addEventListener('click',(e)=>{
        let hasErrors=false;
        if (categoryname.value.trim()==='' || document.querySelector('#id_categorySelect').selectedIndex===-1){
            showMessage(document.querySelector('#error-message'),"The category name cant be empty, there must be at least a category selected");
            categoryname.focus();
            hasErrors=true;
        }
        
          
        //Si no hay errores se añade el cliente
        if (!hasErrors){
            const index=document.querySelector('#id_categorySelect').selectedIndex;
            const categoryid=document.querySelector('#id_categorySelect').options[index].value;
            //const ifhidden=document.querySelector('#id_categorySelect').options[index].dataset.hidden;
            
            editticketcategory(categoryid,ifhidden,categoryname.value,token, url);
        }
            
    })
})

function handleCategoryChange(e) {
    const checkbox=document.getElementById('id_hiddencategory'); 
    const categoryList=document.querySelector('#id_categorySelect');
    const categoryname = document.querySelector('#id_categoryname');
    const boedit=document.querySelector('#id_boedit');
    const index = categoryList.selectedIndex;
    

    if (index >= 0) {
        const selectedOption = categoryList.options[index];
        const categoryid = selectedOption.value;
        const ifhidden = parseInt(selectedOption.dataset.hidden);

        checkbox.checked = ifhidden === 1;
        categoryname.value = selectedOption.text;
        boedit.disabled = false;
    } else {
        categoryname.value = "";
    }
}


const addticketcategory=(ifhidden,categoryname,token, url)=>{
    let xhr=new XMLHttpRequest();
    
    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', 'local_ticketmanagement_add_ticketcategory');
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][category]',categoryname);
    formData.append('params[0][ifhidden]',ifhidden);
    

    xhr.open('POST',url,true);
    xhr.send(formData);

    xhr.onload = (ev)=> {
        processAnswer(xhr,categoryname);
    }

    xhr.onerror = ()=> {
        rejectAnswer(xhr);
    }
}

const processAnswer=(xhr,categoryname)=>{
    if (xhr.readyState=== 4 && xhr. status === 200){
        if (xhr.response){
            const response=JSON.parse(xhr.response);
            if (response.ok===0){
                const errMsg=document.querySelector('#error-message');
                const msg="Operation has not been completed. Verify that the category name is not duplicated.";
                const categoryname=document.querySelector('#id_categoryname');
                categoryname.focus();
                categoryname.select();
                showMessage(errMsg,msg);
            } else { //suponiendo que todo haya ido bien
                const selectText=document.querySelector('#id_categorySelect');
                const option=document.createElement('option');
                option.text=categoryname.toUpperCase();
                option.value=response.ok;
                option.dataset.hidden=response.ifhidden;
                if (response.ifhidden===1){
                    option.classList.add('bg-warning');
                }
                selectText.add(option);
            }
        }
    }
}

const editticketcategory=(categoryid,ifhidden,categoryname,token, url)=>{
    let xhr=new XMLHttpRequest();
    
    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', 'local_ticketmanagement_edit_ticketcategory');
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][category]',categoryname);
    formData.append('params[0][id]',categoryid);
    formData.append('params[0][ifhidden]',ifhidden);
    

    xhr.open('POST',url,true);
    xhr.send(formData);

    xhr.onload = (ev)=> {
        processEditAnswer(xhr,categoryid,categoryname);
    }

    xhr.onerror = ()=> {
        rejectAnswer(xhr);
    }
}

const processEditAnswer=(xhr,categoryid,categoryname)=>{
    if (xhr.readyState=== 4 && xhr. status === 200){
        if (xhr.response){
            const response=JSON.parse(xhr.response);
            if (response===0){
                const errMsg=document.querySelector('#error-message');
                const msg="Operation has not been completed. Verify that the category name is not duplicated.";
                const categoryname=document.querySelector('#id_categoryname');
                categoryname.focus();
                categoryname.select();
                showMessage(errMsg,msg);
            } else { //suponiendo que todo haya ido bien
                
                const selectText=document.querySelector('#id_categorySelect');
                const selectedOption=selectText.options[selectText.selectedIndex];
                selectedOption.text=categoryname.toUpperCase();
                const checkbox=document.getElementById('id_hiddencategory');
                if (checkbox.checked) {
                    ifhidden=checkbox.value;
                } else {
                    ifhidden=0;
                }
                selectedOption.dataset.hidden=ifhidden;
                if (ifhidden==="1"){
                    selectedOption.classList.add('bg-warning');
                } else {
                    selectedOption.classList.remove('bg-warning');
                }
                selectedOption.value=categoryid;
                
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

