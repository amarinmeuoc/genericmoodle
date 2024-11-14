
define([
    'core_form/modalform',
    'local_ticketmanagement/funciones_comunes'
],function(ModalForm,funcionesComunes){
    const url=M.cfg.wwwroot+'/webservice/rest/server.php';
    const token=document.querySelector('input[name="token"]').value;
    
    const init =() => {
        
        const tickets=document.querySelectorAll('.tickets');

        tickets.forEach((node)=>{
            node.addEventListener('click',(e)=>{
                    funcionesComunes.showTicketFormPopup(e,'controller');
            })
        })
    }

    return {
        init:init
    }
})



