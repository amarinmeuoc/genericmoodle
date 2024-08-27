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

});

const requestResetTrainingPlan=(url,token,op)=>{
    let xhr = new XMLHttpRequest();
    
        //Se prepara el objeto a enviar
        const formData= new FormData();
        formData.append('wstoken',token);
        formData.append('wsfunction', 'block_itp_reset_training_plan');
        formData.append('moodlewsrestformat', 'json');
        formData.append('params[0][op]',op);
        
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
            if (!response){
                const errMsg=document.querySelector('#error-message');
                const msg="Something went wrong. The table training plan hasn't been reset yet.";
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

const showMessage=(elem,msg)=>{
    elem.textContent=msg;
    elem.style.display='block';

    setTimeout(function() {
        elem.style.display = 'none';
    }, 3000); // 3000 milisegundos = 3 segundos
    
}