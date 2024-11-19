define(['core/modal',
    'core_form/modalform',
    'core/toast',
    'core/notification',
    'local_ticketmanagement/funciones_comunes'], 
    function(ModalFactory, ModalForm, addToast, displayException, funcionesComunes){

const url=M.cfg.wwwroot+'/webservice/rest/server.php';
const token=document.querySelector('input[name="token"]').value;
const init =() => {
    
    const logs=document.querySelectorAll('.logs');

    logs.forEach((node)=>{
        node.addEventListener('click',(e)=>{
            showTicketActions(e);
        })
    })
}

const showTicketActions=(e)=>{
    e.stopPropagation();
    const ticketId=e.target.dataset.ticketid;
    let xhr = new XMLHttpRequest();
    
    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', 'local_ticketmanagement_load_actions');
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][ticketid]',ticketId);
    

    xhr.open('POST',url,true);
    xhr.send(formData);

    xhr.onload = (ev)=> {
        funcionesComunes.reqHandlerLoadActions(xhr);
    }

    xhr.onerror = ()=> {
        rejectAnswer(xhr);
    }
}





    
return {
    init:init
  } 

});